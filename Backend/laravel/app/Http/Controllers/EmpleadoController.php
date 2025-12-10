<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmpleadoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = DB::table('users as u')
            ->join('rol_usuario as ru', 'ru.user_id', '=', 'u.id')
            ->join('roles as r', 'r.id', '=', 'ru.rol_id')
            ->where('r.nombre', 'empleado')
            ->select('u.id', 'u.name', 'u.email');
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('u.name', 'like', "%{$search}%")
                  ->orWhere('u.email', 'like', "%{$search}%");
                if (is_numeric($search)) {
                    $q->orWhere('u.id', '=', $search);
                }
            });
        }
        
        $usuarios = $query->orderBy('u.id', 'desc')->paginate(15);
        $detalle = $request->input('detalle');

        return view('empleados', compact('usuarios', 'detalle'));
    }

    public function detalle($userId)
    {
        return redirect()->route('empleados.index', ['detalle' => $userId]);
    }

    public function crear(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $userId = DB::table('users')->insertGetId([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($data['password']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Asignar rol de empleado
        $rolId = DB::table('roles')->where('nombre', 'empleado')->value('id');
        if ($rolId) {
            DB::table('rol_usuario')->insert([
                'user_id' => $userId,
                'rol_id' => $rolId,
            ]);
        }

        return redirect()->route('empleados.index')->with('success', 'Empleado creado correctamente');
    }

    public function editar(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        // Verificar que el email no esté duplicado
        $exists = DB::table('users')
            ->where('email', $data['email'])
            ->where('id', '!=', $data['user_id'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['email' => 'El email ya existe'])->withInput();
        }

        DB::table('users')->where('id', $data['user_id'])->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'updated_at' => now(),
        ]);

        return redirect()->route('empleados.index')->with('success', 'Empleado actualizado correctamente');
    }

    public function eliminar(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        DB::table('users')->where('id', $data['user_id'])->delete();

        return redirect()->route('empleados.index')->with('success', 'Empleado eliminado correctamente');
    }

    public function cambiarPassword(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        DB::table('users')->where('id', $data['user_id'])->update([
            'password' => \Illuminate\Support\Facades\Hash::make($data['password']),
            'updated_at' => now(),
        ]);

        return redirect()->route('empleados.index')->with('success', 'Contraseña actualizada correctamente');
    }

    public function asignarDepartamento(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'department_id' => ['required', 'integer', 'exists:departamentos,id'],
        ]);

        // Verificar si el empleado existe
        $empleado = DB::table('empleados')->where('user_id', $data['user_id'])->first();

        if ($empleado) {
            // Actualizar departamento
            DB::table('empleados')->where('user_id', $data['user_id'])->update([
                'department_id' => $data['department_id'],
                'updated_at' => now(),
            ]);
        } else {
            // Crear registro de empleado
            $user = DB::table('users')->find($data['user_id']);
            $nombres = explode(' ', $user->name);
            
            DB::table('empleados')->insert([
                'user_id' => $data['user_id'],
                'numero_empleado' => 'EMP' . str_pad($data['user_id'], 4, '0', STR_PAD_LEFT),
                'nombre' => $nombres[0] ?? '',
                'apellido' => $nombres[1] ?? '',
                'correo' => $user->email,
                'department_id' => $data['department_id'],
                'fecha_ingreso' => now()->toDateString(),
                'estado' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('empleados.index')->with('success', 'Departamento asignado correctamente');
    }
}
