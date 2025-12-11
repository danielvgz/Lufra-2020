<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecibosPagosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // Verificar si es empleado
        $esEmpleado = DB::table('rol_usuario as ru')
            ->join('roles as r', 'r.id', '=', 'ru.rol_id')
            ->where('ru.user_id', auth()->id())
            ->where('r.nombre', 'empleado')
            ->exists();

        if ($esEmpleado) {
            return $this->vistaEmpleado($request);
        } else {
            return $this->vistaAdministrador($request);
        }
    }

    private function vistaEmpleado(Request $request)
    {
        $searchPagos = $request->input('search_pagos');
        $queryPagos = DB::table('pagos as p')
            ->join('recibos as r', 'r.id', '=', 'p.recibo_id')
            ->join('empleados as e', 'e.id', '=', 'r.empleado_id')
            ->where('e.user_id', auth()->id())
            ->select('p.id', 'p.importe', 'p.metodo', 'p.estado', 'p.referencia', 'r.id as recibo_id', 'p.moneda');
        
        if ($searchPagos) {
            $queryPagos->where(function($q) use ($searchPagos) {
                $q->where('p.importe', 'like', "%{$searchPagos}%")
                  ->orWhere('p.metodo', 'like', "%{$searchPagos}%")
                  ->orWhere('p.estado', 'like', "%{$searchPagos}%")
                  ->orWhere('p.referencia', 'like', "%{$searchPagos}%")
                  ->orWhere('p.moneda', 'like', "%{$searchPagos}%");
            });
        }
        
        $pagos = $queryPagos->orderByDesc('p.id')->paginate(20, ['*'], 'pagos_page');

        return view('recibos_pagos', compact('pagos'));
    }

    private function vistaAdministrador(Request $request)
    {
        // Períodos de nómina
        $searchPeriodos = $request->input('search_periodos');
        $queryPeriodos = DB::table('periodos_nomina');
        
        if ($searchPeriodos) {
            $queryPeriodos->where(function($q) use ($searchPeriodos) {
                $q->where('codigo', 'like', "%{$searchPeriodos}%")
                  ->orWhere('fecha_inicio', 'like', "%{$searchPeriodos}%")
                  ->orWhere('fecha_fin', 'like', "%{$searchPeriodos}%")
                  ->orWhere('estado', 'like', "%{$searchPeriodos}%");
            });
        }
        
        $periodos = $queryPeriodos->orderByDesc('fecha_inicio')->paginate(15, ['*'], 'periodos_page');

        // Recibos sin pago
        $q = $request->input('q');
        $recibosQuery = DB::table('recibos as r')
            ->leftJoin('pagos as p', 'p.recibo_id', '=', 'r.id')
            ->join('empleados as e', 'e.id', '=', 'r.empleado_id')
            ->join('periodos_nomina as pn', 'pn.id', '=', 'r.periodo_nomina_id')
            ->leftJoin('contratos as c', 'c.empleado_id', '=', 'r.empleado_id')
            ->whereNull('p.id')
            // Cambio: Mostrar recibos sin pago de cualquier período (abierto o cerrado)
            ->where(function($w) {
                $w->whereColumn('c.fecha_inicio', '<=', 'pn.fecha_fin')
                  ->where(function($w2) {
                      $w2->whereNull('c.fecha_fin')
                         ->orWhereColumn('c.fecha_fin', '>=', 'pn.fecha_inicio');
                  });
            });

        if ($q) {
            $recibosQuery->where(function($w) use ($q) {
                $w->where('e.nombre', 'like', "%{$q}%")
                  ->orWhere('e.apellido', 'like', "%{$q}%");
                if (is_numeric($q)) {
                    $w->orWhere('r.id', '=', $q);
                }
            });
        }

        $recibosSinPago = $recibosQuery->select('r.id', 'e.nombre', 'e.apellido', 'r.neto', 'pn.codigo as periodo_codigo', 'pn.estado as periodo_estado')
            ->orderByDesc('r.id')
            ->paginate(20, ['*'], 'recibos_page');

        return view('recibos_pagos', compact('periodos', 'recibosSinPago'));
    }

    public function reportes(Request $request)
    {
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');

        $query = DB::table('periodos_nomina as pn')
            ->leftJoin('recibos as r', function($join) use ($desde, $hasta) {
                $join->on('r.periodo_nomina_id', '=', 'pn.id');
                if ($desde) {
                    $join->whereDate('r.created_at', '>=', $desde);
                }
                if ($hasta) {
                    $join->whereDate('r.created_at', '<=', $hasta);
                }
            })
            ->select(
                'pn.codigo',
                'pn.fecha_inicio',
                'pn.fecha_fin',
                DB::raw('COUNT(r.id) as recibos'),
                DB::raw('COALESCE(SUM(r.neto), 0) as total_neto')
            )
            ->groupBy('pn.id', 'pn.codigo', 'pn.fecha_inicio', 'pn.fecha_fin')
            ->orderByDesc('pn.fecha_inicio');

        $periodos = $query->get();

        return view('recibos_pagos_reportes', compact('periodos', 'desde', 'hasta'));
    }

    public function reportesDetalle(Request $request)
    {
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');
        $q = $request->input('q');

        $query = DB::table('recibos as r')
            ->join('periodos_nomina as p', 'p.id', '=', 'r.periodo_nomina_id')
            ->join('empleados as e', 'e.id', '=', 'r.empleado_id')
            ->leftJoin('pagos as pg', 'pg.recibo_id', '=', 'r.id')
            ->select(
                'p.codigo as periodo',
                'p.fecha_inicio',
                'p.fecha_fin',
                'r.id as recibo_id',
                'e.nombre',
                'e.apellido',
                'r.neto',
                'pg.id as pago_id',
                'pg.metodo',
                'pg.importe',
                'pg.estado',
                'pg.referencia as descripcion'
            )
            ->orderByDesc('p.fecha_inicio')
            ->orderByDesc('r.id');

        if ($desde) {
            $query->whereDate('p.fecha_inicio', '>=', $desde);
        }
        if ($hasta) {
            $query->whereDate('p.fecha_fin', '<=', $hasta);
        }
        if ($q) {
            $query->where(function($w) use ($q) {
                $w->where('e.nombre', 'like', "%{$q}%")
                  ->orWhere('e.apellido', 'like', "%{$q}%")
                  ->orWhere('p.codigo', 'like', "%{$q}%");
                if (is_numeric($q)) {
                    $w->orWhere('r.id', '=', $q);
                }
            });
        }

        $rows = $query->limit(500)->get();

        return view('recibos_pagos_reportes_detalle', compact('rows', 'desde', 'hasta', 'q'));
    }

    public function archivoBanco(Request $request)
    {
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');

        $query = DB::table('pagos as pg')
            ->join('recibos as r', 'r.id', '=', 'pg.recibo_id')
            ->join('empleados as e', 'e.id', '=', 'r.empleado_id')
            ->join('periodos_nomina as p', 'p.id', '=', 'r.periodo_nomina_id')
            ->where('pg.estado', 'aceptado')
            ->select(
                'e.nombre',
                'e.apellido',
                'e.cuenta_bancaria as numero_cuenta',
                'pg.importe',
                'pg.moneda',
                'p.codigo as periodo',
                'pg.created_at'
            );

        if ($desde) {
            $query->whereDate('pg.created_at', '>=', $desde);
        }
        if ($hasta) {
            $query->whereDate('pg.created_at', '<=', $hasta);
        }

        $pagos = $query->orderBy('e.apellido')->orderBy('e.nombre')->get();

        return view('recibos_pagos_banco', compact('pagos', 'desde', 'hasta'));
    }

    public function obligaciones(Request $request)
    {
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');

        $query = DB::table('recibos as r')
            ->join('periodos_nomina as p', 'p.id', '=', 'r.periodo_nomina_id')
            ->join('empleados as e', 'e.id', '=', 'r.empleado_id')
            ->select(
                'p.codigo as periodo',
                'p.fecha_inicio',
                'p.fecha_fin',
                DB::raw('COUNT(r.id) as total_recibos'),
                DB::raw('SUM(r.bruto) as total_bruto'),
                DB::raw('SUM(r.deducciones) as total_deducciones'),
                DB::raw('SUM(r.neto) as total_neto')
            )
            ->groupBy('p.id', 'p.codigo', 'p.fecha_inicio', 'p.fecha_fin')
            ->orderByDesc('p.fecha_inicio');

        if ($desde) {
            $query->whereDate('p.fecha_inicio', '>=', $desde);
        }
        if ($hasta) {
            $query->whereDate('p.fecha_fin', '<=', $hasta);
        }

        $obligaciones = $query->paginate(20);

        return view('recibos_pagos_obligaciones', compact('obligaciones', 'desde', 'hasta'));
    }

    public function asignarPago(Request $request)
    {
        $data = $request->validate([
            'recibo_id' => ['required', 'integer', 'exists:recibos,id'],
            'importe' => ['required', 'numeric', 'min:0'],
            'moneda' => ['required', 'string', 'max:10'],
            'metodo' => ['required', 'string', 'max:100'],
            'referencia' => ['nullable', 'string', 'max:200'],
            'concepto' => ['nullable', 'string', 'max:100'],
            'impuesto_id' => ['nullable', 'integer', 'exists:impuestos,id'],
        ], [
            'recibo_id.required' => 'El recibo es obligatorio.',
            'recibo_id.integer' => 'El recibo debe ser un número.',
            'recibo_id.exists' => 'El recibo no existe.',
            'importe.required' => 'El importe es obligatorio.',
            'importe.numeric' => 'El importe debe ser un número.',
            'importe.min' => 'El importe debe ser mayor o igual a 0.',
            'moneda.required' => 'La moneda es obligatoria.',
            'moneda.string' => 'La moneda debe ser texto.',
            'moneda.max' => 'La moneda no debe superar 10 caracteres.',
            'metodo.required' => 'El método de pago es obligatorio.',
            'metodo.string' => 'El método de pago debe ser texto.',
            'metodo.max' => 'El método de pago no debe superar 100 caracteres.',
            'referencia.string' => 'La referencia debe ser texto.',
            'referencia.max' => 'La referencia no debe superar 200 caracteres.',
            'concepto.string' => 'El concepto debe ser texto.',
            'concepto.max' => 'El concepto no debe superar 100 caracteres.',
            'impuesto_id.integer' => 'El impuesto debe ser un número.',
            'impuesto_id.exists' => 'El impuesto no existe.',
        ]);

        DB::table('pagos')->insert([
            'recibo_id' => $data['recibo_id'],
            'importe' => $data['importe'],
            'moneda' => $data['moneda'],
            'metodo' => $data['metodo'],
            'referencia' => $data['referencia'] ?? null,
            'impuesto_id' => $data['impuesto_id'] ?? null,
            'estado' => 'pendiente',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('recibos_pagos')->with('success', 'Pago asignado correctamente');
    }

    public function aceptar($pagoId)
    {
        DB::table('pagos')->where('id', $pagoId)->update([
            'estado' => 'aceptado',
            'pagado_en' => now(),
            'updated_at' => now(),
        ]);

        // Notificar a administradores
        $pago = DB::table('pagos as p')
            ->join('recibos as r', 'r.id', '=', 'p.recibo_id')
            ->where('p.id', $pagoId)
            ->first();

        if ($pago) {
            \App\Http\Controllers\NotificationHelper::notifyReciboAceptado($pagoId, $pago->empleado_id);
        }

        return redirect()->route('recibos_pagos')->with('success', 'Pago aceptado correctamente');
    }

    public function rechazar($pagoId)
    {
        DB::table('pagos')->where('id', $pagoId)->update([
            'estado' => 'rechazado',
            'updated_at' => now(),
        ]);

        // Notificar a administradores
        $pago = DB::table('pagos as p')
            ->join('recibos as r', 'r.id', '=', 'p.recibo_id')
            ->where('p.id', $pagoId)
            ->first();

        if ($pago) {
            \App\Http\Controllers\NotificationHelper::notifyReciboRechazado($pagoId, $pago->empleado_id);
        }

        return redirect()->route('recibos_pagos')->with('success', 'Pago rechazado correctamente');
    }

    public function pagoManual(Request $request)
    {
        $data = $request->validate([
            'empleado_id' => ['required','integer','exists:empleados,id'],
            'importe' => ['required','numeric','min:0'],
            'moneda' => ['required','string','max:3'],
            'metodo' => ['required','string','max:50'],
        ], [
            'empleado_id.required' => 'El empleado es obligatorio.',
            'empleado_id.integer' => 'El empleado debe ser un número.',
            'empleado_id.exists' => 'El empleado no existe.',
            'importe.required' => 'El importe es obligatorio.',
            'importe.numeric' => 'El importe debe ser un número.',
            'importe.min' => 'El importe debe ser mayor o igual a 0.',
            'moneda.required' => 'La moneda es obligatoria.',
            'moneda.string' => 'La moneda debe ser texto.',
            'moneda.max' => 'La moneda no debe superar 3 caracteres.',
            'metodo.required' => 'El método de pago es obligatorio.',
            'metodo.string' => 'El método de pago debe ser texto.',
            'metodo.max' => 'El método de pago no debe superar 50 caracteres.',
        ]);
        
        // Crear un recibo ad-hoc para vincular el pago manual
        $periodoId = DB::table('periodos_nomina')->orderByDesc('fecha_inicio')->value('id');
        
        if (!$periodoId) {
            return redirect()->route('recibos_pagos')->with('error','No existe un período de nómina para vincular el pago manual.');
        }
        
        $reciboId = DB::table('recibos')->insertGetId([
            'empleado_id' => $data['empleado_id'],
            'periodo_nomina_id' => $periodoId,
            'bruto' => $data['importe'],
            'neto' => $data['importe'],
            'estado' => 'aprobado',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        DB::table('pagos')->insert([
            'recibo_id' => $reciboId,
            'importe' => $data['importe'],
            'moneda' => $data['moneda'],
            'metodo' => $data['metodo'],
            'estado' => 'pendiente',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return redirect()->route('recibos_pagos')->with('success', 'Pago manual creado correctamente');
    }
}
