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
    /* Temas de color */
    .skin-blue .navbar { background-color: #3c8dbc !important; }
    .skin-blue .btn-primary { background-color: #3c8dbc; border-color: #367fa9; }
    .skin-blue .info-box-icon { background-color: #3c8dbc; }
    
    .skin-blue-light .navbar { background-color: #3c8dbc !important; }
    .skin-blue-light .btn-primary { background-color: #3c8dbc; border-color: #367fa9; }
    
    .skin-green .navbar { background-color: #00a65a !important; }
    .skin-green .btn-primary { background-color: #00a65a; border-color: #008d4c; }
    .skin-green .info-box-icon { background-color: #00a65a; }
    
    .skin-green-light .navbar { background-color: #00a65a !important; }
    .skin-green-light .btn-primary { background-color: #00a65a; border-color: #008d4c; }
    
    .skin-black .navbar { background-color: #222d32 !important; }
    .skin-black .btn-primary { background-color: #222d32; border-color: #1a2226; }
    .skin-black .info-box-icon { background-color: #222d32; }
    
    .skin-red .navbar { background-color: #dd4b39 !important; }
    .skin-red .btn-primary { background-color: #dd4b39; border-color: #d73925; }
    .skin-red .info-box-icon { background-color: #dd4b39; }
    
    .skin-red-light .navbar { background-color: #dd4b39 !important; }
    .skin-red-light .btn-primary { background-color: #dd4b39; border-color: #d73925; }
    
    .skin-yellow .navbar { background-color: #f39c12 !important; }
    .skin-yellow .btn-primary { background-color: #f39c12; border-color: #e08e0b; }
    .skin-yellow .info-box-icon { background-color: #f39c12; }
    
    .skin-yellow-light .navbar { background-color: #f39c12 !important; }
    .skin-yellow-light .btn-primary { background-color: #f39c12; border-color: #e08e0b; }
    
    .skin-purple .navbar { background-color: #605ca8 !important; }
    .skin-purple .btn-primary { background-color: #605ca8; border-color: #555299; }
    .skin-purple .info-box-icon { background-color: #605ca8; }
    
    .skin-purple-light .navbar { background-color: #605ca8 !important; }
    .skin-purple-light .btn-primary { background-color: #605ca8; border-color: #555299; }

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
    
    /* Estilos para paginación */
    .pagination {
      margin-bottom: 0;
    }
    .pagination .page-link {
      color: #3c8dbc;
      border-color: #dee2e6;
    }
    .pagination .page-item.active .page-link {
      background-color: #3c8dbc;
      border-color: #3c8dbc;
      color: #fff;
    }
    .pagination .page-link:hover {
      color: #2c6e9c;
      background-color: #e9ecef;
      border-color: #dee2e6;
    }
    .pagination .page-item.disabled .page-link {
      color: #6c757d;
      background-color: #fff;
      border-color: #dee2e6;
    }
    
    /* Adaptar color de paginación según tema */
    .skin-green .pagination .page-link {
      color: #00a65a;
    }
    .skin-green .pagination .page-item.active .page-link {
      background-color: #00a65a;
      border-color: #00a65a;
    }
    .skin-red .pagination .page-link {
      color: #dd4b39;
    }
    .skin-red .pagination .page-item.active .page-link {
      background-color: #dd4b39;
      border-color: #dd4b39;
    }
    .skin-yellow .pagination .page-link {
      color: #f39c12;
    }
    .skin-yellow .pagination .page-item.active .page-link {
      background-color: #f39c12;
      border-color: #f39c12;
    }
    .skin-purple .pagination .page-link {
      color: #605ca8;
    }
    .skin-purple .pagination .page-item.active .page-link {
      background-color: #605ca8;
      border-color: #605ca8;
    }
  </style>
</head>
<body class="d-flex flex-column min-vh-100 {{ config('settings.theme', 'skin-blue') }}">
  <nav class="navbar navbar-expand navbar-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="{{ route('home') }}">Gestión Nóminas</a>
      <ul class="navbar-nav ml-auto">
        @auth
            @php
            $showNotificationsForUser = true;
            try {
              if (auth()->check()) {
                $global = \App\Models\Settings::where('key', 'show_notifications')->value('value');
                $pref = \App\Models\Settings::where('key', 'user_'.auth()->id().'_show_notifications')->value('value');
                if (!is_null($pref)) {
                  $showNotificationsForUser = ((string)$pref === '1');
                } else {
                  $showNotificationsForUser = !(!is_null($global) && (string)$global === '0');
                }
              }
            } catch (\Throwable $e) {
              // if settings table not available, default to true
              $showNotificationsForUser = true;
            }
            @endphp
            @if($showNotificationsForUser)
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
          @endif
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
        <a class="list-group-item list-group-item-action" href="{{ route('home') }}">
          <i class="fas fa-home mr-2"></i>Inicio
        </a>
        @if($role === 'administrador')
          <a class="list-group-item list-group-item-action" href="{{ route('notificaciones.view') }}">
            <i class="fas fa-bell mr-2"></i>Notificaciones
          </a>
          <a class="list-group-item list-group-item-action" href="{{ route('departamentos.view') }}">
            <i class="fas fa-sitemap mr-2"></i>Departamentos
          </a>
          <a class="list-group-item list-group-item-action" href="{{ route('empleados.index') }}">
            <i class="fas fa-users mr-2"></i>Empleados
          </a>
          <a class="list-group-item list-group-item-action" href="{{ route('contratos.index') }}">
            <i class="fas fa-file-contract mr-2"></i>Contratos
          </a>
          <a class="list-group-item list-group-item-action" href="{{ route('nominas.index') }}">
            <i class="fas fa-calendar-alt mr-2"></i>Períodos de Nómina
          </a>
          <a class="list-group-item list-group-item-action" href="{{ route('recibos_pagos') }}">
            <i class="fas fa-money-bill-wave mr-2"></i>Recibos y Pagos
          </a>
          <a class="list-group-item list-group-item-action" href="{{ route('impuestos.view') }}">
            <i class="fas fa-percentage mr-2"></i>Impuestos
          </a>
          <a class="list-group-item list-group-item-action" href="{{ route('tabuladores.view') }}">
            <i class="fas fa-list-alt mr-2"></i>Tabuladores Salariales
          </a>
          @if(auth()->check() && auth()->user()->puede('asignar_roles'))
            <a class="list-group-item list-group-item-action" href="{{ url('/roles') }}">
              <i class="fas fa-user-shield mr-2"></i>Roles
            </a>
          @endif
          @if(auth()->check() && auth()->user()->puede('asignar_roles'))
            <a class="list-group-item list-group-item-action" href="{{ url('/permissions') }}">
              <i class="fas fa-key mr-2"></i>Permisos
            </a>
          @endif
          <a class="list-group-item list-group-item-action" href="{{ url('/configuracion') }}">
            <i class="fas fa-cog mr-2"></i>Configuración
          </a>
        @elseif($role === 'empleado')
          <a class="list-group-item list-group-item-action" href="{{ route('notificaciones.view') }}">
            <i class="fas fa-bell mr-2"></i>Notificaciones
          </a>
          <a class="list-group-item list-group-item-action" href="{{ route('recibos_pagos') }}">
            <i class="fas fa-money-bill-wave mr-2"></i>Recibos y Pagos
          </a>
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
  @if(isset($showNotificationsForUser) ? $showNotificationsForUser : true)
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
      
      // Notificaciones de recibos y pagos
      if (data.recibo_id || data.pago_id || notif.type.includes('recibo')) {
        return '/recibos-pagos';
      }
      
      // Notificaciones de departamentos
      if (data.departamento_id || notif.type.includes('departamento')) {
        return '/departamentos';
      }
      
      // Notificaciones de contratos
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
      
      // Formato: DD/MM/YYYY HH:MM
      const day = String(date.getDate()).padStart(2, '0');
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const year = date.getFullYear();
      const hours = String(date.getHours()).padStart(2, '0');
      const minutes = String(date.getMinutes()).padStart(2, '0');
      
      return `${day}/${month}/${year} ${hours}:${minutes}`;
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
  @endif
  @endauth
</script>

@yield('scripts')

</body>
</html>
