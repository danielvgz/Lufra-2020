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
        
        // Contratos con paginación y búsqueda
        $contratosQuery = DB::table('contratos')
            ->select('id','tipo_contrato','frecuencia_pago','salario_base');
        if ($searchContratos) {
            $contratosQuery->where(function($q) use ($searchContratos) {
                $q->where('tipo_contrato', 'like', "%{$searchContratos}%")
                  ->orWhere('frecuencia_pago', 'like', "%{$searchContratos}%");
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
            // Para empleados: solo sus propios recibos
            $recibosQuery = DB::table('recibos as r')
                ->join('empleados as e','e.id','=','r.empleado_id')
                ->where('e.user_id', auth()->id())
                ->select('r.neto','r.estado')
                ->orderByDesc('r.id');
            if ($searchRecibos) {
                $recibosQuery->where('r.estado', 'like', "%{$searchRecibos}%");
            }
            $recibosList = $recibosQuery->paginate(10, ['*'], 'recibos_page');
            
            // Para empleados: solo sus propios pagos aceptados o rechazados
            $pagosQuery = DB::table('pagos as p')
                ->join('recibos as r','r.id','=','p.recibo_id')
                ->join('empleados as e','e.id','=','r.empleado_id')
                ->where('e.user_id', auth()->id())
                ->whereIn('p.estado', ['aceptado','rechazado'])
                ->select('p.recibo_id','p.importe','p.metodo','p.referencia as descripcion','p.estado','p.id','p.respondido_en','p.updated_at','p.created_at','p.pagado_en')
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
            $recibosQuery = DB::table('recibos')
                ->select('id','empleado_id','neto','estado')
                ->orderByDesc('id');
            if ($searchRecibos) {
                $recibosQuery->where('estado', 'like', "%{$searchRecibos}%");
            }
            $recibosList = $recibosQuery->paginate(10, ['*'], 'recibos_page');
            
            $pagosQuery = DB::table('pagos')
                ->select('id','recibo_id','importe','metodo','created_at','updated_at','pagado_en')
                ->orderByDesc('id');
            
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
