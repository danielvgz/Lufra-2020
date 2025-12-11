<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TabuladoresController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $frecuencia = $request->input('frecuencia');
        
        $query = DB::table('tabuladores_salariales')->orderBy('nombre');
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('cargo', 'like', "%{$search}%");
            });
        }
        
        if ($frecuencia) {
            $query->where('frecuencia', $frecuencia);
        }
        
        $tabuladores = $query->paginate(20);
        
        return view('tabuladores', compact('tabuladores'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'cargo' => ['nullable', 'string', 'max:100'],
            'frecuencia' => ['required', 'in:semanal,quincenal,mensual'],
            'sueldo_base' => ['required', 'numeric', 'min:0'],
            'moneda' => ['required', 'string', 'max:10'],
            'descripcion' => ['nullable', 'string'],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'frecuencia.required' => 'La frecuencia es obligatoria.',
            'sueldo_base.required' => 'El sueldo base es obligatorio.',
            'sueldo_base.min' => 'El sueldo base no puede ser negativo.',
            'moneda.required' => 'La moneda es obligatoria.',
        ]);

        DB::table('tabuladores_salariales')->insert([
            'nombre' => $data['nombre'],
            'cargo' => $data['cargo'] ?? null,
            'frecuencia' => $data['frecuencia'],
            'sueldo_base' => $data['sueldo_base'],
            'moneda' => $data['moneda'],
            'descripcion' => $data['descripcion'] ?? null,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('tabuladores.view')->with('success', 'Tabulador creado correctamente');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'cargo' => ['nullable', 'string', 'max:100'],
            'frecuencia' => ['required', 'in:semanal,quincenal,mensual'],
            'sueldo_base' => ['required', 'numeric', 'min:0'],
            'moneda' => ['required', 'string', 'max:10'],
            'descripcion' => ['nullable', 'string'],
        ]);

        DB::table('tabuladores_salariales')->where('id', $id)->update([
            'nombre' => $data['nombre'],
            'cargo' => $data['cargo'] ?? null,
            'frecuencia' => $data['frecuencia'],
            'sueldo_base' => $data['sueldo_base'],
            'moneda' => $data['moneda'],
            'descripcion' => $data['descripcion'] ?? null,
            'updated_at' => now(),
        ]);

        return redirect()->route('tabuladores.view')->with('success', 'Tabulador actualizado correctamente');
    }

    public function destroy($id)
    {
        DB::table('tabuladores_salariales')->where('id', $id)->delete();
        return redirect()->route('tabuladores.view')->with('success', 'Tabulador eliminado correctamente');
    }

    public function toggle($id)
    {
        $tabulador = DB::table('tabuladores_salariales')->where('id', $id)->first();
        
        DB::table('tabuladores_salariales')->where('id', $id)->update([
            'activo' => !$tabulador->activo,
            'updated_at' => now(),
        ]);

        return redirect()->route('tabuladores.view')->with('success', 'Estado actualizado correctamente');
    }

    public function getSueldoByFrecuencia(Request $request)
    {
        $frecuencia = $request->input('frecuencia');
        $cargoId = $request->input('cargo_id');
        
        $tabulador = DB::table('tabuladores_salariales')
            ->where('frecuencia', $frecuencia)
            ->where('activo', true)
            ->where('id', $cargoId)
            ->first();
        
        return response()->json([
            'sueldo_base' => $tabulador ? $tabulador->sueldo_base : 0,
            'moneda' => $tabulador ? $tabulador->moneda : 'VES',
        ]);
    }
}
