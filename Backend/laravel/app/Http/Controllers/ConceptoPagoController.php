<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConceptoPagoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Verificar rol de administrador
        $role = DB::table('rol_usuario')
            ->join('roles', 'roles.id', '=', 'rol_usuario.rol_id')
            ->where('rol_usuario.user_id', auth()->id())
            ->value('roles.nombre');

        if ($role !== 'administrador') {
            abort(403);
        }

        // Insertar conceptos por defecto si no existen
        $count = DB::table('conceptos_pago')->count();
        if ($count === 0) {
            DB::table('conceptos_pago')->insert([
                ['nombre' => 'Nómina', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Bono', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Anticipo', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Vacaciones', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        $items = DB::table('conceptos_pago')
            ->orderBy('nombre')
            ->paginate(20);

        return view('conceptos', compact('items'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:conceptos_pago,nombre'],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser texto.',
            'nombre.max' => 'El nombre no debe superar 100 caracteres.',
            'nombre.unique' => 'El nombre ya existe.',
        ]);

        DB::table('conceptos_pago')->insert([
            'nombre' => $data['nombre'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('conceptos.view')->with('success', 'Concepto de pago creado correctamente');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'id' => ['required', 'integer', 'exists:conceptos_pago,id'],
            'nombre' => ['required', 'string', 'max:100'],
        ], [
            'id.required' => 'El ID es obligatorio.',
            'id.integer' => 'El ID debe ser un número.',
            'id.exists' => 'El concepto no existe.',
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser texto.',
            'nombre.max' => 'El nombre no debe superar 100 caracteres.',
        ]);

        // Verificar que el nombre no esté duplicado
        $exists = DB::table('conceptos_pago')
            ->where('nombre', $data['nombre'])
            ->where('id', '!=', $data['id'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['nombre' => 'El nombre ya existe'])->withInput();
        }

        DB::table('conceptos_pago')->where('id', $data['id'])->update([
            'nombre' => $data['nombre'],
            'updated_at' => now(),
        ]);

        return redirect()->route('conceptos.view')->with('success', 'Concepto de pago actualizado correctamente');
    }

    public function destroy(Request $request)
    {
        $data = $request->validate([
            'id' => ['required', 'integer', 'exists:conceptos_pago,id'],
        ], [
            'id.required' => 'El ID es obligatorio.',
            'id.integer' => 'El ID debe ser un número.',
            'id.exists' => 'El concepto no existe.',
        ]);

        DB::table('conceptos_pago')->where('id', $data['id'])->delete();

        return redirect()->route('conceptos.view')->with('success', 'Concepto de pago eliminado correctamente');
    }
}
