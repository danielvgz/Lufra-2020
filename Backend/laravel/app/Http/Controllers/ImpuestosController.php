<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImpuestosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        
        $query = DB::table('impuestos')->orderBy('nombre');
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('codigo', 'like', "%{$search}%");
            });
        }
        
        $impuestos = $query->paginate(20);
        
        return view('impuestos', compact('impuestos'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'codigo' => ['required', 'string', 'max:50', 'unique:impuestos,codigo'],
            'porcentaje' => ['required', 'numeric', 'min:0', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'por_defecto' => ['boolean'],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'codigo.required' => 'El código es obligatorio.',
            'codigo.unique' => 'Este código ya existe.',
            'porcentaje.required' => 'El porcentaje es obligatorio.',
            'porcentaje.min' => 'El porcentaje no puede ser negativo.',
            'porcentaje.max' => 'El porcentaje no puede ser mayor a 100.',
        ]);

        // Si se marca como por defecto, desmarcar otros
        if ($request->boolean('por_defecto')) {
            DB::table('impuestos')->update(['por_defecto' => false]);
        }

        DB::table('impuestos')->insert([
            'nombre' => $data['nombre'],
            'codigo' => $data['codigo'],
            'porcentaje' => $data['porcentaje'],
            'descripcion' => $data['descripcion'] ?? null,
            'por_defecto' => $request->boolean('por_defecto'),
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('impuestos.view')->with('success', 'Impuesto creado correctamente');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'codigo' => ['required', 'string', 'max:50', 'unique:impuestos,codigo,' . $id],
            'porcentaje' => ['required', 'numeric', 'min:0', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'por_defecto' => ['boolean'],
        ]);

        // Si se marca como por defecto, desmarcar otros
        if ($request->boolean('por_defecto')) {
            DB::table('impuestos')->where('id', '!=', $id)->update(['por_defecto' => false]);
        }

        DB::table('impuestos')->where('id', $id)->update([
            'nombre' => $data['nombre'],
            'codigo' => $data['codigo'],
            'porcentaje' => $data['porcentaje'],
            'descripcion' => $data['descripcion'] ?? null,
            'por_defecto' => $request->boolean('por_defecto'),
            'updated_at' => now(),
        ]);

        return redirect()->route('impuestos.view')->with('success', 'Impuesto actualizado correctamente');
    }

    public function destroy($id)
    {
        DB::table('impuestos')->where('id', $id)->delete();
        return redirect()->route('impuestos.view')->with('success', 'Impuesto eliminado correctamente');
    }

    public function toggle($id)
    {
        $impuesto = DB::table('impuestos')->where('id', $id)->first();
        
        DB::table('impuestos')->where('id', $id)->update([
            'activo' => !$impuesto->activo,
            'updated_at' => now(),
        ]);

        return redirect()->route('impuestos.view')->with('success', 'Estado actualizado correctamente');
    }
}
