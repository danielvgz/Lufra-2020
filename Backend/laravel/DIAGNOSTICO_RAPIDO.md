# 🔧 Diagnóstico Rápido - Notificaciones que no cargan

## Problema: Se queda en "Cargando notificaciones..."

Esto significa que la petición AJAX a `/notifications/all` está fallando o tardando mucho.

---

## ✅ Paso 1: Verificar que las Migraciones se Ejecutaron

```bash
php artisan migrate
```

Si ya lo hiciste, verifica que se creó la tabla:

```bash
php artisan tinker
```

```php
DB::table('notifications')->count();
// Si sale error "table doesn't exist", la migración NO se ejecutó
exit;
```

---

## ✅ Paso 2: Verificar en el Navegador

1. Ve a `/notificaciones`
2. Presiona **F12** (Herramientas de Desarrollo)
3. Ve a la pestaña **Network** (Red)
4. Recarga la página (F5)
5. Busca la petición a `notifications/all`
6. Haz click en ella
7. ¿Qué código de estado tiene?

### Si es 404:
Las rutas no se cargaron. Ejecuta:
```bash
php artisan route:clear
php artisan route:cache
```

### Si es 500:
Hay un error en el servidor. Ve a la pestaña **Console** y copia el error.

O revisa:
```bash
type storage\logs\laravel.log
```

### Si no aparece ninguna petición:
El JavaScript no se está ejecutando. Verifica que la página usa `layouts.blade.php`.

---

## ✅ Paso 3: Usar Rutas de Diagnóstico

### A. Verificación Completa

Visita: `http://tu-sitio/notifications/debug`

Deberías ver:
```json
{
  "status": "OK",
  "user_id": 1,
  "total_notifications": 0,
  "administrators": [...]
}
```

**Si ves un error**, copia el mensaje completo.

### B. Crear Notificación Directa

Visita: `http://tu-sitio/notifications/create-direct`

Esto crea una notificación directamente en la BD para TU usuario.

Luego ve a `/notificaciones` y debería aparecer.

### C. Verificar la Ruta `/notifications/all`

Visita: `http://tu-sitio/notifications/all`

Deberías ver:
```json
{
  "notifications": [],
  "total": 0,
  "unread": 0
}
```

**Si ves Error 404**: Las rutas no están cargadas.
**Si ves Error 500**: Hay un error en el código.

---

## ✅ Paso 4: Crear Notificación de Departamento Real

1. **Asegúrate de tener 2 administradores**

```bash
php artisan tinker
```

```php
// Ver cuántos admins hay
DB::table('users')
  ->join('rol_usuario', 'users.id', '=', 'rol_usuario.user_id')
  ->join('roles', 'roles.id', '=', 'rol_usuario.rol_id')
  ->where('roles.nombre', 'administrador')
  ->count();
  
// Si sale 0 o 1, necesitas crear otro administrador
exit;
```

2. **Crear segundo administrador** (si es necesario):

```bash
php artisan tinker
```

```php
$user = new App\Models\User();
$user->name = 'Admin Dos';
$user->email = 'admin2@test.com';
$user->password = bcrypt('password');
$user->save();

$rolId = DB::table('roles')->where('nombre', 'administrador')->value('id');
DB::table('rol_usuario')->insert(['user_id' => $user->id, 'rol_id' => $rolId]);

echo "Creado: {$user->email} con ID {$user->id}\n";
exit;
```

3. **Probar el flujo**:

   a. Inicia sesión como Admin 1
   b. Ve a **Departamentos**
   c. Crea un nuevo departamento
   d. Cierra sesión
   e. Inicia sesión como Admin 2
   f. Ve a `/notificaciones`
   g. Deberías ver la notificación

---

## ✅ Paso 5: Revisar Logs

Si nada funciona, revisa los logs:

```bash
# Windows (PowerShell o CMD)
type storage\logs\laravel.log

# O abre el archivo con un editor de texto
notepad storage\logs\laravel.log
```

Busca líneas que digan:
- `ERROR`
- `Exception`
- `notifications`

---

## 🐛 Errores Comunes y Soluciones

### Error: "SQLSTATE[HY000]: General error: 1 no such table: notifications"

**Causa**: La tabla no existe.

**Solución**:
```bash
php artisan migrate
```

---

### Error: "Call to undefined method"

**Causa**: El modelo Notification no se encuentra.

**Solución**: Verifica que existe el archivo:
`app/Models/Notification.php`

---

### La página se queda en blanco

**Causa**: Error de JavaScript.

**Solución**:
1. Presiona F12
2. Ve a Console
3. Copia el error que aparece en rojo

---

### Las notificaciones se crean pero no aparecen

**Verifica el user_id**:

```bash
php artisan tinker
```

```php
// Tu ID
auth()->id();

// Notificaciones en la BD
DB::table('notifications')->select('id', 'user_id', 'title', 'created_at')->get();

// Si el user_id no coincide con tu ID, ahí está el problema
exit;
```

---

## 📋 Checklist de Diagnóstico

Marca lo que ya verificaste:

- [ ] Ejecuté `php artisan migrate`
- [ ] La tabla `notifications` existe (verificado con tinker)
- [ ] Puedo acceder a `/notifications/debug` sin error
- [ ] Puedo acceder a `/notifications/all` sin error
- [ ] `/notifications/create-direct` crea notificaciones
- [ ] Tengo al menos 2 usuarios administradores
- [ ] La consola del navegador (F12) no muestra errores
- [ ] El archivo `storage/logs/laravel.log` no tiene errores recientes

---

## 🆘 Reporte de Error

Si después de todo esto sigue sin funcionar, recopila esta información:

1. **Resultado de `/notifications/debug`**: (Copia el JSON completo)

2. **Consola del navegador** (F12 → Console): (Copia los errores en rojo)

3. **Últimas líneas del log**:
```bash
type storage\logs\laravel.log
# Copia las últimas 20 líneas
```

4. **Estado de migraciones**:
```bash
php artisan migrate:status
# Copia la salida
```

Con esta información se puede identificar el problema exacto.
