<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NominaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // Búsqueda de períodos existentes
        $search = $request->input('search');
        $query = DB::table('periodos_nomina')
            ->select('id', 'codigo', 'fecha_inicio', 'fecha_fin', 'estado');
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('codigo', 'like', "%{$search}%")
                  ->orWhere('fecha_inicio', 'like', "%{$search}%")
                  ->orWhere('fecha_fin', 'like', "%{$search}%")
                  ->orWhere('estado', 'like', "%{$search}%");
            });
        }
        
        $periodos = $query->orderByDesc('fecha_inicio')->paginate(10);

        // Búsqueda de períodos cerrados
        $searchCerrados = $request->input('search_cerrados');
        $queryCerrados = DB::table('periodos_nomina')
            ->select('codigo', 'fecha_inicio', 'fecha_fin', 'estado')
            ->where('estado', 'cerrado');
        
        if ($searchCerrados) {
            $queryCerrados->where(function($q) use ($searchCerrados) {
                $q->where('codigo', 'like', "%{$searchCerrados}%")
                  ->orWhere('fecha_inicio', 'like', "%{$searchCerrados}%")
                  ->orWhere('fecha_fin', 'like', "%{$searchCerrados}%");
            });
        }
        
        $cerrados = $queryCerrados->orderByDesc('fecha_inicio')->paginate(15, ['*'], 'cerrados_page');

        return view('nominas', compact('periodos', 'cerrados'));
    }
}
