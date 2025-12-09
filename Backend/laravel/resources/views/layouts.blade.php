<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $title ?? 'Dashboard' }}</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <style>
    #notification-dropdown .nav-link {
      cursor: pointer;
    }
    #notification-dropdown .nav-link span {
      color: #fff;
      font-weight: 500;
    }
    #notification-dropdown .nav-link .fa-bell {
      font-size: 1.1rem;
    }
    .notification-item {
      position: relative;
      transition: background-color 0.2s;
      border-left: 3px solid transparent;
    }
    .notification-item.bg-light {
      background-color: #f8f9fa !important;
      border-left-color: #007bff;
    }
    .notification-item:hover {
      background-color: #e9ecef !important;
    }
    .notification-item .delete-notification {
      opacity: 0;
      transition: opacity 0.2s;
      padding: 2px 6px;
    }
    .notification-item:hover .delete-notification {
      opacity: 1;
    }
    .notification-item a {
      text-decoration: none !important;
    }
    .notification-item a:hover {
      text-decoration: none !important;
    }
    .dropdown-menu {
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      border: 1px solid rgba(0,0,0,0.1);
    }
    .navbar-nav .nav-item {
      margin-left: 0.5rem;
    }
    .navbar-nav .nav-item:first-child {
      margin-left: 0;
    }
    .navbar-nav .nav-item .nav-link {
      display: flex;
      align-items: center;
      padding: 0.5rem 0.75rem;
      color: rgba(255,255,255,0.75);
      transition: color 0.2s;
    }
    .navbar-nav .nav-item .nav-link:hover {
      color: #fff;
    }
    .navbar-nav .nav-item .nav-link .fa-bell {
      font-size: 1.25rem;
    }
    #notification-dropdown .nav-link {
      position: relative;
      padding: 0.5rem 1rem;
    }
    #notification-count {
      font-size: 0.6rem;
      font-weight: 700;
      padding: 2px 5px;
      min-width: 16px;
      height: 16px;
      line-height: 12px;
      border-radius: 10px;
    }
    .navbar-nav .nav-item .dropdown-menu {
      margin-top: 0.5rem;
    }
  </style>
</head>
<body class="d-flex flex-column min-vh-100">
  <nav class="navbar navbar-expand navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="{{ route('home') }}">Gestión Nóminas</a>
      <ul class="navbar-nav ml-auto">
        @auth
          <li class="nav-item dropdown" id="notification-dropdown">
            <a class="nav-link position-relative" href="#" id="notificationDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="fas fa-bell"></i>
              <span class="badge badge-danger badge-pill position-absolute" id="notification-count" style="display: none; top: 8px; right: 8px; font-size: 0.65rem; padding: 2px 5px; min-width: 18px;">0</span>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="notificationDropdown" style="width: 350px; max-height: 500px; overflow-y: auto;">
              <h6 class="dropdown-header d-flex justify-content-between align-items-center">
                <span>Notificaciones</span>
                <a href="{{ route('notificaciones.view') }}" class="btn btn-sm btn-link">Ver todas</a>
              </h6>
              <div class="dropdown-divider"></div>
              <div id="notification-list">
                <p class="text-center text-muted py-3">No hay notificaciones</p>
              </div>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item text-center small text-primary" href="#" id="mark-all-read">
                Marcar todas como leídas
              </a>
            </div>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="fas fa-user-circle mr-1"></i>{{ auth()->user()->name }}
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
              <a class="dropdown-item" href="{{ route('perfil') }}">
                <i class="fas fa-user mr-2"></i>Mi Perfil
              </a>
              <div class="dropdown-divider"></div>
              <form method="POST" action="{{ route('logout') }}" class="px-3 py-2">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger btn-block">
                  <i class="fas fa-sign-out-alt mr-1"></i>Cerrar sesión
                </button>
              </form>
            </div>
          </li>
        @endauth
      </ul>
    </div>
  </nav>
