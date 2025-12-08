# Sistema de Notificaciones

## Descripción

Se ha implementado un sistema de notificaciones en tiempo real para alertar a empleados y administradores sobre eventos relacionados con recibos de pago.

## Características

### 1. Icono de Notificaciones en el Navbar
- Ubicado justo al lado (a la derecha) del nombre del usuario en la barra de navegación
- Muestra un badge rojo con el número de notificaciones no leídas
- Al hacer clic, se despliega un dropdown con la lista de notificaciones en forma de lista
- Las notificaciones no leídas tienen un fondo gris claro y una línea azul a la izquierda

### 2. Tipos de Notificaciones

#### Para Empleados:
- **Recibo Creado**: Se notifica cuando se genera un nuevo recibo de pago para el empleado

#### Para Administradores:
- **Recibo Aceptado**: Se notifica cuando un empleado acepta su recibo de pago
- **Recibo Rechazado**: Se notifica cuando un empleado rechaza su recibo de pago
- **Departamento Creado**: Se notifica cuando otro administrador crea un departamento
- **Departamento Editado**: Se notifica cuando otro administrador edita un departamento
- **Departamento Eliminado**: Se notifica cuando otro administrador elimina un departamento
- **Contrato Creado**: Se notifica cuando otro administrador crea un contrato
- **Contrato Editado**: Se notifica cuando otro administrador edita un contrato
- **Contrato Eliminado**: Se notifica cuando otro administrador elimina un contrato

### 3. Funcionalidades

- **Actualización Automática**: Las notificaciones se actualizan cada 30 segundos
- **Diseño en Lista**: Las notificaciones se muestran en formato de lista vertical con:
  - Ícono grande a la izquierda que identifica el tipo de notificación
  - Título en negrita
  - Mensaje descriptivo
  - Tiempo transcurrido desde la creación
  - Botón de eliminar (aparece al pasar el mouse)
- **Indicador Visual**: Las notificaciones no leídas tienen:
  - Fondo gris claro
  - Borde azul a la izquierda
- **Marcar como Leída**: Al hacer clic en una notificación, se marca como leída y redirige al área correspondiente
- **Marcar Todas como Leídas**: Opción para marcar todas las notificaciones como leídas de una vez
- **Eliminar Notificación**: Cada notificación tiene un botón (X) que aparece al pasar el mouse para eliminarla individualmente

## Archivos Creados

1. **Migración**: `database/migrations/2025_12_06_130700_create_notifications_table.php`
2. **Modelo**: `app/Models/Notification.php`
3. **Controlador**: `app/Http/Controllers/NotificationController.php`
4. **Helper**: `app/Http/Controllers/NotificationHelper.php`

## Archivos Modificados

1. **routes/web.php**: Agregadas rutas para las notificaciones
2. **resources/views/layouts.blade.php**: 
   - Agregado icono de notificaciones en el navbar
   - Agregado JavaScript para gestionar notificaciones
   - Agregado CSS para estilos de notificaciones
3. **app/Models/User.php**: Agregadas relaciones con notificaciones

## Uso

### Crear una Notificación Manualmente

```php
use App\Http\Controllers\NotificationHelper;

// Notificar creación de recibo
NotificationHelper::notifyReciboCreado($reciboId, $empleadoId);

// Notificar aceptación de recibo
NotificationHelper::notifyReciboAceptado($pagoId, $empleadoId);

// Notificar rechazo de recibo
NotificationHelper::notifyReciboRechazado($pagoId, $empleadoId);

// Notificar creación de departamento
NotificationHelper::notifyDepartamentoCreado($departamentoId, $nombreDepartamento, $creadorId);

// Notificar edición de departamento
NotificationHelper::notifyDepartamentoEditado($departamentoId, $nombreDepartamento, $editorId);

// Notificar eliminación de departamento
NotificationHelper::notifyDepartamentoEliminado($nombreDepartamento, $eliminadorId);

// Notificar creación de contrato
NotificationHelper::notifyContratoCreado($contratoId, $empleadoNombre, $creadorId);

// Notificar edición de contrato
NotificationHelper::notifyContratoEditado($contratoId, $empleadoNombre, $editorId);

// Notificar eliminación de contrato
NotificationHelper::notifyContratoEliminado($empleadoNombre, $eliminadorId);
```

### Rutas API

- `GET /notifications/unread` - Obtener notificaciones no leídas
- `POST /notifications/{id}/read` - Marcar notificación como leída
- `POST /notifications/mark-all-read` - Marcar todas como leídas
- `DELETE /notifications/{id}` - Eliminar una notificación

## Migración de Base de Datos

Para crear la tabla de notificaciones, ejecutar:

```bash
php artisan migrate
```

## Próximas Mejoras

- Notificaciones en tiempo real con WebSockets (Laravel Echo + Pusher)
- Notificaciones por email
- Configuración de preferencias de notificaciones por usuario
- Historial completo de notificaciones con paginación
