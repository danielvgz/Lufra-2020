# ✅ Instrucciones para Activar las Notificaciones

## 🚀 Paso 1: Ejecutar las Migraciones (OBLIGATORIO)

Abre una terminal en la carpeta del proyecto y ejecuta:

```bash
php artisan migrate
```

Este comando creará:
- La tabla `notifications` en la base de datos
- El permiso `gestionar_departamentos`

**⚠️ IMPORTANTE:** Sin este paso, las notificaciones NO funcionarán.

## 🧪 Paso 2: Probar que Funciona

### Método 1: Crear una Notificación de Prueba

1. Inicia sesión en la aplicación como administrador
2. Visita en tu navegador: `http://tu-sitio/notifications/test`
3. Deberías ver un mensaje JSON como:
   ```json
   {
     "success": true,
     "message": "Notificación de prueba creada",
     "total_notifications": 1,
     "user_id": 1
   }
   ```
4. Recarga la página principal
5. Deberías ver el badge rojo con "1" en el icono de la campana

### Método 2: Crear un Departamento

1. Asegúrate de tener al menos 2 usuarios con rol administrador
2. Inicia sesión con el Usuario 1
3. Ve a **Departamentos** → **Crear nuevo departamento**
4. Cierra sesión e inicia sesión con el Usuario 2
5. El Usuario 2 debería ver una notificación del departamento creado

## 🔍 Paso 3: Verificar que Todo Está Correcto

### A. Verificar la Consola del Navegador

1. Abre la aplicación en tu navegador
2. Presiona **F12** (Chrome/Edge) o **Ctrl+Shift+I** (Firefox)
3. Ve a la pestaña **Console**
4. Recarga la página (F5)
5. Deberías ver: `Notificaciones recibidas: {count: X, notifications: Array(X)}`

### B. Si ves errores:

#### Error 404 en /notifications/unread
- **Causa:** Las rutas no están cargadas
- **Solución:** Ejecuta `php artisan route:clear` y `php artisan route:cache`

#### Error 500
- **Causa:** Error en el servidor (probablemente tabla no existe)
- **Solución:** Ejecuta `php artisan migrate`

#### Error CORS o CSRF
- **Causa:** Token CSRF no válido
- **Solución:** Recarga la página completamente (Ctrl+F5)

## 📋 Paso 4: Verificar Visualmente

El navbar debería mostrar:

```
[Logo] Gestión Nóminas                    [Tu Nombre] [🔔¹] [Cerrar sesión]
                                                        └─ Badge rojo con número de notificaciones
```

**Nota:** El icono de la campana 🔔 está justo al lado del nombre del usuario.

Al hacer clic en la campana 🔔, debería abrirse un menú desplegable con:
- Encabezado "Notificaciones"
- Lista vertical de notificaciones con:
  - Ícono grande de color según tipo
  - Título y mensaje
  - Tiempo transcurrido
  - Botón de eliminar (X) al pasar el mouse
- Botón "Marcar todas como leídas" al final

Las notificaciones no leídas tienen fondo gris claro y línea azul a la izquierda.

## 🎯 Tipos de Notificaciones Disponibles

### Para Empleados:
- 💰 **Recibo Creado** - Se genera cuando se asigna un recibo de pago

### Para Administradores:
- ✅ **Recibo Aceptado** - Un empleado aceptó su recibo
- ❌ **Recibo Rechazado** - Un empleado rechazó su recibo
- 🏢 **Departamento Creado** - Otro admin creó un departamento
- ✏️ **Departamento Editado** - Otro admin editó un departamento
- 🗑️ **Departamento Eliminado** - Otro admin eliminó un departamento

## ⚙️ Configuración Adicional (Opcional)

### Cambiar el Intervalo de Actualización

Por defecto, las notificaciones se actualizan cada 30 segundos. Para cambiarlo:

1. Abre `resources/views/layouts.blade.php`
2. Busca la línea: `setInterval(loadNotifications, 30000);`
3. Cambia `30000` por el valor deseado en milisegundos
   - 15 segundos = 15000
   - 1 minuto = 60000
   - 5 minutos = 300000

### Deshabilitar la Actualización Automática

Comenta o elimina la línea:
```javascript
setInterval(loadNotifications, 30000);
```

## 🐛 Solución de Problemas

### No aparece el icono de la campana

1. Verifica que estés logueado
2. Verifica que la vista esté usando `layouts.blade.php`
3. Limpia la caché: `php artisan view:clear`

### Las notificaciones no se cargan

1. Abre F12 → Console y busca errores
2. Verifica que la ruta `/notifications/unread` responda correctamente
3. Visita manualmente: `http://tu-sitio/notifications/debug`

### Notificación creada pero no aparece

1. Verifica que el `user_id` de la notificación coincida con tu usuario
2. Ejecuta en Tinker:
   ```php
   php artisan tinker
   auth()->id()  // Tu ID de usuario
   DB::table('notifications')->where('user_id', 1)->get()  // Cambia 1 por tu ID
   ```

## 📞 Necesitas Ayuda?

Si después de seguir todos estos pasos aún tienes problemas:

1. Revisa el archivo de log: `storage/logs/laravel.log`
2. Ejecuta los comandos de diagnóstico:
   - `/notifications/test` - Crear notificación de prueba
   - `/notifications/debug` - Ver todas tus notificaciones
3. Verifica la consola del navegador (F12)
4. Asegúrate de que la migración se ejecutó correctamente: `php artisan migrate:status`

---

**✨ Una vez completado el Paso 1 (migración), el sistema debería funcionar automáticamente.**
