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
        ], [
            'periodo_id.required' => 'El periodo es obligatorio.',
            'periodo_id.integer' => 'El periodo debe ser un número.',
            'periodo_id.exists' => 'El periodo no existe.',
        ]);

        $period = PayrollPeriod::findOrFail($data['periodo_id']);
        if ($period->estado === 'cerrado') {
            return response()->json(['message' => 'Periodo cerrado, no se puede recalcular.'], 422);
        }

        // Obtener impuesto por defecto
        $impuestoPorDefecto = DB::table('impuestos')
            ->where('activo', true)
            ->where('por_defecto', true)
            ->first();
        
        $taxRate = $impuestoPorDefecto ? ($impuestoPorDefecto->porcentaje / 100) : (float)($request->input('impuesto', 0.10));
        $ssRate  = (float)($request->input('seguridad_social', 0.05));

        DB::transaction(function () use ($period, $taxRate, $ssRate, $impuestoPorDefecto) {
            $empleados = Employee::query()->where('estado','activo')->get();
            
            foreach ($empleados as $emp) {
                // Verificar si el empleado ya tiene pagos en este período
                $tienePagos = DB::table('pagos')
                    ->join('recibos', 'pagos.recibo_id', '=', 'recibos.id')
                    ->where('recibos.periodo_nomina_id', $period->id)
                    ->where('recibos.empleado_id', $emp->id)
                    ->exists();
                
                // Si ya tiene pagos, omitir el cálculo para este empleado
                if ($tienePagos) {
                    continue;
                }
                
                $devengado = (float)($emp->salario_base ?? 0);
                $impuestoMonto = round($devengado * $taxRate, 2);
                $ss  = round($devengado * $ssRate, 2);
                $ded = $impuestoMonto + $ss;
                $neto = max(0, round($devengado - $ded, 2));
                
                $payload = [
                    'bruto' => $devengado, // mantener por compatibilidad
                    'devengado' => $devengado,
                    'impuesto_monto' => $impuestoMonto,
                    'impuesto_id' => $impuestoPorDefecto ? $impuestoPorDefecto->id : null,
                    'deducciones' => $ded,
                    'detalle_deducciones' => [
                        'impuesto' => $impuestoMonto,
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
        ], [
            'periodo_id.required' => 'El periodo es obligatorio.',
            'periodo_id.integer' => 'El periodo debe ser un número.',
            'periodo_id.exists' => 'El periodo no existe.',
        ]);
        $period = PayrollPeriod::findOrFail($data['periodo_id']);
        if ($period->estado === 'cerrado') {
            return response()->json(['message' => 'Periodo ya cerrado.'], 422);
        }
        
        $pagosPendientes = 0;
        
        DB::transaction(function () use ($period, &$pagosPendientes) {
            Recibo::where('periodo_nomina_id', $period->id)
                ->whereNull('locked_at')
                ->update(['estado' => 'aprobado', 'locked_at' => now(), 'updated_at' => now()]);
            
            // Contar pagos pendientes
            $pagosPendientes = DB::table('pagos')
                ->join('recibos', 'pagos.recibo_id', '=', 'recibos.id')
                ->where('recibos.periodo_nomina_id', $period->id)
                ->where('pagos.estado', 'pendiente')
                ->count();
            
            $period->estado = 'cerrado';
            $period->save();
        });
        
        // Notificar si hay pagos pendientes
        if ($pagosPendientes > 0) {
            NotificationHelper::notifyPeriodoCerradoConPagosPendientes($period->id, $pagosPendientes);
        }
        
        return response()->json(['message' => 'Recibos aprobados y periodo cerrado.']);
    }

    public function pay(Request $request): JsonResponse
    {
        $data = $request->validate([
            'periodo_id' => ['required','integer','exists:periodos_nomina,id'],
            'metodo' => ['required','string','max:50'],
            'referencia' => ['nullable','string','max:100'],
        ], [
            'periodo_id.required' => 'El periodo es obligatorio.',
            'periodo_id.integer' => 'El periodo debe ser un número.',
            'periodo_id.exists' => 'El periodo no existe.',
            'metodo.required' => 'El método de pago es obligatorio.',
            'metodo.string' => 'El método de pago debe ser texto.',
            'metodo.max' => 'El método de pago no debe superar 50 caracteres.',
            'referencia.string' => 'La referencia debe ser texto.',
            'referencia.max' => 'La referencia no debe superar 100 caracteres.',
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

    // Métodos adicionales para web routes
    public function createPeriod(Request $request)
    {
        $data = $request->validate([
            'frecuencia' => ['required', 'in:semanal,quincenal,mensual'],
            'fecha_inicio' => ['required', 'date'],
        ], [
            'frecuencia.required' => 'La frecuencia es obligatoria.',
            'frecuencia.in' => 'La frecuencia debe ser semanal, quincenal o mensual.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida.',
        ]);

        $inicio = \Carbon\Carbon::parse($data['fecha_inicio']);
        
        switch ($data['frecuencia']) {
            case 'semanal':
                $fin = $inicio->copy()->addDays(6);
                break;
            case 'quincenal':
                $fin = $inicio->copy()->addDays(14);
                break;
            case 'mensual':
                $fin = $inicio->copy()->endOfMonth();
                break;
        }

        $codigo = $inicio->format('Y-m');
        if ($data['frecuencia'] !== 'mensual') {
            $codigo .= '-' . $inicio->format('d');
        }

        // Buscar si existe un período con ese código
        $existingPeriod = DB::table('periodos_nomina')->where('codigo', $codigo)->first();
        
        if ($existingPeriod) {
            // Si existe y está cerrado, reabrirlo automáticamente
            if ($existingPeriod->estado === 'cerrado') {
                DB::table('periodos_nomina')
                    ->where('id', $existingPeriod->id)
                    ->update([
                        'estado' => 'abierto',
                        'updated_at' => now(),
                    ]);
                
                return redirect()->route('nominas.index')
                    ->with('success', "El período {$codigo} ya existía y estaba cerrado. Se ha reabierto automáticamente.");
            }
            
            // Si existe y está abierto, mostrar mensaje
            return back()
                ->withErrors(['codigo' => "Ya existe un período con ese código ({$codigo}) y está abierto."])
                ->withInput();
        }

        // Crear nuevo período si no existe
        DB::table('periodos_nomina')->insert([
            'codigo' => $codigo,
            'fecha_inicio' => $inicio->toDateString(),
            'fecha_fin' => $fin->toDateString(),
            'estado' => 'abierto',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('nominas.index')->with('success', 'Período de nómina creado correctamente');
    }

    public function reopenPeriod(Request $request)
    {
        $data = $request->validate([
            'periodo_id' => ['required', 'integer', 'exists:periodos_nomina,id'],
        ], [
            'periodo_id.required' => 'El período es obligatorio.',
            'periodo_id.integer' => 'El período debe ser un número.',
            'periodo_id.exists' => 'El período no existe.',
        ]);

        $period = PayrollPeriod::findOrFail($data['periodo_id']);

        if ($period->estado === 'abierto') {
            return back()->withErrors(['estado' => 'El período ya está abierto.']);
        }

        $period->estado = 'abierto';
        $period->save();

        return redirect()->route('nominas.index')
            ->with('success', "El período {$period->codigo} ha sido reabierto correctamente.");
    }


    public function closePeriod(Request $request)
    {
        $data = $request->validate([
            'periodo_id' => ['required', 'integer', 'exists:periodos_nomina,id'],
        ], [
            'periodo_id.required' => 'El periodo es obligatorio.',
            'periodo_id.integer' => 'El periodo debe ser un número.',
            'periodo_id.exists' => 'El periodo no existe.',
        ]);

        $pagosPendientes = DB::table('pagos as p')
            ->join('recibos as r', 'r.id', '=', 'p.recibo_id')
            ->where('r.periodo_nomina_id', $data['periodo_id'])
            ->where('p.estado', 'pendiente')
            ->count();

        DB::table('periodos_nomina')->where('id', $data['periodo_id'])->update([
            'estado' => 'cerrado',
            'updated_at' => now(),
        ]);

        if ($pagosPendientes > 0) {
            NotificationHelper::notifyPeriodoCerradoConPagosPendientes($data['periodo_id'], $pagosPendientes);
        }

        return redirect()->route('nominas.index')->with('success', 'Período cerrado correctamente');
    }
}
