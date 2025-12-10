<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatosPruebaSeeder extends Seeder
{
    public function run(): void
    {
        echo "Creando datos de prueba...\n";

        // 1. Crear períodos de nómina (últimos 6 meses)
        echo "Creando períodos de nómina...\n";
        $periodos = [];
        for ($i = 5; $i >= 0; $i--) {
            $fecha = Carbon::now()->subMonths($i);
            $codigo = $fecha->format('Y-m');
            
            // Verificar si ya existe
            $periodoExistente = DB::table('periodos_nomina')->where('codigo', $codigo)->first();
            if ($periodoExistente) {
                $periodos[] = $periodoExistente->id;
                echo "  ⚠ Período {$codigo} ya existe, usando existente\n";
                continue;
            }
            
            $periodoId = DB::table('periodos_nomina')->insertGetId([
                'codigo' => $codigo,
                'fecha_inicio' => $fecha->copy()->startOfMonth()->format('Y-m-d'),
                'fecha_fin' => $fecha->copy()->endOfMonth()->format('Y-m-d'),
                'estado' => $i >= 2 ? 'cerrado' : 'abierto',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $periodos[] = $periodoId;
            echo "  ✓ Período {$codigo} creado\n";
        }

        // 2. Obtener empleados existentes
        $empleados = DB::table('empleados')->get();
        if ($empleados->isEmpty()) {
            echo "⚠ No hay empleados en la base de datos. Ejecuta primero EmpleadosSeeder\n";
            return;
        }
        echo "Encontrados {$empleados->count()} empleados\n";

        // 3. Crear recibos para cada período y empleado
        echo "Creando recibos de nómina...\n";
        $recibos = [];
        $nuevosRecibos = 0;
        foreach ($periodos as $periodoId) {
            foreach ($empleados as $empleado) {
                // Verificar si ya existe el recibo
                $reciboExistente = DB::table('recibos')
                    ->where('periodo_nomina_id', $periodoId)
                    ->where('empleado_id', $empleado->id)
                    ->first();
                
                if ($reciboExistente) {
                    $recibos[] = [
                        'id' => $reciboExistente->id,
                        'empleado_id' => $reciboExistente->empleado_id,
                        'neto' => $reciboExistente->neto,
                    ];
                    continue;
                }
                
                $salarioBruto = rand(8000, 50000);
                $deducciones = $salarioBruto * 0.15;
                $neto = $salarioBruto - $deducciones;

                $reciboId = DB::table('recibos')->insertGetId([
                    'periodo_nomina_id' => $periodoId,
                    'empleado_id' => $empleado->id,
                    'bruto' => $salarioBruto,
                    'deducciones' => $deducciones,
                    'neto' => $neto,
                    'estado' => 'aprobado',
                    'detalle_deducciones' => json_encode([
                        'impuesto' => $deducciones * 0.7,
                        'seguridad_social' => $deducciones * 0.3,
                    ]),
                    'locked_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $recibos[] = [
                    'id' => $reciboId,
                    'empleado_id' => $empleado->id,
                    'neto' => $neto,
                ];
                $nuevosRecibos++;
            }
        }
        echo "  ✓ " . count($recibos) . " recibos totales (" . $nuevosRecibos . " nuevos)\n";

        // 4. Obtener métodos de pago
        $metodos = DB::table('metodos_pago')->pluck('nombre')->toArray();
        if (empty($metodos)) {
            $metodos = ['Transferencia bancaria', 'Pago móvil', 'Efectivo', 'Cheque'];
        }

        // 5. Obtener monedas
        $monedas = DB::table('monedas')->pluck('codigo')->toArray();
        if (empty($monedas)) {
            $monedas = ['USD', 'VES', 'EUR'];
        }

        // 6. Crear pagos para cada recibo (múltiples pagos por recibo)
        echo "Creando pagos...\n";
        $estados = ['pendiente', 'aceptado', 'rechazado'];
        $totalPagos = 0;
        
        foreach ($recibos as $recibo) {
            // Número aleatorio de pagos por recibo (1-4)
            $numPagos = rand(1, 4);
            $montoRestante = $recibo['neto'];
            
            for ($i = 0; $i < $numPagos; $i++) {
                // Último pago lleva el resto, otros son proporcionales
                if ($i == $numPagos - 1) {
                    $importe = $montoRestante;
                } else {
                    $importe = round($montoRestante / ($numPagos - $i) * rand(30, 70) / 100, 2);
                    $montoRestante -= $importe;
                }

                $estado = $estados[array_rand($estados)];
                $metodo = $metodos[array_rand($metodos)];
                $moneda = $monedas[array_rand($monedas)];

                DB::table('pagos')->insert([
                    'recibo_id' => $recibo['id'],
                    'importe' => $importe,
                    'moneda' => $moneda,
                    'metodo' => $metodo,
                    'referencia' => 'REF-' . strtoupper(substr(md5(uniqid()), 0, 10)),
                    'estado' => $estado,
                    'pagado_en' => $estado != 'pendiente' ? now()->subDays(rand(0, 30)) : null,
                    'created_at' => now()->subDays(rand(0, 180)),
                    'updated_at' => now(),
                ]);
                $totalPagos++;
            }
        }
        echo "  ✓ " . $totalPagos . " pagos creados\n";

        // 7. Crear notificaciones para los administradores
        echo "Creando notificaciones de prueba...\n";
        $admins = DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'Administrador')
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->select('users.*')
            ->get();

        $tiposNotificacion = [
            ['type' => 'recibo_creado', 'title' => 'Nuevo Recibo de Pago', 'message' => 'Se ha generado un nuevo recibo de pago.'],
            ['type' => 'recibo_aceptado', 'title' => 'Pago Aceptado', 'message' => 'Un empleado ha aceptado su pago.'],
            ['type' => 'recibo_rechazado', 'title' => 'Pago Rechazado', 'message' => 'Un empleado ha rechazado su pago.'],
            ['type' => 'departamento_creado', 'title' => 'Departamento Creado', 'message' => 'Se ha creado un nuevo departamento.'],
            ['type' => 'contrato_creado', 'title' => 'Contrato Creado', 'message' => 'Se ha creado un nuevo contrato.'],
            ['type' => 'periodo_cerrado_pagos_pendientes', 'title' => 'Período Cerrado con Pagos Pendientes', 'message' => 'Se ha cerrado un período con pagos pendientes.'],
        ];

        foreach ($admins as $admin) {
            // Crear 15-25 notificaciones por admin
            $numNotificaciones = rand(15, 25);
            for ($i = 0; $i < $numNotificaciones; $i++) {
                $notif = $tiposNotificacion[array_rand($tiposNotificacion)];
                DB::table('notifications')->insert([
                    'user_id' => $admin->id,
                    'type' => $notif['type'],
                    'title' => $notif['title'],
                    'message' => $notif['message'],
                    'data' => json_encode([
                        'recibo_id' => $recibos[array_rand($recibos)]['id'],
                    ]),
                    'read' => rand(0, 1), // 50% leídas, 50% sin leer
                    'created_at' => now()->subDays(rand(0, 60)),
                    'updated_at' => now(),
                ]);
            }
        }
        echo "  ✓ Notificaciones creadas para " . $admins->count() . " administrador(es)\n";

        // 8. Crear departamentos adicionales si hay pocos
        $deptosCount = DB::table('departamentos')->count();
        if ($deptosCount < 10) {
            echo "Creando departamentos adicionales...\n";
            $departamentos = [
                'Recursos Humanos', 'Finanzas', 'Marketing', 'Ventas', 'Soporte Técnico',
                'Desarrollo', 'Diseño', 'Operaciones', 'Legal', 'Compras',
                'Logística', 'Calidad', 'Investigación', 'Capacitación'
            ];
            
            foreach ($departamentos as $nombre) {
                if (DB::table('departamentos')->where('nombre', $nombre)->doesntExist()) {
                    DB::table('departamentos')->insert([
                        'codigo' => strtoupper(substr($nombre, 0, 3)),
                        'nombre' => $nombre,
                        'descripcion' => "Departamento de {$nombre}",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            echo "  ✓ Departamentos adicionales creados\n";
        }

        echo "\n✅ DATOS DE PRUEBA CREADOS EXITOSAMENTE\n";
        echo "Resumen:\n";
        echo "  - Períodos de nómina: " . count($periodos) . "\n";
        echo "  - Recibos: " . count($recibos) . "\n";
        echo "  - Pagos: {$totalPagos}\n";
        echo "  - Notificaciones: " . ($admins->count() * rand(15, 25)) . "\n";
    }
}
