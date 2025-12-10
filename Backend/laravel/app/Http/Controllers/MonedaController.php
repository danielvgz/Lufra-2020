<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonedaController extends Controller
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

        // Insertar monedas por defecto si no existen
        $count = DB::table('monedas')->count();
        if ($count === 0) {
            DB::table('monedas')->insert([
                ['codigo' => 'USD', 'nombre' => 'Dólar', 'simbolo' => '$', 'created_at' => now(), 'updated_at' => now()],
                ['codigo' => 'VES', 'nombre' => 'Bolívar', 'simbolo' => 'Bs', 'created_at' => now(), 'updated_at' => now()],
                ['codigo' => 'EUR', 'nombre' => 'Euro', 'simbolo' => '€', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        $items = DB::table('monedas')
            ->orderBy('nombre')
            ->paginate(20);

        return view('monedas', compact('items'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo' => ['required', 'string', 'max:10', 'unique:monedas,codigo'],
            'nombre' => ['required', 'string', 'max:100'],
            'simbolo' => ['required', 'string', 'max:10'],
        ], [
            'codigo.required' => 'El código es obligatorio.',
            'codigo.string' => 'El código debe ser texto.',
            'codigo.max' => 'El código no debe superar 10 caracteres.',
            'codigo.unique' => 'El código ya existe.',
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser texto.',
            'nombre.max' => 'El nombre no debe superar 100 caracteres.',
            'simbolo.required' => 'El símbolo es obligatorio.',
            'simbolo.string' => 'El símbolo debe ser texto.',
            'simbolo.max' => 'El símbolo no debe superar 10 caracteres.',
        ]);

        DB::table('monedas')->insert([
            'codigo' => strtoupper($data['codigo']),
            'nombre' => $data['nombre'],
            'simbolo' => $data['simbolo'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('monedas.view')->with('success', 'Moneda creada correctamente');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'id' => ['required', 'integer', 'exists:monedas,id'],
            'codigo' => ['required', 'string', 'max:10'],
            'nombre' => ['required', 'string', 'max:100'],
            'simbolo' => ['required', 'string', 'max:10'],
        ], [
            'id.required' => 'El ID es obligatorio.',
            'id.integer' => 'El ID debe ser un número.',
            'id.exists' => 'La moneda no existe.',
            'codigo.required' => 'El código es obligatorio.',
            'codigo.string' => 'El código debe ser texto.',
            'codigo.max' => 'El código no debe superar 10 caracteres.',
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser texto.',
            'nombre.max' => 'El nombre no debe superar 100 caracteres.',
            'simbolo.required' => 'El símbolo es obligatorio.',
            'simbolo.string' => 'El símbolo debe ser texto.',
            'simbolo.max' => 'El símbolo no debe superar 10 caracteres.',
        ]);

        // Verificar que el código no esté duplicado
        $exists = DB::table('monedas')
            ->where('codigo', strtoupper($data['codigo']))
            ->where('id', '!=', $data['id'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['codigo' => 'El código ya existe'])->withInput();
        }

        DB::table('monedas')->where('id', $data['id'])->update([
            'codigo' => strtoupper($data['codigo']),
            'nombre' => $data['nombre'],
            'simbolo' => $data['simbolo'],
            'updated_at' => now(),
        ]);

        return redirect()->route('monedas.view')->with('success', 'Moneda actualizada correctamente');
    }

    public function destroy(Request $request)
    {
        $data = $request->validate([
            'id' => ['required', 'integer', 'exists:monedas,id'],
        ], [
            'id.required' => 'El ID es obligatorio.',
            'id.integer' => 'El ID debe ser un número.',
            'id.exists' => 'La moneda no existe.',
        ]);

        DB::table('monedas')->where('id', $data['id'])->delete();

        return redirect()->route('monedas.view')->with('success', 'Moneda eliminada correctamente');
    }
}
