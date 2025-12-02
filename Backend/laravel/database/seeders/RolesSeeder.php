<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        DB::table('roles')->insert([
            ['nombre' => 'administrador', 'descripcion' => 'Admin del sistema', 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'empleado', 'descripcion' => 'Empleado', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
