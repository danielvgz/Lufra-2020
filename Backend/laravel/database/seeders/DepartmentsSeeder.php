<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        DB::table('departamentos')->insert([
            ['codigo' => 'RRHH', 'nombre' => 'Recursos Humanos', 'descripcion' => 'Gestión de personal', 'created_at' => $now, 'updated_at' => $now],
            ['codigo' => 'FIN', 'nombre' => 'Finanzas', 'descripcion' => 'Contabilidad y pagos', 'created_at' => $now, 'updated_at' => $now],
            ['codigo' => 'IT', 'nombre' => 'Tecnología', 'descripcion' => 'Sistemas y soporte', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