<div class="container-fluid">
  <div class="row">
    <aside class="col-md-3 col-lg-2 bg-light border-right pt-3">
      <div class="list-group list-group-flush">
        <a class="list-group-item list-group-item-action" href="{{ route('home') }}">Inicio</a>
        @php
          $role = auth()->user()->role ?? null;
          if (!$role && auth()->check()) {
            $role = \Illuminate\Support\Facades\DB::table('rol_usuario')
              ->join('roles','roles.id','=','rol_usuario.rol_id')
              ->where('rol_usuario.user_id', auth()->id())
              ->value('roles.nombre');
          }
        @endphp
        @if($role === 'administrador')
          <a class="list-group-item list-group-item-action" href="{{ route('notificaciones.view') }}">
            <i class="fas fa-bell mr-2"></i>Notificaciones
          </a>
          <a class="list-group-item list-group-item-action" href="{{ route('departamentos.view') }}">Departamentos</a>
          <a class="list-group-item list-group-item-action" href="{{ route('empleados.index') }}">Empleados</a>
          <a class="list-group-item list-group-item-action" href="{{ route('contratos.index') }}">Contratos</a>
          <a class="list-group-item list-group-item-action" href="{{ route('nominas.index') }}">Períodos de Nómina</a>
          <a class="list-group-item list-group-item-action" href="{{ route('recibos_pagos') }}">Recibos y Pagos</a>
          @if(auth()->check() && auth()->user()->puede('asignar_roles'))
            <a class="list-group-item list-group-item-action" href="{{ url('/roles') }}">Roles</a>
          @endif
          @if(auth()->check() && auth()->user()->puede('asignar_roles'))
            <a class="list-group-item list-group-item-action" href="{{ url('/permissions') }}">Permisos</a>
          @endif
          <a class="list-group-item list-group-item-action" href="{{ url('/configuracion') }}">Configuración</a>
        @elseif($role === 'empleado')
          <a class="list-group-item list-group-item-action" href="{{ route('notificaciones.view') }}">
            <i class="fas fa-bell mr-2"></i>Notificaciones
          </a>
          <a class="list-group-item list-group-item-action" href="{{ route('recibos_pagos') }}">Recibos y Pagos</a>
        @endif
      </div>
    </aside>
    <main class="col-md-9 col-lg-10 py-4">
      @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          {{ session('success') }}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
      @endif
      @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          {{ session('status') }}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
      @endif
      @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          {{ session('error') }}
          <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
      @endif
      @if($errors && $errors->any())
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
          <ul class="mb-0">
            @foreach($errors->all() as $e)
              <li>{{ $e }}</li>
            @endforeach
          </ul>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
      @endif
      @yield('content')
    </main>
  </div>
</div>

