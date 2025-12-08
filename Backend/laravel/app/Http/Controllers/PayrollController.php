<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\Recibo;
use App\Models\Pago;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    public function listPeriods(): JsonResponse
    {
        $periods = PayrollPeriod::query()
            ->withCount(['recibos as total_recibos'])
            ->orderByDesc('fecha_inicio')
            ->paginate(50);
        return response()->json($periods);
    }

    public function calculate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'periodo_id' => ['required','integer','exists:periodos_nomina,id'],
        ]);

        $period = PayrollPeriod::findOrFail($data['periodo_id']);
        if ($period->estado === 'cerrado') {
            return response()->json(['message' => 'Periodo cerrado, no se puede recalcular.'], 422);
        }

        $taxRate = (float)($request->input('impuesto', 0.10));
        $ssRate  = (float)($request->input('seguridad_social', 0.05));

        DB::transaction(function () use ($period, $taxRate, $ssRate) {
            $empleados = Employee::query()->where('estado','activo')->get();
            foreach ($empleados as $emp) {
                $bruto = (float)($emp->salario_base ?? 0);
                $imp = round($bruto * $taxRate, 2);
                $ss  = round($bruto * $ssRate, 2);
                $ded = $imp + $ss;
                $neto = max(0, round($bruto - $ded, 2));
                $payload = [
                    'bruto' => $bruto,
                    'deducciones' => $ded,
                    'detalle_deducciones' => [
                        'impuesto' => $imp,
                        'seguridad_social' => $ss,
                    ],
                    'neto' => $neto,
                ];

                $recibo = Recibo::firstOrNew([
                    'periodo_nomina_id' => $period->id,
                    'empleado_id' => $emp->id,
                ]);

                if (!is_null($recibo->locked_at) || $recibo->estado === 'aprobado') {
                    continue; // inmutable
                }

                $recibo->fill($payload);
                $recibo->estado = 'calculado';
                $recibo->save();
            }
        });

        return response()->json(['message' => 'Cálculo completado.']);
    }

    public function approve(Request $request): JsonResponse
    {
        $data = $request->validate([
            'periodo_id' => ['required','integer','exists:periodos_nomina,id'],
        ]);
        $period = PayrollPeriod::findOrFail($data['periodo_id']);
        if ($period->estado === 'cerrado') {
            return response()->json(['message' => 'Periodo ya cerrado.'], 422);
        }
        DB::transaction(function () use ($period) {
            Recibo::where('periodo_nomina_id', $period->id)
                ->whereNull('locked_at')
                ->update(['estado' => 'aprobado', 'locked_at' => now(), 'updated_at' => now()]);
            $period->estado = 'cerrado';
            $period->save();
        });
        return response()->json(['message' => 'Recibos aprobados y periodo cerrado.']);
    }

    public function pay(Request $request): JsonResponse
    {
        $data = $request->validate([
            'periodo_id' => ['required','integer','exists:periodos_nomina,id'],
            'metodo' => ['required','string','max:50'],
            'referencia' => ['nullable','string','max:100'],
        ]);
        $period = PayrollPeriod::findOrFail($data['periodo_id']);
        $metodo = $data['metodo'];
        $ref = $data['referencia'] ?? null;

        $count = 0;
        DB::transaction(function () use ($period, $metodo, $ref, &$count) {
            $recibos = Recibo::where('periodo_nomina_id', $period->id)->where('estado','aprobado')->get();
            foreach ($recibos as $r) {
                $exists = Pago::where('recibo_id',$r->id)->exists();
                if ($exists) { continue; }
                Pago::create([
                    'recibo_id' => $r->id,
                    'importe' => $r->neto,
                    'metodo' => $metodo,
                    'referencia' => $ref,
                    'pagado_at' => now(),
                ]);
                $count++;
            }
        });

        return response()->json(['message' => "Pagos registrados: {$count}"]);
    }

    public function receiptPdf(Recibo $recibo)
    {
        // Sin librería PDF: devolver HTML imprimible para exportar a PDF desde el navegador
        return response()->view('recibo_pdf', ['recibo' => $recibo->load('empleado','periodo','pagos')]);
    }

    public function reports(Request $request): JsonResponse
    {
        $periodoId = $request->integer('periodo_id');
        $por = $request->input('por', 'periodo'); // 'periodo' | 'departamento'

        if ($por === 'departamento') {
            $rows = DB::table('recibos as r')
                ->join('empleados as e','e.id','=','r.empleado_id')
                ->leftJoin('departments as d','d.id','=','e.department_id')
                ->when($periodoId, fn($q) => $q->where('r.periodo_nomina_id',$periodoId))
                ->selectRaw('COALESCE(d.name, "Sin depto") as departamento, SUM(r.bruto) as bruto, SUM(r.neto) as neto, COUNT(*) as recibos')
                ->groupBy('departamento')
                ->orderBy('departamento')
                ->get();
            return response()->json($rows);
        }

        $rows = DB::table('recibos as r')
            ->join('periodos_nomina as p','p.id','=','r.periodo_nomina_id')
            ->when($periodoId, fn($q) => $q->where('r.periodo_nomina_id',$periodoId))
            ->selectRaw('p.codigo, SUM(r.bruto) as bruto, SUM(r.neto) as neto, COUNT(*) as recibos')
            ->groupBy('p.codigo')
            ->orderByDesc(DB::raw('MIN(p.fecha_inicio)'))
            ->get();
        return response()->json($rows);
    }

    public function obligations(Request $request): JsonResponse
    {
        $periodoId = $request->integer('periodo_id');
        $query = Recibo::query();
        if ($periodoId) { $query->where('periodo_nomina_id', $periodoId); }
        $totImp = 0.0; $totSS = 0.0; $totDed = 0.0; $totBruto = 0.0; $totNeto = 0.0; $count = 0;
        foreach ($query->get() as $r) {
            $det = is_array($r->detalle_deducciones) ? $r->detalle_deducciones : (json_decode($r->detalle_deducciones ?? '[]', true) ?: []);
            $imp = (float)($det['impuesto'] ?? 0);
            $ss  = (float)($det['seguridad_social'] ?? 0);
            $totImp += $imp;
            $totSS  += $ss;
            $totDed += ($imp + $ss);
            $totBruto += (float)$r->bruto;
            $totNeto  += (float)$r->neto;
            $count++;
        }
        return response()->json([
            'periodo_id' => $periodoId,
            'total_bruto' => round($totBruto, 2),
            'total_impuestos' => round($totImp, 2),
            'total_seguridad_social' => round($totSS, 2),
            'total_deducciones' => round($totDed, 2),
            'total_neto' => round($totNeto, 2),
            'recibos' => $count,
        ]);
    }

    public function bankFile(int $periodoId, Request $request)
    {
        $period = PayrollPeriod::findOrFail($periodoId);
        $recibos = Recibo::with('empleado')
            ->where('periodo_nomina_id', $period->id)
            ->where('estado','aprobado')
            ->get();
        $lines = [];
        $lines[] = 'cuenta_bancaria;importe;nombre;identificador_fiscal;recibo_id';
        foreach ($recibos as $r) {
            $emp = $r->empleado;
            $cuenta = $emp->cuenta_bancaria ?? '';
            $nombre = trim(($emp->nombre ?? '').' '.($emp->apellido ?? ''));
            $idfis = $emp->identificador_fiscal ?? '';
            $importe = number_format((float)$r->neto, 2, '.', '');
            $lines[] = implode(';', [ $cuenta, $importe, $nombre, $idfis, (string)$r->id ]);
        }
        $content = implode("\r\n", $lines) . "\r\n";
        return response($content)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="transfer_'.($period->codigo).'.csv"');
    }
}
