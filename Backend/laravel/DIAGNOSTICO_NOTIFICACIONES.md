# Diagnóstico de Notificaciones

## Problema: No aparecen las notificaciones

Sigue estos pasos para diagnosticar y resolver el problema:

## Paso 1: Ejecutar las Migraciones

Las notificaciones requieren que se cree la tabla en la base de datos. Ejecuta:

```bash
php artisan migrate
```

Esto creará la tabla `notifications` y el permiso `gestionar_departamentos`.

## Paso 2: Verificar en el Navegador

### A. Abrir la Consola del Navegador

1. Abre tu aplicación en el navegador
2. Presiona F12 para abrir las Herramientas de Desarrollo
3. Ve a la pestaña "Console"
4. Recarga la página

### B. Verificar los logs de JavaScript

Deberías ver un mensaje como:
```
Notificaciones recibidas: {count: 0, notifications: Array(0)}
```

Si ves un error, anótalo para saber qué está fallando.

## Paso 3: Probar las Rutas de Diagnóstico

### A. Ruta de Debug

Visita en tu navegador:
```
http://tu-dominio/notifications/debug
```

Esto mostrará:
- Tu ID de usuario
- Total de notificaciones
- Lista de todas tus notificaciones

### B. Crear una Notificación de Prueba

Visita en tu navegador:
```
http://tu-dominio/notifications/test
```

Esto creará una notificación de prueba y mostrará el resultado.

### C. Verificar Notificaciones No Leídas

Visita en tu navegador:
```
http://tu-dominio/notifications/unread
```

Deberías ver algo como:
```json
{
  "notifications": [],
  "count": 0
}
```

## Paso 4: Verificar la Base de Datos

### Opción A: Usando Artisan Tinker

```bash
php artisan tinker
```

Luego ejecuta:
```php
DB::table('notifications')->count();
DB::table('notifications')->get();
```

### Opción B: Usando SQL directo

Si usas SQLite (base de datos por defecto):
```bash
sqlite3 database/database.sqlite
```

Luego:
```sql
SELECT COUNT(*) FROM notifications;
SELECT * FROM notifications;
```

## Paso 5: Crear una Notificación Manualmente

Para probar que el sistema funciona, crea una notificación desde la consola:

```bash
php artisan tinker
```

```php
App\Models\Notification::create([
    'user_id' => 1, // Cambia por tu ID de usuario
    'type' => 'test',
    'title' => 'Prueba',
    'message' => 'Mensaje de prueba',
    'data' => ['test' => true],
    'read' => false,
]);
```

Luego recarga tu página y verifica si aparece la notificación.

## Paso 6: Verificar que el Usuario Esté Autenticado

Las notificaciones solo aparecen para usuarios autenticados. Verifica que:

1. Estés logueado en el sistema
2. Tu sesión no haya expirado

## Errores Comunes

### Error: "Table 'notifications' doesn't exist"

**Solución:** Ejecutar `php artisan migrate`

### Error: AJAX con código 500

**Solución:** Revisar el archivo `storage/logs/laravel.log` para ver el error específico

### Las notificaciones no se actualizan automáticamente

**Solución:** Espera 30 segundos (el intervalo de actualización) o recarga la página

### No aparece el icono de notificaciones

**Solución:** Verifica que el código `@auth` esté funcionando. Asegúrate de estar logueado.

## Verificar el Navbar

Si el icono de la campana no aparece, verifica que estés en la vista que usa `layouts.blade.php` y que estés autenticado.

El HTML debe mostrar:
```html
<li class="nav-item dropdown" id="notification-dropdown">
  <a class="nav-link" href="#" id="notificationDropdown">
    <i class="fas fa-bell"></i>
    <span class="badge badge-danger badge-pill" id="notification-count" style="display: none;">0</span>
  </a>
  ...
</li>
```

## Contacto de Soporte

Si después de seguir todos estos pasos el problema persiste, proporciona:

1. El mensaje de error de la consola del navegador (F12)
2. El contenido de `storage/logs/laravel.log` (últimas líneas)
3. El resultado de `/notifications/debug`
4. El resultado de `/notifications/test`
