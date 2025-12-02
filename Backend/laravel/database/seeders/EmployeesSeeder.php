<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        DB::table('empleados')->insert([
            [
                'numero_empleado' => 'EMP001',
                'nombre' => 'Juan',
                'apellido' => 'Pérez',
                'correo' => 'juan.perez@example.com',
                'fecha_ingreso' => '2024-01-10',
                'estado' => 'activo',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'numero_empleado' => 'EMP002',
                'nombre' => 'María',
                'apellido' => 'García',
                'correo' => 'maria.garcia@example.com',
                'fecha_ingreso' => '2024-02-03',
                'estado' => 'activo',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
