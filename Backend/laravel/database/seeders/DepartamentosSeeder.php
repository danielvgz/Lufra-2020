<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartamentosSeeder extends Seeder
{
    public function run(): void
    {
        $departamentos = [
            ['codigo' => 'RRHH', 'nombre' => 'Recursos Humanos', 'descripcion' => 'Gestión de personal'],
            ['codigo' => 'FIN', 'nombre' => 'Finanzas', 'descripcion' => 'Contabilidad y pagos'],
            ['codigo' => 'IT', 'nombre' => 'Tecnología', 'descripcion' => 'Sistemas y soporte'],
        ];

        foreach ($departamentos as $depto) {
            DB::table('departamentos')->updateOrInsert(
                ['codigo' => $depto['codigo']],
                [
                    'nombre' => $depto['nombre'],
                    'descripcion' => $depto['descripcion'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
