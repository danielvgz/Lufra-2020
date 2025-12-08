<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Test de Notificaciones ===\n\n";

try {
    // Verificar si existe la tabla
    $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name='notifications'");
    
    if (empty($tables)) {
        echo "❌ ERROR: La tabla 'notifications' NO existe\n";
        echo "   SOLUCIÓN: Ejecuta 'php artisan migrate'\n\n";
        exit(1);
    }
    
    echo "✅ La tabla 'notifications' existe\n\n";
    
    // Obtener usuarios administradores
    $rolAdminId = DB::table('roles')->where('nombre', 'administrador')->value('id');
    if (!$rolAdminId) {
        echo "⚠️  No existe el rol 'administrador'\n\n";
    } else {
        echo "✅ Rol administrador existe (ID: $rolAdminId)\n\n";
        
        $admins = DB::table('users')
            ->join('rol_usuario', 'users.id', '=', 'rol_usuario.user_id')
            ->where('rol_usuario.rol_id', $rolAdminId)
            ->select('users.id', 'users.name', 'users.email')
            ->get();
        
        echo "👥 Administradores encontrados: " . $admins->count() . "\n";
        foreach ($admins as $admin) {
            echo "   - {$admin->name} (ID: {$admin->id})\n";
        }
        echo "\n";
        
        // Verificar permiso
        $permiso = DB::table('permisos')->where('nombre', 'gestionar_departamentos')->first();
        if ($permiso) {
            echo "✅ Permiso 'gestionar_departamentos' existe (ID: {$permiso->id})\n\n";
        } else {
            echo "⚠️  Permiso 'gestionar_departamentos' NO existe\n";
            echo "   Se crearán notificaciones solo para administradores\n\n";
        }
    }
    
    // Contar notificaciones
    $totalNotif = DB::table('notifications')->count();
    echo "📊 Total de notificaciones en la BD: $totalNotif\n\n";
    
    if ($totalNotif > 0) {
        echo "📋 Últimas 5 notificaciones:\n";
        $notifs = DB::table('notifications')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        foreach ($notifs as $n) {
            $read = $n->read ? '✓' : '✗';
            echo "  [$read] {$n->type} - {$n->title}\n";
            echo "      Usuario: {$n->user_id} | Creada: {$n->created_at}\n";
        }
    }
    
    echo "\n=== Test Completado ===\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n\n";
    echo "Stacktrace:\n" . $e->getTraceAsString() . "\n";
}
