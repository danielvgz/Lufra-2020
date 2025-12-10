<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // Búsqueda de permisos
        $searchPermisos = $request->input('search_permisos');
        $queryPermisos = DB::table('permisos')
            ->select('id', 'nombre', 'descripcion')
            ->orderBy('nombre');
        
        if ($searchPermisos) {
            $queryPermisos->where(function($q) use ($searchPermisos) {
                $q->where('nombre', 'like', "%{$searchPermisos}%")
                  ->orWhere('descripcion', 'like', "%{$searchPermisos}%");
            });
        }
        
        $lista = $queryPermisos->paginate(15, ['*'], 'permisos_page');
        
        // Obtener todos los roles para asignación
        $roles = DB::table('roles')
            ->select('id', 'nombre')
            ->orderBy('nombre')
            ->get();
        
        return view('permissions', compact('lista', 'roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required','string','max:100'],
            'descripcion' => ['nullable','string','max:255']
        ], [
            'nombre.required' => 'El nombre del permiso es obligatorio.',
            'nombre.string' => 'El nombre del permiso debe ser texto.',
            'nombre.max' => 'El nombre del permiso no debe superar 100 caracteres.',
            'descripcion.max' => 'La descripción es demasiado larga.'
        ]);
        
        DB::table('permisos')->updateOrInsert(['nombre'=>$data['nombre']], ['descripcion'=>$data['descripcion'] ?? null,'created_at'=>now(),'updated_at'=>now()]);
        
        return redirect()->route('permissions.index');
    }

    public function asignar(Request $request)
    {
        $data = $request->validate([
            'rol_id' => ['required','integer'],
            'permisos' => ['array']
        ], [
            'rol_id.required' => 'El rol es obligatorio.',
            'rol_id.integer' => 'El identificador de rol debe ser numérico.',
            'permisos.array' => 'Los permisos deben enviarse como lista.'
        ]);
        
        $rid = (int)$data['rol_id'];
        DB::table('permiso_rol')->where('rol_id',$rid)->delete();
        foreach(($data['permisos'] ?? []) as $pid){
            DB::table('permiso_rol')->updateOrInsert(['rol_id'=>$rid,'permiso_id'=>$pid], []);
        }
        
        return redirect()->route('permissions.index');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'permiso_id' => ['required','integer'],
            'nombre' => ['required','string','max:100'],
            'descripcion' => ['nullable','string','max:255']
        ], [
            'permiso_id.required' => 'El ID del permiso es obligatorio.',
            'permiso_id.integer' => 'El ID del permiso debe ser un número.',
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser texto.',
            'nombre.max' => 'El nombre no debe superar 100 caracteres.',
            'descripcion.string' => 'La descripción debe ser texto.',
            'descripcion.max' => 'La descripción no debe superar 255 caracteres.',
        ]);
        
        DB::table('permisos')->where('id',$data['permiso_id'])->update([
            'nombre'=>$data['nombre'],
            'descripcion'=>$data['descripcion'] ?? null,
            'updated_at'=>now()
        ]);
        
        return redirect()->route('permissions.index');
    }

    public function destroy(Request $request)
    {
        $data = $request->validate([
            'permiso_id' => ['required','integer'],
        ], [
            'permiso_id.required' => 'El ID del permiso es obligatorio.',
            'permiso_id.integer' => 'El ID del permiso debe ser un número.',
        ]);
        
        $pid = (int)$data['permiso_id'];
        DB::table('permiso_rol')->where('permiso_id',$pid)->delete();
        DB::table('permisos')->where('id',$pid)->delete();
        
        return redirect()->route('permissions.index');
    }
}
