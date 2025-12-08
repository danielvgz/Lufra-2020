# 🔔 Cómo Probar las Notificaciones

## ✅ Cambios Realizados

1. **Campana ANTES del nombre**: `[🔔¹ Tu Nombre] [Cerrar sesión]`
2. **Vista completa de notificaciones**: Nueva página `/notificaciones`
3. **Nuevas rutas de prueba**: Para verificar que todo funciona

---

## 🚀 Paso 1: Ejecutar Migraciones

```bash
php artisan migrate
```

---

## 🧪 Paso 2: Probar con la Vista de Notificaciones

### A. Acceder a la vista

1. Inicia sesión en tu aplicación
2. Haz click en el menú lateral: **"Notificaciones"**
3. Deberías ver la página completa de notificaciones

### B. Crear una notificación de prueba

Visita en tu navegador:
```
http://tu-sitio/notifications/test
```

Esto creará una notificación de prueba para TU usuario.

### C. Verificar en la vista

1. Ve a `/notificaciones` (o click en el menú "Notificaciones")
2. Deberías ver:
   - Tu notificación de prueba
   - Con badge "Nueva" si no está leída
   - Con fondo gris claro
   - Con icono de campana

---

## 🏢 Paso 3: Probar Notificaciones de Departamentos

### Opción A: Crear Manualmente (Recomendado)

**Necesitas 2 usuarios administradores.**

1. **Crear segundo administrador** (si no tienes):

```bash
php artisan tinker
```

```php
$user = new App\Models\User();
$user->name = 'Admin Segundo';
$user->email = 'admin2@ejemplo.com';
$user->password = bcrypt('password123');
$user->save();

$rolId = DB::table('roles')->where('nombre', 'administrador')->value('id');
DB::table('rol_usuario')->insert(['user_id' => $user->id, 'rol_id' => $rolId]);

echo "Usuario creado: ID = {$user->id}, Email = {$user->email}\n";
exit;
```

2. **Probar el flujo completo**:

   a. **Inicia sesión como Admin 1**
   b. Ve a **Departamentos**
   c. **Crea un nuevo departamento**:
      - Nombre: "Ventas Norte"
      - Código: "VEN-NORTE"
   d. Guarda

   e. **Cierra sesión**
   f. **Inicia sesión como Admin 2**
   g. Deberías ver:
      - Badge rojo con "1" en la campana (navbar)
      - En el dropdown: Notificación "Departamento Creado"
      - En `/notificaciones`: La misma notificación con más detalles

   h. **Haz click en la notificación**
   i. Te redirige a `/departamentos`
   j. La notificación se marca como leída (desaparece el badge)

3. **Probar edición**:

   a. **Vuelve a iniciar sesión como Admin 1**
   b. **Edita el departamento** "Ventas Norte"
   c. Cambia el nombre a "Ventas Región Norte"
   d. Guarda

   e. **Cierra sesión e inicia como Admin 2**
   f. Deberías ver:
      - Badge con "1" (nueva notificación)
      - Notificación "Departamento Editado"

4. **Probar eliminación**:

   a. **Inicia como Admin 1**
   b. **Elimina** el departamento
   c. **Cierra sesión e inicia como Admin 2**
   d. Deberías ver:
      - Badge con "1"
      - Notificación "Departamento Eliminado"

### Opción B: Ruta de Prueba Rápida

Visita:
```
http://tu-sitio/notifications/test-departamento
```

**Nota**: Esta ruta crea notificaciones para OTROS administradores, no para ti.

Si solo tienes 1 administrador, verás:
```json
{
  "success": true,
  "notifications_created": 0,
  "note": "Las notificaciones fueron enviadas a otros administradores"
}
```

Si tienes 2 o más administradores, verás:
```json
{
  "success": true,
  "notifications_created": 1
}
```

Luego cierra sesión e inicia con el otro administrador para ver la notificación.

---

## 🔍 Paso 4: Verificar Visualmente

### En el Navbar

Deberías ver:
```
[🔔¹ Tu Nombre] ← Campana ANTES del nombre
    └─ Badge rojo si hay notificaciones
```

