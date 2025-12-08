<?php

// Script para verificar el estado de las notificaciones

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Verificando Sistema de Notificaciones ===\n\n";

// 1. Verificar si existe la tabla
try {
    $exists = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name='notifications'");
    if (empty($exists)) {
        echo "❌ ERROR: La tabla 'notifications' NO existe\n";
        echo "   Solución: Ejecutar 'php artisan migrate'\n\n";
    } else {
        echo "✅ La tabla 'notifications' existe\n\n";
        
        // 2. Contar notificaciones
        $count = DB::table('notifications')->count();
        echo "📊 Total de notificaciones: $count\n\n";
        
        // 3. Mostrar las últimas 5 notificaciones
        if ($count > 0) {
            echo "📋 Últimas 5 notificaciones:\n";
            $notifications = DB::table('notifications')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            
            foreach ($notifications as $notif) {
                $read = $notif->read ? '✓' : '✗';
                echo "  [$read] ID: {$notif->id} - {$notif->title} (User: {$notif->user_id})\n";
                echo "      {$notif->message}\n";
                echo "      Creada: {$notif->created_at}\n\n";
            }
        }
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "   La tabla 'notifications' probablemente no existe.\n";
    echo "   Solución: Ejecutar 'php artisan migrate'\n\n";
}

// 4. Verificar usuarios
echo "👥 Usuarios en el sistema:\n";
$users = DB::table('users')->select('id', 'name', 'email')->get();
foreach ($users as $user) {
    echo "  ID: {$user->id} - {$user->name} ({$user->email})\n";
}

echo "\n=== Fin de la verificación ===\n";
