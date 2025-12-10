@extends('layouts')

@section('content')
  <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-bell mr-2"></i>Mis Notificaciones
                    </h3>
                    <div>
                        <button id="mark-all-read-btn" class="btn btn-sm btn-primary">
                            <i class="fas fa-check-double"></i> Marcar todas como leídas
                        </button>
                        <button id="delete-all-read-btn" class="btn btn-sm btn-danger ml-2">
                            <i class="fas fa-trash"></i> Eliminar leídas
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="notifications-container">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Cargando...</span>
                            </div>
                            <p class="mt-3 text-muted">Cargando notificaciones...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .notification-card {
        border-left: 4px solid transparent;
        transition: all 0.2s;
        cursor: pointer;
    }
    .notification-card.unread {
        background-color: #f8f9fa;
        border-left-color: #007bff;
    }
    .notification-card:hover {
        background-color: #e9ecef;
        transform: translateX(5px);
    }
    .notification-icon {
        font-size: 2rem;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background-color: #f8f9fa;
    }
    .notification-icon.departamento_creado {
        background-color: #e3f2fd;
        color: #2196F3;
    }
    .notification-icon.departamento_editado {
        background-color: #fff3e0;
        color: #ff9800;
    }
    .notification-icon.departamento_eliminado {
        background-color: #ffebee;
        color: #f44336;
    }
    .notification-icon.recibo_creado {
        background-color: #e8f5e9;
        color: #4caf50;
    }
    .notification-icon.recibo_aceptado {
        background-color: #e8f5e9;
        color: #4caf50;
    }
    .notification-icon.recibo_rechazado {
        background-color: #ffebee;
        color: #f44336;
    }
    .notification-icon.contrato_creado {
        background-color: #e8f5e9;
        color: #4caf50;
    }
    .notification-icon.contrato_editado {
        background-color: #fff3e0;
        color: #ff9800;
    }
    .notification-icon.contrato_eliminado {
        background-color: #ffebee;
        color: #f44336;
    }
    .notification-icon.periodo_cerrado_pagos_pendientes {
        background-color: #fff3e0;
        color: #ff9800;
    }
    .badge-new {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
</style>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        console.log('✓ jQuery cargado, iniciando...');
        console.log('✓ CSRF Token:', $('meta[name="csrf-token"]').attr('content'));
        
        loadAllNotifications();

        function loadAllNotifications() {
            console.log('Cargando notificaciones desde: /notifications/all');
            
            $.ajax({
                url: '/notifications/all',
                method: 'GET',
                timeout: 10000,
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                beforeSend: function() {
                    console.log('Iniciando petición AJAX...');
                },
                success: function(response) {
                    console.log('✓ Respuesta recibida:', response);
                    if (response && response.notifications) {
                        displayNotifications(response.notifications);
                    } else {
                        console.error('Respuesta sin notificaciones:', response);
                        displayNotifications([]);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('✗ Error en AJAX:', status, error);
                    console.error('Error al cargar notificaciones:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        error: error,
                        response: xhr.responseText
                    });
                    
                    let errorMsg = 'Error desconocido';
                    if (xhr.status === 404) {
                        errorMsg = 'Ruta no encontrada (404). Ejecuta: php artisan route:clear';
                    } else if (xhr.status === 500) {
                        errorMsg = 'Error del servidor (500). Revisa storage/logs/laravel.log';
                    } else if (xhr.responseText) {
                        try {
                            const json = JSON.parse(xhr.responseText);
                            errorMsg = json.error || json.message || xhr.responseText;
                        } catch(e) {
                            errorMsg = xhr.responseText.substring(0, 200);
                        }
                    }
                    
                    $('#notifications-container').html(`
                        <div class="alert alert-danger m-3">
                            <h5><i class="fas fa-exclamation-triangle"></i> Error al cargar las notificaciones</h5>
                            <p><strong>Código:</strong> ${xhr.status} - ${xhr.statusText}</p>
                            <p><strong>Detalle:</strong> ${errorMsg}</p>
                            <hr>
                            <p class="mb-0"><strong>Verifica:</strong></p>
                            <ol>
                                <li>Que ejecutaste: <code>php artisan migrate</code></li>
                                <li>Abre en otra pestaña: <a href="/notifications/debug" target="_blank">/notifications/debug</a></li>
                                <li>Revisa la consola del navegador (F12) para más detalles</li>
                            </ol>
                        </div>
                    `);
                }
            });
        }

        function displayNotifications(notifications) {
            console.log('Mostrando notificaciones:', notifications);
            
            if (!notifications || notifications.length === 0) {
                $('#notifications-container').html(`
                    <div class="text-center py-5">
                        <i class="fas fa-bell-slash fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No tienes notificaciones</h5>
                        <p class="text-muted">Cuando recibas notificaciones aparecerán aquí</p>
                    </div>
                `);
                return;
            }

            let html = '<div class="list-group list-group-flush">';
            
            notifications.forEach(function(notif) {
                console.log('Procesando notificación:', notif);
                
                const icon = getNotificationIcon(notif.type);
                const iconClass = notif.type;
                const time = formatTime(notif.created_at);
                const unreadClass = notif.read ? '' : 'unread';
                const badge = notif.read ? '' : '<span class="badge badge-primary badge-new ml-2">Nueva</span>';
                
                // Parsear data si es string
                let notifData = notif.data;
                if (typeof notifData === 'string') {
                    try {
                        notifData = JSON.parse(notifData);
                    } catch(e) {
                        notifData = {};
                    }
                }
                
                // Guardar data parseado en el objeto
                notif.parsedData = notifData;
                
                html += `
                    <div class="list-group-item notification-card ${unreadClass}" data-id="${notif.id}" data-notif='${JSON.stringify(notif).replace(/'/g, "&apos;")}'>
                        <div class="d-flex align-items-start">
                            <div class="notification-icon ${iconClass} mr-3">
                                <i class="${icon}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h6 class="mb-0">
                                        ${notif.title}
                                        ${badge}
                                    </h6>
                                    <small class="text-muted ml-3">${time}</small>
                                </div>
                                <p class="mb-0 text-muted">${notif.message}</p>
                            </div>
                            <div class="ml-3">
                                <button class="btn btn-sm btn-outline-danger delete-notification" data-id="${notif.id}" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            $('#notifications-container').html(html);
            console.log('Notificaciones mostradas correctamente');
        }

        function getNotificationIcon(type) {
            const icons = {
                'recibo_creado': 'fas fa-file-invoice-dollar',
                'recibo_aceptado': 'fas fa-check-circle',
                'recibo_rechazado': 'fas fa-times-circle',
                'departamento_creado': 'fas fa-building',
                'departamento_editado': 'fas fa-edit',
                'departamento_eliminado': 'fas fa-trash-alt',
                'contrato_creado': 'fas fa-file-contract',
                'contrato_editado': 'fas fa-pen-square',
                'contrato_eliminado': 'fas fa-file-excel',
                'periodo_cerrado_pagos_pendientes': 'fas fa-exclamation-triangle',
            };
            return icons[type] || 'fas fa-bell';
        }

        function getNotificationUrl(notif) {
            let data = notif.parsedData || notif.data || {};
            
            // Si data es string, parsearlo
            if (typeof data === 'string') {
                try {
                    data = JSON.parse(data);
                } catch(e) {
                    data = {};
                }
            }
            
            // Para notificaciones de recibos/pagos, siempre llevar a recibos-pagos
            // Los empleados verán "Mis pagos" y los admins verán todos los recibos
            if (data.recibo_id || notif.type.includes('recibo')) {
                return '/recibos-pagos';
            }
            
            // Para notificaciones de período cerrado con pagos pendientes
            if (notif.type === 'periodo_cerrado_pagos_pendientes' || data.periodo_id) {
                return '/recibos-pagos';
            }
            
            // Redirigir al detalle específico del elemento para otros tipos
            if (data.departamento_id) {
                return '/departamentos?id=' + data.departamento_id;
            }
            if (data.contrato_id) {
                return '/contratos?id=' + data.contrato_id;
            }
            
            // Fallback a las páginas generales si no hay ID específico
            if (notif.type.includes('departamento')) {
                return '/departamentos';
            }
            if (notif.type.includes('contrato')) {
                return '/contratos';
            }
            
            return '#';
        }

        function formatTime(datetime) {
            const date = new Date(datetime);
            const now = new Date();
            const diff = Math.floor((now - date) / 1000);
            
            if (diff < 60) return 'Hace un momento';
            if (diff < 3600) return `Hace ${Math.floor(diff / 60)} minutos`;
            if (diff < 86400) return `Hace ${Math.floor(diff / 3600)} horas`;
            
            // Formato: DD/MM/YYYY HH:MM
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            
            return `${day}/${month}/${year} ${hours}:${minutes}`;
        }

        // Click en notificación
        $(document).on('click', '.notification-card', function(e) {
            if ($(e.target).closest('.delete-notification').length) return;
            
            const id = $(this).data('id');
            const notifData = $(this).data('notif');
            
            console.log('Click en notificación:', id, notifData);
            
            let url = '#';
            if (notifData) {
                url = getNotificationUrl(notifData);
            }
            
            $.ajax({
                url: `/notifications/${id}/read`,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function() {
                    if (url && url !== '#') {
                        window.location.href = url;
                    } else {
                        loadAllNotifications();
                    }
                }
            });
        });

        // Eliminar notificación
        $(document).on('click', '.delete-notification', function(e) {
            e.stopPropagation();
            const id = $(this).data('id');
            
            if (confirm('¿Eliminar esta notificación?')) {
                $.ajax({
                    url: `/notifications/${id}`,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function() {
                        loadAllNotifications();
                    }
                });
            }
        });

        // Marcar todas como leídas
        $('#mark-all-read-btn').click(function() {
            $.ajax({
                url: '/notifications/mark-all-read',
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function() {
                    loadAllNotifications();
                }
            });
        });

        // Eliminar todas las leídas
        $('#delete-all-read-btn').click(function() {
            if (confirm('¿Eliminar todas las notificaciones leídas?')) {
                $.ajax({
                    url: '/notifications/delete-read',
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function() {
                        loadAllNotifications();
                    }
                });
            }
        });
    });
</script>
@endsection
