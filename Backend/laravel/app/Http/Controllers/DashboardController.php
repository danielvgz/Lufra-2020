<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Contadores principales del dashboard
        $empleados = DB::table('empleados')->count();
        $departamentos = DB::table('departamentos')->count();
        $contratos = DB::table('contratos')->count();
        $periodos = DB::table('periodos_nomina')->count();
        $recibos = DB::table('recibos')->count();
        $pagos = DB::table('pagos')->count();
        
        // Verificar si el usuario autenticado tiene rol de empleado
        $esEmpleado = false;
        if (auth()->check()) {
            $esEmpleado = DB::table('rol_usuario as ru')
                ->join('roles as r','r.id','=','ru.rol_id')
                ->where('ru.user_id', auth()->id())
                ->where('r.nombre','empleado')
                ->exists();
        }
        
        // Obtener el último periodo de nómina
        $ultimoPeriodo = DB::table('periodos_nomina')->orderByDesc('fecha_fin')->first();
        
        // Filtros de búsqueda
        $searchDeps = $request->input('search_deps');
        $searchContratos = $request->input('search_contratos');
        $searchPeriodos = $request->input('search_periodos');
        $searchRecibos = $request->input('search_recibos');
        $searchPagos = $request->input('search_pagos');
        
        // Departamentos con paginación y búsqueda
        $depsQuery = DB::table('departamentos')->select('codigo','nombre');
        if ($searchDeps) {
            $depsQuery->where(function($q) use ($searchDeps) {
                $q->where('codigo', 'like', "%{$searchDeps}%")
                  ->orWhere('nombre', 'like', "%{$searchDeps}%");
            });
        }
        $deps = $depsQuery->paginate(10, ['*'], 'deps_page');
        
        // Contratos con paginación y búsqueda (incluye código y cédula del empleado)
        $contratosQuery = DB::table('contratos as c')
            ->leftJoin('users as u', 'u.id', '=', 'c.empleado_id')
            ->leftJoin('empleados as emp', 'emp.user_id', '=', 'u.id')
            ->select(
                'c.id',
                'c.tipo_contrato',
                'c.frecuencia_pago',
                'c.salario_base',
                'u.id as empleado_user_id',
                'u.name as empleado_name',
                'emp.numero_empleado as empleado_codigo',
                'emp.cedula as empleado_cedula'
            );
        if ($searchContratos) {
            $contratosQuery->where(function($q) use ($searchContratos) {
                $q->where('c.tipo_contrato', 'like', "%{$searchContratos}%")
                  ->orWhere('c.frecuencia_pago', 'like', "%{$searchContratos}%")
                  ->orWhere('u.name', 'like', "%{$searchContratos}%")
                  ->orWhere('emp.numero_empleado', 'like', "%{$searchContratos}%")
                  ->orWhere('emp.cedula', 'like', "%{$searchContratos}%");
            });
        }
        $contratosList = $contratosQuery->paginate(10, ['*'], 'contratos_page');
        
        // Períodos de nómina con paginación y búsqueda
        $periodosQuery = DB::table('periodos_nomina')
            ->select('codigo','fecha_inicio','fecha_fin','estado')
            ->orderByDesc('fecha_inicio');
        if ($searchPeriodos) {
            $periodosQuery->where(function($q) use ($searchPeriodos) {
                $q->where('codigo', 'like', "%{$searchPeriodos}%")
                  ->orWhere('estado', 'like', "%{$searchPeriodos}%");
            });
        }
        $periodosList = $periodosQuery->paginate(10, ['*'], 'periodos_page');
        
        // Obtener recibos y pagos según el rol del usuario
        if ($esEmpleado) {
            // Para empleados: solo sus propios recibos (incluir código, cédula y nombre)
            $recibosQuery = DB::table('recibos as r')
                ->join('empleados as emp','emp.id','=','r.empleado_id')
                ->join('users as u','u.id','=','emp.user_id')
                ->where('emp.user_id', auth()->id())
                ->select('r.id','r.neto','r.estado','emp.numero_empleado as empleado_codigo','emp.cedula as empleado_cedula','u.name as empleado_name','u.id as empleado_user_id')
                ->orderByDesc('r.id');
            if ($searchRecibos) {
                $recibosQuery->where('r.estado', 'like', "%{$searchRecibos}%");
            }
            $recibosList = $recibosQuery->paginate(10, ['*'], 'recibos_page');
            
            // Para empleados: mostrar sus propios pagos aceptados, rechazados o pendientes (con datos del empleado)
            $pagosQuery = DB::table('pagos as p')
                ->join('recibos as r','r.id','=','p.recibo_id')
                ->join('empleados as emp','emp.id','=','r.empleado_id')
                ->join('users as u','u.id','=','emp.user_id')
                ->where('emp.user_id', auth()->id())
                ->whereIn('p.estado', ['aceptado','rechazado','pendiente'])
                ->select(
                    'p.id','p.recibo_id','p.importe','p.metodo','p.referencia as descripcion','p.estado','p.respondido_en','p.updated_at','p.created_at','p.pagado_en',
                    'emp.numero_empleado as empleado_codigo','emp.cedula as empleado_cedula','u.name as empleado_name','u.id as empleado_user_id'
                )
                ->orderByDesc('p.id');
            
            // Filtros de pagos
            if ($request->input('metodo_pago')) {
                $pagosQuery->where('p.metodo', $request->input('metodo_pago'));
            }
            if ($request->input('estado_pago')) {
                $pagosQuery->where('p.estado', $request->input('estado_pago'));
            }
            if ($request->input('desde_pago')) {
                $pagosQuery->whereDate('p.created_at', '>=', $request->input('desde_pago'));
            }
            if ($request->input('hasta_pago')) {
                $pagosQuery->whereDate('p.created_at', '<=', $request->input('hasta_pago'));
            }
            
            $pagosList = $pagosQuery->paginate(10, ['*'], 'pagos_page');
        } else {
            // Para administradores: todos los recibos y pagos
            $recibosQuery = DB::table('recibos as r')
                ->leftJoin('empleados as emp','emp.id','=','r.empleado_id')
                ->leftJoin('users as u','u.id','=','emp.user_id')
                ->select('r.id','r.empleado_id','r.neto','r.estado','emp.numero_empleado as empleado_codigo','emp.cedula as empleado_cedula','u.name as empleado_name','u.id as empleado_user_id')
                ->orderByDesc('r.id');
            if ($searchRecibos) {
                $recibosQuery->where('estado', 'like', "%{$searchRecibos}%");
            }
            $recibosList = $recibosQuery->paginate(10, ['*'], 'recibos_page');
            
            $pagosQuery = DB::table('pagos as p')
                ->leftJoin('recibos as r','r.id','=','p.recibo_id')
                ->leftJoin('empleados as emp','emp.id','=','r.empleado_id')
                ->leftJoin('users as u','u.id','=','emp.user_id')
                ->select('p.id','p.recibo_id','p.importe','p.metodo','p.created_at','p.updated_at','p.pagado_en','p.estado', 'emp.numero_empleado as empleado_codigo','emp.cedula as empleado_cedula','u.name as empleado_name','u.id as empleado_user_id')
                ->orderByDesc('p.id');
            
            // Filtros de pagos
            if ($request->input('metodo_pago')) {
                $pagosQuery->where('metodo', $request->input('metodo_pago'));
            }
            if ($request->input('desde_pago')) {
                $pagosQuery->whereDate('created_at', '>=', $request->input('desde_pago'));
            }
            if ($request->input('hasta_pago')) {
                $pagosQuery->whereDate('created_at', '<=', $request->input('hasta_pago'));
            }
            
            $pagosList = $pagosQuery->paginate(10, ['*'], 'pagos_page');
        }
        
        // Obtener métodos de pago desde la tabla metodos
        $metodosPago = DB::table('metodos_pago')
            ->where('activo', true)
            ->orderBy('nombre')
            ->pluck('nombre')
            ->toArray();
        
        // Si no hay métodos en la BD, usar los por defecto
        if (empty($metodosPago)) {
            $metodosPago = ['Transferencia', 'Efectivo', 'Cheque', 'Pago móvil', 'Zelle'];
        }

        // Calcular información de contrato para el empleado autenticado
        $contratoInfo = null;
        if ($esEmpleado && auth()->check()) {
            try {
                $userId = auth()->id();
                $contrato = DB::table('contratos')
                    ->where('empleado_id', $userId)
                    ->where(function($q) {
                        $q->where('estado', 'activo')
                          ->orWhereNull('fecha_fin')
                          ->orWhereDate('fecha_fin', '>=', now());
                    })
                    ->orderByDesc('id')
                    ->first();

                if ($contrato) {
                    $fechaFin = $contrato->fecha_fin ? \Carbon\Carbon::parse($contrato->fecha_fin) : null;
                    $hoy = \Carbon\Carbon::today();
                    if ($fechaFin) {
                        if ($fechaFin->lt($hoy)) {
                            $daysRemaining = 0;
                            $expired = true;
                        } else {
                            $daysRemaining = $hoy->diffInDays($fechaFin);
                            $expired = false;
                        }
                    } else {
                        $daysRemaining = null;
                        $expired = false;
                    }

                    $contratoInfo = [
                        'id' => $contrato->id,
                        'tipo_contrato' => $contrato->tipo_contrato ?? null,
                        'puesto' => $contrato->puesto ?? null,
                        'fecha_inicio' => $contrato->fecha_inicio ?? null,
                        'fecha_fin' => $contrato->fecha_fin ?? null,
                        'days_remaining' => $daysRemaining,
                        'expired' => $expired,
                        'salario_base' => $contrato->salario_base ?? null,
                        'estado' => $contrato->estado ?? null,
                    ];
                }
            } catch (\Throwable $e) {
                // ignore errors and leave contratoInfo null
                $contratoInfo = null;
            }
        }

        return view('inicio', compact(
            'empleados',
            'departamentos',
            'contratos',
            'periodos',
            'recibos',
            'pagos',
            'esEmpleado',
            'ultimoPeriodo',
            'deps',
            'contratosList',
            'periodosList',
            'recibosList',
            'pagosList',
            'metodosPago'
            , 'contratoInfo'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
