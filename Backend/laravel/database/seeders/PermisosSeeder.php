<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermisosSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        DB::table('permisos')->insert([
            ['nombre' => 'ver_dashboard', 'descripcion' => 'Acceder al dashboard', 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'gestionar_empleados', 'descripcion' => 'CRUD de empleados', 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'gestionar_departamentos', 'descripcion' => 'CRUD de departamentos', 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'gestionar_contratos', 'descripcion' => 'CRUD de contratos', 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'gestionar_periodos', 'descripcion' => 'CRUD de periodos de nómina', 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'gestionar_recibos_pagos', 'descripcion' => 'Emitir recibos y registrar pagos', 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'asignar_roles', 'descripcion' => 'Asignar roles a usuarios', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // asignar todos los permisos al rol administrador
        $adminRolId = DB::table('roles')->where('nombre','administrador')->value('id');
        $permisos = DB::table('permisos')->pluck('id');
        foreach ($permisos as $pid) {
            DB::table('permiso_rol')->updateOrInsert(['rol_id'=>$adminRolId,'permiso_id'=>$pid], []);
        }
    }
}
