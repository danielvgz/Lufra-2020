<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Employee::query()->with(['department','user']);

        // Filtros
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->integer('department_id'));
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }
        if ($request->filled('dni')) {
            $query->where('identificador_fiscal', $request->input('dni'));
        }

        // Búsqueda general por nombre, apellido, correo o identificador fiscal
        $term = $request->input('q', $request->input('search'));
        if (is_string($term) && $term !== '') {
            $like = '%' . str_replace(['%','_'], ['\\%','\\_'], $term) . '%';
            $query->where(function ($q) use ($like) {
                $q->where('nombre', 'like', $like)
                  ->orWhere('apellido', 'like', $like)
                  ->orWhere('correo', 'like', $like)
                  ->orWhere('identificador_fiscal', 'like', $like);
            });
        }

        return response()->json($query->orderBy('apellido')->orderBy('nombre')->paginate(1));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'numero_empleado' => ['nullable','string','max:50'],
            'nombre' => ['required','string','max:100'],
            'apellido' => ['required','string','max:100'],
            'correo' => ['required','email','max:255'],
            'identificador_fiscal' => ['nullable','string','max:50'],
            'fecha_nacimiento' => ['nullable','date'],
            'fecha_ingreso' => ['required','date'],
            'fecha_baja' => ['nullable','date'],
            'estado' => ['sometimes','required', Rule::in(['activo','inactivo','permiso'])],
            'telefono' => ['nullable','string','max:50'],
            'direccion' => ['nullable','string','max:255'],
            'banco' => ['nullable','string','max:100'],
            'cuenta_bancaria' => ['nullable','string','max:100'],
            'notas' => ['nullable','string','max:500'],
            'department_id' => ['required','integer','exists:departments,id'],
            'puesto' => ['nullable','string','max:100'],
            'salario_base' => ['nullable','numeric','min:0'],
        ], [
            'numero_empleado.string' => 'El número de empleado debe ser texto.',
            'numero_empleado.max' => 'El número de empleado no debe superar 50 caracteres.',
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser texto.',
            'nombre.max' => 'El nombre no debe superar 100 caracteres.',
            'apellido.required' => 'El apellido es obligatorio.',
            'apellido.string' => 'El apellido debe ser texto.',
            'apellido.max' => 'El apellido no debe superar 100 caracteres.',
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'El correo debe ser válido.',
            'correo.max' => 'El correo no debe superar 255 caracteres.',
            'identificador_fiscal.string' => 'El identificador fiscal debe ser texto.',
            'identificador_fiscal.max' => 'El identificador fiscal no debe superar 50 caracteres.',
            'fecha_nacimiento.date' => 'La fecha de nacimiento debe ser una fecha válida.',
            'fecha_ingreso.required' => 'La fecha de ingreso es obligatoria.',
            'fecha_ingreso.date' => 'La fecha de ingreso debe ser una fecha válida.',
            'fecha_baja.date' => 'La fecha de baja debe ser una fecha válida.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado debe ser activo, inactivo o permiso.',
            'telefono.string' => 'El teléfono debe ser texto.',
            'telefono.max' => 'El teléfono no debe superar 50 caracteres.',
            'direccion.string' => 'La dirección debe ser texto.',
            'direccion.max' => 'La dirección no debe superar 255 caracteres.',
            'banco.string' => 'El banco debe ser texto.',
            'banco.max' => 'El banco no debe superar 100 caracteres.',
            'cuenta_bancaria.string' => 'La cuenta bancaria debe ser texto.',
            'cuenta_bancaria.max' => 'La cuenta bancaria no debe superar 100 caracteres.',
            'notas.string' => 'Las notas deben ser texto.',
            'notas.max' => 'Las notas no deben superar 500 caracteres.',
            'department_id.required' => 'El departamento es obligatorio.',
            'department_id.integer' => 'El departamento debe ser un número.',
            'department_id.exists' => 'El departamento no existe.',
            'puesto.string' => 'El puesto debe ser texto.',
            'puesto.max' => 'El puesto no debe superar 100 caracteres.',
            'salario_base.numeric' => 'El salario base debe ser un número.',
            'salario_base.min' => 'El salario base debe ser mayor o igual a 0.',
        ]);

        if (!array_key_exists('estado', $data)) {
            $data['estado'] = 'activo';
        }

        // Asegurar usuario con rol "empleado"
        $user = User::firstOrCreate(
            ['email' => $data['correo']],
            ['name' => trim($data['nombre'].' '.$data['apellido']) ?: 'Empleado', 'password' => Str::password(12)]
        );
        $rolId = DB::table('roles')->where('nombre','empleado')->value('id');
        if (!$rolId) {
            $rolId = DB::table('roles')->insertGetId(['nombre'=>'empleado','descripcion'=>null,'created_at'=>now(),'updated_at'=>now()]);
        }
        DB::table('rol_usuario')->updateOrInsert(['user_id'=>$user->id,'rol_id'=>$rolId], []);

        $data['user_id'] = $user->id;

        $employee = Employee::create($data);
        return response()->json($employee->load(['department','user']), 201);
    }

    public function show(Employee $employee): JsonResponse
    {
        return response()->json($employee->load(['department','user']));
    }

    public function update(Request $request, Employee $employee): JsonResponse
    {
        $data = $request->validate([
            'numero_empleado' => ['sometimes','nullable','string','max:50'],
            'nombre' => ['sometimes','required','string','max:100'],
            'apellido' => ['sometimes','required','string','max:100'],
            'correo' => ['sometimes','required','email','max:255'],
            'identificador_fiscal' => ['sometimes','nullable','string','max:50'],
            'fecha_nacimiento' => ['sometimes','nullable','date'],
            'fecha_ingreso' => ['sometimes','required','date'],
            'fecha_baja' => ['sometimes','nullable','date'],
            'estado' => ['sometimes','required', Rule::in(['activo','inactivo','permiso'])],
            'telefono' => ['sometimes','nullable','string','max:50'],
            'direccion' => ['sometimes','nullable','string','max:255'],
            'banco' => ['sometimes','nullable','string','max:100'],
            'cuenta_bancaria' => ['sometimes','nullable','string','max:100'],
            'notas' => ['sometimes','nullable','string','max:500'],
            'department_id' => ['sometimes','required','integer','exists:departments,id'],
            'puesto' => ['sometimes','nullable','string','max:100'],
            'salario_base' => ['sometimes','nullable','numeric','min:0'],
        ], [
            'numero_empleado.string' => 'El número de empleado debe ser texto.',
            'numero_empleado.max' => 'El número de empleado no debe superar 50 caracteres.',
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser texto.',
            'nombre.max' => 'El nombre no debe superar 100 caracteres.',
            'apellido.required' => 'El apellido es obligatorio.',
            'apellido.string' => 'El apellido debe ser texto.',
            'apellido.max' => 'El apellido no debe superar 100 caracteres.',
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'El correo debe ser válido.',
            'correo.max' => 'El correo no debe superar 255 caracteres.',
            'identificador_fiscal.string' => 'El identificador fiscal debe ser texto.',
            'identificador_fiscal.max' => 'El identificador fiscal no debe superar 50 caracteres.',
            'fecha_nacimiento.date' => 'La fecha de nacimiento debe ser una fecha válida.',
            'fecha_ingreso.required' => 'La fecha de ingreso es obligatoria.',
            'fecha_ingreso.date' => 'La fecha de ingreso debe ser una fecha válida.',
            'fecha_baja.date' => 'La fecha de baja debe ser una fecha válida.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado debe ser activo, inactivo o permiso.',
            'telefono.string' => 'El teléfono debe ser texto.',
            'telefono.max' => 'El teléfono no debe superar 50 caracteres.',
            'direccion.string' => 'La dirección debe ser texto.',
            'direccion.max' => 'La dirección no debe superar 255 caracteres.',
            'banco.string' => 'El banco debe ser texto.',
            'banco.max' => 'El banco no debe superar 100 caracteres.',
            'cuenta_bancaria.string' => 'La cuenta bancaria debe ser texto.',
            'cuenta_bancaria.max' => 'La cuenta bancaria no debe superar 100 caracteres.',
            'notas.string' => 'Las notas deben ser texto.',
            'notas.max' => 'Las notas no deben superar 500 caracteres.',
            'department_id.required' => 'El departamento es obligatorio.',
            'department_id.integer' => 'El departamento debe ser un número.',
            'department_id.exists' => 'El departamento no existe.',
            'puesto.string' => 'El puesto debe ser texto.',
            'puesto.max' => 'El puesto no debe superar 100 caracteres.',
            'salario_base.numeric' => 'El salario base debe ser un número.',
            'salario_base.min' => 'El salario base debe ser mayor o igual a 0.',
        ]);

        $employee->update($data);

        // Sincronizar datos básicos con el usuario vinculado
        if ($employee->user_id) {
            $user = User::find($employee->user_id);
            if ($user) {
                if (isset($data['correo'])) { $user->email = $data['correo']; }
                $nombre = $data['nombre'] ?? $employee->nombre;
                $apellido = $data['apellido'] ?? $employee->apellido;
                if ($nombre || $apellido) { $user->name = trim(($nombre ?? '').' '.($apellido ?? '')); }
                $user->save();
            }
        }

        return response()->json($employee->load(['department','user']));
    }

    public function destroy(Employee $employee): JsonResponse
    {
        $employee->delete();
        return response()->json(null, 204);
    }
}
