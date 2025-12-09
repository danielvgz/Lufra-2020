<?php

/**
 * Script de prueba para verificar la notificación de período cerrado con pagos pendientes
 * Ejecutar: php test_periodo_cerrado_notificacion.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\NotificationHelper;

try {
    echo "=== TEST: Notificación de Período Cerrado con Pagos Pendientes ===\n\n";
    
    // 1. Verificar que existe la tabla notifications
    $tableExists = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name='notifications'");
    if (empty($tableExists)) {
        die("❌ ERROR: La tabla 'notifications' no existe. Ejecuta: php artisan migrate\n");
    }
    echo "✓ Tabla 'notifications' existe\n";
    
    // 2. Obtener un período de prueba
    $periodo = DB::table('periodos_nomina')->first();
    if (!$periodo) {
        die("❌ ERROR: No hay períodos de nómina. Crea uno primero.\n");
    }
    echo "✓ Período encontrado: {$periodo->codigo} (ID: {$periodo->id})\n";
    
    // 3. Contar notificaciones antes
    $countBefore = DB::table('notifications')->count();
    echo "✓ Notificaciones actuales: {$countBefore}\n\n";
    
    // 4. Simular el cierre con pagos pendientes
    echo "Simulando cierre de período con 3 pagos pendientes...\n";
    NotificationHelper::notifyPeriodoCerradoConPagosPendientes($periodo->id, 3);
    
    // 5. Contar notificaciones después
    $countAfter = DB::table('notifications')->count();
    $nuevas = $countAfter - $countBefore;
    echo "✓ Notificaciones después: {$countAfter}\n";
    echo "✓ Notificaciones nuevas creadas: {$nuevas}\n\n";
    
    // 6. Mostrar las notificaciones creadas
    $notificaciones = DB::table('notifications')
        ->where('type', 'periodo_cerrado_pagos_pendientes')
        ->orderBy('id', 'desc')
        ->limit(5)
        ->get();
    
    echo "=== Últimas notificaciones de período cerrado ===\n";
    foreach ($notificaciones as $notif) {
        echo "ID: {$notif->id}\n";
        echo "Usuario: {$notif->user_id}\n";
        echo "Título: {$notif->title}\n";
        echo "Mensaje: {$notif->message}\n";
        echo "Tipo: {$notif->type}\n";
        echo "Leída: " . ($notif->read ? 'Sí' : 'No') . "\n";
        echo "Creada: {$notif->created_at}\n";
        echo "---\n";
    }
    
    echo "\n✅ TEST COMPLETADO\n";
    echo "Visita: http://tu-dominio/notificaciones para ver las notificaciones\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