<footer class="bg-light border-top py-3 mt-auto">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-6 text-muted small">
        <p class="mb-0">&copy; {{ date('Y') }} {{ config('settings.app_name', config('app.name')) }}. Todos los derechos reservados.</p>
      </div>
      <div class="col-md-6 text-md-right text-muted small">
        <span>Versión 1.0.0</span>
      </div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script>
  @auth
  $(document).ready(function() {
    // Función para cargar notificaciones
    function loadNotifications() {
      $.ajax({
        url: '/notifications/unread',
        method: 'GET',
        success: function(response) {
          console.log('Notificaciones recibidas:', response);
          const count = response.count;
          const notifications = response.notifications;
          
          // Actualizar contador
          if (count > 0) {
            $('#notification-count').text(count).show();
          } else {
            $('#notification-count').hide();
          }
          
          // Actualizar lista de notificaciones
          if (notifications.length > 0) {
            let html = '';
            notifications.forEach(function(notif) {
              const icon = getNotificationIcon(notif.type);
              const time = formatTime(notif.created_at);
              const bgClass = notif.read ? '' : 'bg-light';
              html += `
                <div class="notification-item ${bgClass}" style="position: relative;">
                  <a href="#" class="text-decoration-none text-dark d-block" 
                     data-id="${notif.id}" data-url="${getNotificationUrl(notif)}"
                     onclick="markAndRedirect(event, ${notif.id}, '${getNotificationUrl(notif)}')">
                    <div class="d-flex align-items-start p-2">
                      <div class="mr-2" style="font-size: 1.5rem;">
                        <i class="${icon}"></i>
                      </div>
                      <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                          <strong class="d-block mb-1">${notif.title}</strong>
                          <small class="text-muted ml-2">${time}</small>
                        </div>
                        <p class="mb-0 small text-muted">${notif.message}</p>
                      </div>
                    </div>
                  </a>
                  <button class="btn btn-sm btn-link text-danger delete-notification position-absolute" 
                          style="top: 5px; right: 5px; z-index: 10;"
                          data-id="${notif.id}" 
                          title="Eliminar"
                          onclick="event.stopPropagation(); deleteNotification(${notif.id})">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
                <div class="dropdown-divider m-0"></div>
              `;
            });
            $('#notification-list').html(html);
          } else {
            $('#notification-list').html('<p class="text-center text-muted py-3">No hay notificaciones</p>');
          }
        },
        error: function(xhr, status, error) {
          console.error('Error al cargar notificaciones:', error, xhr.responseText);
        }
      });
    }
    
    function getNotificationIcon(type) {
      const icons = {
        'recibo_creado': 'fas fa-file-invoice-dollar text-success',
        'recibo_aceptado': 'fas fa-check-circle text-success',
        'recibo_rechazado': 'fas fa-times-circle text-danger',
        'departamento_creado': 'fas fa-building text-primary',
        'departamento_editado': 'fas fa-edit text-warning',
        'departamento_eliminado': 'fas fa-trash-alt text-danger',
        'contrato_creado': 'fas fa-file-contract text-success',
        'contrato_editado': 'fas fa-pen-square text-warning',
        'contrato_eliminado': 'fas fa-file-excel text-danger',
      };
      return icons[type] || 'fas fa-bell text-info';
    }
    
    function getNotificationUrl(notif) {
      const data = notif.data || {};
      if (data.recibo_id) {
        return '/recibos_pagos';
      }
      if (data.departamento_id || notif.type.includes('departamento')) {
        return '/departamentos';
      }
      if (data.contrato_id || notif.type.includes('contrato')) {
        return '/contratos';
      }
      return '#';
    }
    
    function formatTime(datetime) {
      const date = new Date(datetime);
      const now = new Date();
      const diff = Math.floor((now - date) / 1000); // segundos
      
      if (diff < 60) return 'Hace un momento';
      if (diff < 3600) return `Hace ${Math.floor(diff / 60)} min`;
      if (diff < 86400) return `Hace ${Math.floor(diff / 3600)} h`;
      return date.toLocaleDateString();
    }
    
    // Función global para marcar como leída y redirigir
    window.markAndRedirect = function(e, id, url) {
      e.preventDefault();
      
      $.ajax({
        url: `/notifications/${id}/read`,
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function() {
          if (url && url !== '#') {
            window.location.href = url;
          } else {
            loadNotifications();
          }
        }
      });
    };
    
    // Función global para eliminar notificación
    window.deleteNotification = function(id) {
      if (confirm('¿Eliminar esta notificación?')) {
        $.ajax({
          url: `/notifications/${id}`,
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
          success: function() {
            loadNotifications();
          },
          error: function() {
            alert('Error al eliminar la notificación');
          }
        });
      }
    };
    
    // Marcar todas como leídas
    $('#mark-all-read').click(function(e) {
      e.preventDefault();
      $.ajax({
        url: '/notifications/mark-all-read',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function() {
          loadNotifications();
        }
      });
    });
    
    // Cargar notificaciones al inicio
    loadNotifications();
    
    // Actualizar cada 30 segundos
    setInterval(loadNotifications, 30000);
  });
  @endauth
</script>

@yield('scripts')

</body>
</html>
