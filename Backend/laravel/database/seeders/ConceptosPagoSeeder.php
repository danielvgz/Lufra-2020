<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConceptosPagoSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            'Abono de nómina',
            'Deducción',
            'Bono de salario',
            'Compensación',
        ];
        foreach ($items as $nombre) {
            DB::table('conceptos_pago')->updateOrInsert(['nombre' => $nombre], ['created_at'=>now(),'updated_at'=>now()]);
        }
    }
}
