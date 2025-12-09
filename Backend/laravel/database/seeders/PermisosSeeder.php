<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermisosSeeder extends Seeder
{
    public function run(): void
    {
        $permisos = [
            ['nombre' => 'ver_dashboard', 'descripcion' => 'Acceder al dashboard'],
            ['nombre' => 'gestionar_empleados', 'descripcion' => 'CRUD de empleados'],
            ['nombre' => 'gestionar_departamentos', 'descripcion' => 'CRUD de departamentos'],
            ['nombre' => 'gestionar_contratos', 'descripcion' => 'CRUD de contratos'],
            ['nombre' => 'gestionar_periodos', 'descripcion' => 'CRUD de periodos de nómina'],
            ['nombre' => 'gestionar_recibos_pagos', 'descripcion' => 'Emitir recibos y registrar pagos'],
            ['nombre' => 'asignar_roles', 'descripcion' => 'Asignar roles a usuarios'],
        ];

        foreach ($permisos as $permiso) {
            DB::table('permisos')->updateOrInsert(
                ['nombre' => $permiso['nombre']],
                [
                    'descripcion' => $permiso['descripcion'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // asignar todos los permisos al rol administrador
        $adminRolId = DB::table('roles')->where('nombre','administrador')->value('id');
        if ($adminRolId) {
            $permisos = DB::table('permisos')->pluck('id');
            foreach ($permisos as $pid) {
                DB::table('permiso_rol')->updateOrInsert(
                    ['rol_id' => $adminRolId, 'permiso_id' => $pid],
                    []
                );
            }
        }
    }
}
