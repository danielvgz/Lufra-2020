<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Crear permiso si no existe
        $permisoId = DB::table('permisos')->where('nombre', 'gestionar_departamentos')->value('id');
        
        if (!$permisoId) {
            $permisoId = DB::table('permisos')->insertGetId([
                'nombre' => 'gestionar_departamentos',
                'descripcion' => 'Permite crear, editar y eliminar departamentos',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Asignar permiso al rol administrador automáticamente
        $rolAdminId = DB::table('roles')->where('nombre', 'administrador')->value('id');
        
        if ($rolAdminId && $permisoId) {
            DB::table('permiso_rol')->updateOrInsert(
                [
                    'rol_id' => $rolAdminId,
                    'permiso_id' => $permisoId,
                ],
                []
            );
        }
    }

    public function down(): void
    {
        // Eliminar el permiso y sus relaciones
        $permisoId = DB::table('permisos')->where('nombre', 'gestionar_departamentos')->value('id');
        
        if ($permisoId) {
            DB::table('permiso_rol')->where('permiso_id', $permisoId)->delete();
            DB::table('permisos')->where('id', $permisoId)->delete();
        }
    }
};
