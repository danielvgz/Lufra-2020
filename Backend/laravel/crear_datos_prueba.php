<?php

/**
 * Script para crear datos de prueba
 * Ejecutar: php crear_datos_prueba.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Database\Seeders\DatosPruebaSeeder;

echo "\n";
echo "========================================\n";
echo "   CREANDO DATOS DE PRUEBA\n";
echo "========================================\n";
echo "\n";

try {
    $seeder = new DatosPruebaSeeder();
    $seeder->run();
    
    echo "\n";
    echo "========================================\n";
    echo "   ✅ PROCESO COMPLETADO\n";
    echo "========================================\n";
    echo "\n";
    echo "Los datos han sido creados exitosamente.\n";
    echo "Ahora puedes:\n";
    echo "  1. Ver los períodos de nómina en /recibos-pagos\n";
    echo "  2. Ver los pagos y probar la paginación\n";
    echo "  3. Ver las notificaciones en /notificaciones\n";
    echo "  4. Probar aceptar/rechazar pagos\n";
    echo "\n";
    
} catch (\Exception $e) {
    echo "\n";
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nDetalles:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
