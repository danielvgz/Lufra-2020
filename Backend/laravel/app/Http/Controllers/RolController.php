<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RolController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // Búsqueda de usuarios
        $searchUsers = $request->input('search_users');
        $queryUsers = DB::table('users')
            ->select('id', 'name', 'email')
            ->orderBy('name');
        
        if ($searchUsers) {
            $queryUsers->where(function($q) use ($searchUsers) {
                $q->where('name', 'like', "%{$searchUsers}%")
                  ->orWhere('email', 'like', "%{$searchUsers}%");
            });
        }
        
        $usuarios = $queryUsers->paginate(20, ['*'], 'users_page');
        
        // Búsqueda de roles
        $searchRoles = $request->input('search_roles');
        $queryRoles = DB::table('roles')
            ->select('id', 'nombre', 'descripcion')
            ->orderBy('nombre');
        
        if ($searchRoles) {
            $queryRoles->where(function($q) use ($searchRoles) {
                $q->where('nombre', 'like', "%{$searchRoles}%")
                  ->orWhere('descripcion', 'like', "%{$searchRoles}%");
            });
        }
        
        $roles = $queryRoles->paginate(10, ['*'], 'roles_page');
        
        return view('roles', compact('usuarios', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate(['nombre' => ['required','string','max:100']], [
            'nombre.required' => 'El nombre del rol es obligatorio.',
            'nombre.string' => 'El nombre del rol debe ser texto.',
            'nombre.max' => 'El nombre del rol no debe superar 100 caracteres.',
        ]);
        
        DB::table('roles')->updateOrInsert(['nombre' => $request->nombre], ['descripcion' => null, 'created_at'=>now(),'updated_at'=>now()]);
        
        return redirect()->route('roles.index');
    }

    public function asignar(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required','integer'],
            'roles' => ['array']
        ], [
            'user_id.required' => 'El usuario es obligatorio.',
            'user_id.integer' => 'El identificador de usuario debe ser un número.',
            'roles.array' => 'Los roles deben enviarse como lista.',
        ]);
        
        $uid = (int)$data['user_id'];
        DB::table('rol_usuario')->where('user_id',$uid)->delete();
        $roles = $data['roles'] ?? [];
        foreach($roles as $rid){
            DB::table('rol_usuario')->updateOrInsert(['user_id'=>$uid,'rol_id'=>$rid], []);
        }
        
        return redirect()->route('roles.index');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'rol_id' => ['required','integer'],
            'nombre' => ['required','string','max:100'],
            'descripcion' => ['nullable','string','max:255']
        ], [
            'rol_id.required' => 'El ID del rol es obligatorio.',
            'rol_id.integer' => 'El ID del rol debe ser un número.',
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser texto.',
            'nombre.max' => 'El nombre no debe superar 100 caracteres.',
            'descripcion.string' => 'La descripción debe ser texto.',
            'descripcion.max' => 'La descripción no debe superar 255 caracteres.',
        ]);
        
        DB::table('roles')->where('id',$data['rol_id'])->update([
            'nombre'=>$data['nombre'],
            'descripcion'=>$data['descripcion'] ?? null,
            'updated_at'=>now()
        ]);
        
        return redirect()->route('roles.index');
    }

    public function destroy(Request $request)
    {
        $data = $request->validate([
            'rol_id' => ['required','integer'],
        ], [
            'rol_id.required' => 'El ID del rol es obligatorio.',
            'rol_id.integer' => 'El ID del rol debe ser un número.',
        ]);
        
        $rid = (int)$data['rol_id'];
        DB::table('rol_usuario')->where('rol_id',$rid)->delete();
        DB::table('permiso_rol')->where('rol_id',$rid)->delete();
        DB::table('roles')->where('id',$rid)->delete();
        
        return redirect()->route('roles.index');
    }
}
