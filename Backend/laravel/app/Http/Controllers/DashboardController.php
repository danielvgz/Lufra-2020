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
        
        // Obtener primeros 10 departamentos
        $deps = DB::table('departamentos')->select('codigo','nombre')->limit(10)->get();
        
        // Obtener primeros 10 contratos
        $contratosList = DB::table('contratos')->select('id','tipo_contrato','frecuencia_pago','salario_base')->limit(10)->get();
        
        // Obtener primeros 10 periodos de nómina ordenados por fecha
        $periodosList = DB::table('periodos_nomina')->select('codigo','fecha_inicio','fecha_fin','estado')->orderByDesc('fecha_inicio')->limit(10)->get();
        
        // Obtener recibos y pagos según el rol del usuario
        if ($esEmpleado) {
            // Para empleados: solo sus propios recibos
            $recibosList = DB::table('recibos as r')
                ->join('empleados as e','e.id','=','r.empleado_id')
                ->where('e.user_id', auth()->id())
                ->select('r.neto','r.estado')
                ->orderByDesc('r.id')->limit(10)->get();
            // Para empleados: solo sus propios pagos aceptados o rechazados
            $pagosList = DB::table('pagos as p')
                ->join('recibos as r','r.id','=','p.recibo_id')
                ->join('empleados as e','e.id','=','r.empleado_id')
                ->where('e.user_id', auth()->id())
                ->whereIn('p.estado', ['aceptado','rechazado'])
                ->select('p.recibo_id','p.importe','p.metodo','p.referencia as descripcion','p.estado','p.id','p.respondido_en','p.updated_at','p.created_at')
                ->orderByDesc('p.id')->limit(10)->get();
        } else {
            // Para administradores: todos los recibos y pagos
            $recibosList = DB::table('recibos')->select('id','empleado_id','neto','estado')->orderByDesc('id')->limit(10)->get();
            $pagosList = DB::table('pagos')->select('id','recibo_id','importe','metodo')->orderByDesc('id')->limit(10)->get();
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
            'pagosList'
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
