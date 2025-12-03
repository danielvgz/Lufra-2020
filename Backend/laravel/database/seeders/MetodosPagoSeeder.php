<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MetodosPagoSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            'Transferencia',
            'Efectivo',
            'Pago móvil',
        ];
        foreach ($items as $nombre) {
            DB::table('metodos_pago')->updateOrInsert(['nombre' => $nombre], ['created_at'=>now(),'updated_at'=>now()]);
        }
    }
}