### En el Dropdown (al hacer click)

```
┌─────────────────────────────┐
│ Notificaciones    [Ver todas]│
├─────────────────────────────┤
│ 🏢 Departamento Creado      │
│    Admin creó el depto...   │
│    Hace 5 min            [X]│
├─────────────────────────────┤
│ Marcar todas como leídas    │
└─────────────────────────────┘
```

### En la Vista Completa (`/notificaciones`)

Una página con:
- Título: "Mis Notificaciones"
- Botones: "Marcar todas como leídas" y "Eliminar leídas"
- Lista de notificaciones con:
  - Ícono grande de color
  - Badge "Nueva" para no leídas
  - Título y mensaje
  - Tiempo transcurrido
  - Botón de eliminar

---

## 📊 Paso 5: Ver Todas las Notificaciones en la BD

```bash
php test_notification.php
```

O directamente en la base de datos:

```bash
php artisan tinker
```

```php
// Ver todas las notificaciones
DB::table('notifications')->get();

// Ver notificaciones por usuario
DB::table('notifications')->where('user_id', 1)->get();

// Ver solo no leídas
DB::table('notifications')->where('read', false)->get();

// Contar notificaciones
DB::table('notifications')->count();
```

---

## 🐛 Problemas Comunes

### 1. "No aparece la campana en el navbar"

**Solución:**
```bash
php artisan view:clear
php artisan cache:clear
```

Luego recarga con Ctrl+F5

---

### 2. "No se crean notificaciones al crear departamentos"

**Verifica:**

1. Que la tabla existe:
```bash
php artisan migrate:status | grep notifications
```

2. Que hay más de 1 administrador:
```bash
php artisan tinker
DB::table('users')->join('rol_usuario','users.id','=','rol_usuario.user_id')->where('rol_usuario.rol_id', 1)->get();
```

3. Revisa los logs:
```bash
# Windows
type storage\logs\laravel.log

# Linux/Mac
tail -50 storage/logs/laravel.log
```

---

### 3. "Error 404 en /notificaciones"

**Solución:**
```bash
php artisan route:clear
php artisan route:cache
```

---

### 4. "Las notificaciones se crean pero no aparecen"

**Verifica en la consola del navegador (F12):**

1. Ve a Console
2. Busca errores en rojo
3. Verifica que sale: `Notificaciones recibidas: {count: X, ...}`

**Si hay error 500:**
- Abre `storage/logs/laravel.log`
- Busca el error más reciente
- El error te dirá qué está fallando

---

## ✅ Checklist de Verificación

- [ ] Ejecuté `php artisan migrate`
- [ ] La tabla `notifications` existe
- [ ] Tengo al menos 2 usuarios administradores
- [ ] La campana aparece ANTES del nombre en el navbar
- [ ] Puedo acceder a `/notificaciones`
- [ ] `/notifications/test` crea notificaciones
- [ ] Al crear un departamento, el otro admin recibe notificación
- [ ] El badge rojo muestra el número correcto
- [ ] Puedo hacer click en las notificaciones
- [ ] Puedo marcar como leídas
- [ ] Puedo eliminar notificaciones
- [ ] No hay errores en la consola del navegador (F12)
- [ ] No hay errores en `storage/logs/laravel.log`

---

## 📸 Capturas de Pantalla (Para Verificar)

### Navbar correcto:
```
[🔔¹ Juan Pérez] [Cerrar sesión]
 └── Campana ANTES del nombre
```

### Menú lateral:
```
📋 Inicio
🔔 Notificaciones  ← NUEVO
🏢 Departamentos
👥 Empleados
...
```

### Vista de notificaciones:
- Header con título e iconos
- Lista de notificaciones con diseño bonito
- Funcionalidad de eliminar y marcar como leídas

---

## 🆘 Si Nada Funciona

Ejecuta estos comandos y comparte la salida:

```bash
php artisan migrate:status > debug.txt
php test_notification.php >> debug.txt
php artisan route:list | grep notification >> debug.txt
```

También abre en el navegador:
- `/notifications/debug`
- `/notifications/test`

Y comparte el JSON que aparece.
