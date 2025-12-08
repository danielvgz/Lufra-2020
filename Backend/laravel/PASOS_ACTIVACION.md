# ⚡ Pasos para Activar las Notificaciones

## ⚠️ IMPORTANTE: Debes ejecutar estos pasos EN ORDEN

### Paso 1: Ejecutar las Migraciones

Abre una terminal en la carpeta del proyecto y ejecuta:

```bash
php artisan migrate
```

**Verifica que se ejecutó correctamente:**
Deberías ver algo como:
```
Migration table created successfully.
Migrating: 2025_12_06_130700_create_notifications_table
Migrated:  2025_12_06_130700_create_notifications_table
Migrating: 2025_12_06_131900_add_gestionar_departamentos_permission
Migrated:  2025_12_06_131900_add_gestionar_departamentos_permission
```

---

### Paso 2: Verificar la Base de Datos

Ejecuta el script de verificación:

```bash
php test_notification.php
```

**Deberías ver:**
```
✅ La tabla 'notifications' existe
✅ Rol administrador existe
👥 Administradores encontrados: X
✅ Permiso 'gestionar_departamentos' existe
```

**Si ves errores:**
- ❌ Tabla no existe → Vuelve al Paso 1
- ❌ No hay administradores → Crea un usuario administrador

---

### Paso 3: Verificar el Icono en el Navbar

1. Inicia sesión en la aplicación
2. Mira el navbar superior
3. **Deberías ver:**
   ```
   [Tu Nombre 🔔] [Cerrar sesión]
   ```
   El nombre y la campana deben estar juntos, en el mismo elemento.

**Si no ves el icono:**
- Asegúrate de estar logueado
- Limpia caché: `php artisan view:clear`
- Recarga la página con Ctrl+F5

---

### Paso 4: Crear una Notificación de Prueba

Visita en tu navegador:
```
http://tu-sitio/notifications/test
```

**Deberías ver:**
```json
{
  "success": true,
  "message": "Notificación de prueba creada",
  "total_notifications": 1,
  "user_id": 1
}
```

**Ahora:**
1. Vuelve a la página principal
2. Deberías ver un **badge rojo con el número "1"** sobre la campana
3. Haz click en el nombre/campana
4. Se abre un dropdown con tu notificación de prueba

---

### Paso 5: Probar Notificaciones de Departamentos

**Necesitas 2 usuarios administradores para esto.**

#### A. Crear otro administrador (si no tienes):

```bash
php artisan tinker
```

```php
$user = new App\Models\User();
$user->name = 'Admin 2';
$user->email = 'admin2@test.com';
$user->password = bcrypt('password');
$user->save();

$rolAdminId = DB::table('roles')->where('nombre', 'administrador')->value('id');
DB::table('rol_usuario')->insert(['user_id' => $user->id, 'rol_id' => $rolAdminId]);

echo "Usuario creado con ID: " . $user->id;
exit;
```

#### B. Probar las notificaciones:

1. **Inicia sesión como Admin 1**
2. Ve a **Departamentos**
3. **Crea un nuevo departamento**
   - Nombre: "Prueba Notificaciones"
   - Código: "TEST-NOT"
4. Guarda

5. **Cierra sesión e inicia como Admin 2**
6. **Deberías ver:**
   - Badge rojo con "1" en la campana
   - Al hacer click: Notificación "Departamento Creado"

7. **Vuelve a iniciar sesión como Admin 1**
8. **Edita el departamento** que creaste
9. Cambia el nombre a "Prueba Editada"
10. Guarda

11. **Cierra sesión e inicia como Admin 2**
12. **Deberías ver:**
    - Badge rojo con "2" en la campana
    - Dos notificaciones:
      - "Departamento Creado"
      - "Departamento Editado"

---

### Paso 6: Verificar en el Navegador (F12)

1. Abre las **Herramientas de Desarrollo** (F12)
2. Ve a la pestaña **Console**
3. Recarga la página
4. **Deberías ver:**
   ```
   Notificaciones recibidas: {count: X, notifications: Array(X)}
   ```

**Si ves errores:**
- Error 404 → Las rutas no están bien. Ejecuta `php artisan route:clear`
- Error 500 → Revisa `storage/logs/laravel.log`
- No sale nada → El JavaScript no se está ejecutando. Verifica que estés en una página que use `layouts.blade.php`

---

## 🐛 Solución de Problemas

### Problema: "No aparece el badge rojo"

**Causas posibles:**
1. No hay notificaciones no leídas
2. El JavaScript no se está ejecutando
3. Hay un error en la consola

**Solución:**
1. Abre F12 → Console
2. Busca errores en rojo
3. Si hay errores, copia el mensaje y búscalo en Google

---

### Problema: "Las notificaciones no se crean"

**Verifica:**

1. Que la tabla existe:
   ```bash
   php artisan migrate:status
   ```

2. Que tienes más de un administrador:
   ```bash
   php artisan tinker
   DB::table('users')->join('rol_usuario', 'users.id', '=', 'rol_usuario.user_id')->count();
   ```

3. Que no hay errores en los logs:
   ```bash
   tail -f storage/logs/laravel.log
   ```
   (En Windows, abre el archivo `storage/logs/laravel.log` con un editor)

4. Ejecuta manualmente desde Tinker:
   ```bash
   php artisan tinker
   ```
   ```php
   App\Http\Controllers\NotificationHelper::notifyDepartamentoCreado(1, 'Test', 1);
   DB::table('notifications')->count(); // Debería ser > 0
   ```

---

### Problema: "El icono no está al lado del nombre"

**Verifica en el HTML:**
1. Abre F12 → Elements
2. Busca `notification-dropdown`
3. Debería verse así:
   ```html
   <li class="nav-item dropdown" id="notification-dropdown">
     <a class="nav-link d-flex align-items-center" ...>
       <span class="mr-2">Tu Nombre</span>
       <i class="fas fa-bell position-relative">
         <span class="badge ...">1</span>
       </i>
     </a>
   ```

Si no se ve así, limpia caché:
```bash
php artisan view:clear
php artisan cache:clear
```

---

## ✅ Checklist Final

- [ ] Ejecuté `php artisan migrate`
- [ ] La tabla `notifications` existe
- [ ] Tengo al menos 2 usuarios administradores
- [ ] Veo el icono de campana al lado de mi nombre
- [ ] `/notifications/test` funciona y crea notificaciones
- [ ] Al hacer click en el nombre/campana se abre el dropdown
- [ ] Al crear un departamento con Admin 1, Admin 2 recibe notificación
- [ ] El badge rojo muestra el número correcto
- [ ] No hay errores en la consola del navegador (F12)

---

## 📞 ¿Aún no funciona?

Si después de seguir **TODOS** los pasos anteriores aún no funciona:

1. Ejecuta estos comandos y guarda la salida:
   ```bash
   php test_notification.php > resultado.txt
   php artisan route:list | grep notification >> resultado.txt
   ```

2. Abre el navegador, presiona F12, ve a Console, copia todos los mensajes

3. Abre `storage/logs/laravel.log` y copia las últimas 50 líneas

4. Comparte estos 3 archivos/textos para diagnóstico
