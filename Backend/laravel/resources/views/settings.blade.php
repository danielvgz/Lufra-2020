@extends('layouts')
@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-cog mr-1"></i> Configuración de la Aplicación</h3>
            </div>
            <div class="card-body">
           
                <form action="{{ route('settings.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="details-tab" data-toggle="tab" href="#details" role="tab">Detalles</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="address-tab" data-toggle="tab" href="#address" role="tab">Dirección</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="theme-tab" data-toggle="tab" href="#theme" role="tab">Tema</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="options-tab" data-toggle="tab" href="#options" role="tab">Opciones</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="website-tab" data-toggle="tab" href="#website" role="tab">Sitio web</a>
                        </li>
                    </ul>

                    <div class="tab-content mt-3" id="settingsTabContent">
                        <!-- Tab Detalles -->
                        <div class="tab-pane fade show active" id="details" role="tabpanel">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="app_name">Nombre de la aplicación</label>
                                    <input type="text" name="app_name" class="form-control @error('app_name') is-invalid @enderror" 
                                        id="app_name" placeholder="Nombre de la aplicación"
                                        value="{{ old('app_name', config('settings.app_name')) }}">
                                    @error('app_name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="register_number">Número de registro</label>
                                    <input type="text" name="register_number" class="form-control @error('register_number') is-invalid @enderror"
                                        id="register_number" placeholder="Número de registro"
                                        value="{{ old('register_number', config('settings.register_number')) }}">
                                    @error('register_number')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="app_email">Correo electrónico</label>
                                    <input type="email" name="app_email" class="form-control @error('app_email') is-invalid @enderror" 
                                        id="app_email" placeholder="correo@ejemplo.com"
                                        value="{{ old('app_email', config('settings.app_email')) }}">
                                    @error('app_email')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="phone_number">Teléfono</label>
                                    <input type="text" name="phone_number" class="form-control @error('phone_number') is-invalid @enderror"
                                        id="phone_number" placeholder="+1 234 567 8900"
                                        value="{{ old('phone_number', config('settings.phone_number')) }}">
                                    @error('phone_number')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="image">Logo de la aplicación</label>
                                <input type="file" class="form-control-file" name="image" id="image" accept="image/*">
                                @error('image')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">PNG/JPG, máx 2MB.</small>
                                @if(config('settings.image'))
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/settings/') }}/{{ config('settings.image') }}" 
                                            alt="Logo actual" style="max-height: 100px;">
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Tab Dirección -->
                        <div class="tab-pane fade" id="address" role="tabpanel">
                            <div class="form-group">
                                <label for="app_address1">Dirección</label>
                                <input type="text" name="app_address1" class="form-control @error('app_address1') is-invalid @enderror"
                                    id="app_address1" placeholder="Calle, número, etc."
                                    value="{{ old('app_address1', config('settings.app_address1')) }}">
                                @error('app_address1')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="city">Ciudad</label>
                                    <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" 
                                        id="city" placeholder="Ciudad" 
                                        value="{{ old('city', config('settings.city')) }}">
                                    @error('city')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="state">Región/Provincia</label>
                                    <input type="text" name="state" class="form-control @error('state') is-invalid @enderror" 
                                        id="state" placeholder="Estado o provincia"
                                        value="{{ old('state', config('settings.state')) }}">
                                    @error('state')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="country">País</label>
                                <input type="text" name="country" class="form-control @error('country') is-invalid @enderror" 
                                    id="country" placeholder="País" 
                                    value="{{ old('country', config('settings.country')) }}">
                                @error('country')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Tab Tema -->
                        <div class="tab-pane fade" id="theme" role="tabpanel">
                            <div class="form-group">
                                <label for="theme">Tema de la aplicación</label>
                                <select class="form-control" name="theme" id="theme">
                                    <option value="skin-blue" {{ config('settings.theme') == 'skin-blue' ? 'selected' : '' }}>Tema Azul</option>
                                    <option value="skin-blue-light" {{ config('settings.theme') == 'skin-blue-light' ? 'selected' : '' }}>Tema Azul Claro</option>
                                    <option value="skin-green" {{ config('settings.theme') == 'skin-green' ? 'selected' : '' }}>Tema Verde</option>
                                    <option value="skin-green-light" {{ config('settings.theme') == 'skin-green-light' ? 'selected' : '' }}>Tema Verde Claro</option>
                                    <option value="skin-black" {{ config('settings.theme') == 'skin-black' ? 'selected' : '' }}>Tema Negro</option>
                                    <option value="skin-red" {{ config('settings.theme') == 'skin-red' ? 'selected' : '' }}>Tema Rojo</option>
                                    <option value="skin-red-light" {{ config('settings.theme') == 'skin-red-light' ? 'selected' : '' }}>Tema Rojo Claro</option>
                                    <option value="skin-yellow" {{ config('settings.theme') == 'skin-yellow' ? 'selected' : '' }}>Tema Amarillo</option>
                                    <option value="skin-yellow-light" {{ config('settings.theme') == 'skin-yellow-light' ? 'selected' : '' }}>Tema Amarillo Claro</option>
                                    <option value="skin-purple" {{ config('settings.theme') == 'skin-purple' ? 'selected' : '' }}>Tema Púrpura</option>
                                    <option value="skin-purple-light" {{ config('settings.theme') == 'skin-purple-light' ? 'selected' : '' }}>Tema Púrpura Claro</option>
                                </select>
                                @error('theme')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">Seleccione el tema visual de la aplicación</small>
                            </div>
                            
                            <hr>
                            
                            <h5>Vista previa de temas</h5>
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <div class="card" style="border-top: 3px solid #3c8dbc;">
                                        <div class="card-body text-center">
                                            <h6>Tema Azul</h6>
                                            <div style="height: 50px; background: #3c8dbc; border-radius: 3px;"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card" style="border-top: 3px solid #00a65a;">
                                        <div class="card-body text-center">
                                            <h6>Tema Verde</h6>
                                            <div style="height: 50px; background: #00a65a; border-radius: 3px;"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card" style="border-top: 3px solid #222d32;">
                                        <div class="card-body text-center">
                                            <h6>Tema Negro</h6>
                                            <div style="height: 50px; background: #222d32; border-radius: 3px;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Opciones -->
                        <div class="tab-pane fade" id="options" role="tabpanel">
                            <h5><i class="fa fa-eye"></i> Configuraciones de Visualización</h5>
                            <hr>
                            @php
                                $isAdmin = false;
                                try {
                                    if (auth()->check()) {
                                        $isAdmin = auth()->user()->tieneRol('Administrador') || auth()->user()->tieneRol('administrador');
                                    }
                                } catch (\Throwable $e) {
                                    $isAdmin = false;
                                }
                            @endphp

                            @if($isAdmin)
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="show_notifications" name="show_notifications" value="1" {{ (isset($currentShowNotifications) && $currentShowNotifications == '1') ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="show_notifications">Mostrar notificaciones globalmente</label>
                                    </div>
                                    <small class="form-text text-muted">Control global: si está desactivado, las notificaciones no se mostrarán por defecto. Los usuarios aún pueden activar/desactivar sus notificaciones desde su perfil.</small>
                                </div>
                            @else
                                <p class="text-muted">Las notificaciones se activan por usuario. Cada usuario (incluyendo administradores y empleados) gestiona su propia preferencia desde su perfil.</p>
                            @endif
                        </div>

                        <!-- Tab Sitio Web -->
                            <div class="tab-pane fade" id="website" role="tabpanel">
                                <h5>Plantillas del sitio</h5>
                                <p class="text-muted">Seleccione la plantilla que se usará para el frontend público.</p>
                                @if(!empty($templates))
                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="use_home_view" name="use_home_view" value="1" {{ (isset($currentUseHome) && $currentUseHome == '1') ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="use_home_view">Usar vista predeterminada <code>home</code></label>
                                        </div>
                                        <small class="form-text text-muted">Si está marcado, la ruta <code>/inicio</code> mostrará la vista <code>home</code> y no la plantilla seleccionada.</small>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-8">
                                            <table class="table table-sm">
                                                <thead><tr><th>Plantilla</th><th>Acciones</th></tr></thead>
                                                <tbody>
                                                @foreach($templates as $t)
                                                    <tr>
                                                        <td>{{ $t }}</td>
                                                        <td>
                                                            <a class="btn btn-xs btn-outline-secondary" target="_blank" href="{{ route('templates.preview', ['name'=>$t]) }}">Preview</a>
                                                            <button type="button" class="btn btn-xs btn-outline-primary ml-1 template-select-btn" onclick="selectTemplate('{{ $t }}')">Seleccionar</button>
                                                            <button type="button" class="btn btn-xs btn-outline-danger ml-1 template-delete-btn" data-name="{{ $t }}">Eliminar</button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h6>Plantilla seleccionada</h6>
                                                    <p><strong id="currentTemplate">{{ $current ?? '—' }}</strong></p>
                                                    <input type="hidden" name="web_template" id="web_template_input" value="{{ old('web_template', $current) }}">
                                                    <p class="text-muted">Después de seleccionar, guarda los cambios en la parte inferior para aplicar la plantilla.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <p>No se encontraron plantillas en <code>resources/views/templates</code>.</p>
                                @endif
                            </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">
                        <i class="fas fa-save mr-1"></i> Guardar
                    </button>
                </form>
                
                <!-- Modal de confirmación para eliminar plantilla -->
                <div class="modal fade" id="deleteTemplateModal" tabindex="-1" role="dialog" aria-labelledby="deleteTemplateModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="deleteTemplateModalLabel">Eliminar plantilla</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p id="deleteTemplateMessage">¿Deseas eliminar esta plantilla? Esta acción no se puede deshacer.</p>
                            </div>
                            <div class="modal-footer">
                                <form id="deleteTemplateForm" method="POST" action="">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-danger">Eliminar plantilla</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Preview del tema en tiempo real
    $('#theme').change(function() {
        var selectedTheme = $(this).val();
        
        // Remover todas las clases de tema
        $('body').removeClass('skin-blue skin-blue-light skin-green skin-green-light skin-black skin-red skin-red-light skin-yellow skin-yellow-light skin-purple skin-purple-light');
        
        // Agregar la clase del tema seleccionado
        $('body').addClass(selectedTheme);
        
        // Mostrar mensaje temporal
        var themeName = $(this).find('option:selected').text();
        var message = $('<div class="alert alert-info alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">' +
            '<strong>Vista previa:</strong> ' + themeName + '. Guarda los cambios para aplicar permanentemente.' +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
            '<span aria-hidden="true">&times;</span>' +
            '</button>' +
            '</div>');
        
        $('body').append(message);
        
        // Auto-cerrar después de 3 segundos
        setTimeout(function() {
            message.fadeOut(400, function() {
                $(this).remove();
            });
        }, 3000);
    });
});
</script>
<script>
$(function(){
    function updateTemplateButtons() {
        var useHome = $('#use_home_view').is(':checked');
        $('.template-select-btn').prop('disabled', useHome);
        if (useHome) {
            if ($('#website .no-templates-msg').length === 0) {
                var info = $('<div class="alert alert-info no-templates-msg">Usando vista <code>home</code>. Las plantillas están deshabilitadas hasta desactivar esta opción.</div>');
                $('#website .card-body').first().append(info);
            }
        } else {
            $('#website .no-templates-msg').remove();
        }
    }

    // Initialize state
    updateTemplateButtons();

    // Toggle when checkbox changes
    $('#use_home_view').change(function(){
        updateTemplateButtons();
    });

    window.selectTemplate = function(name) {
        $('#web_template_input').val(name);
        $('#currentTemplate').text(name);
        // Mark website tab to notify user to save
        var msg = $('<div class="alert alert-info alert-dismissible fade show mt-2" role="alert">Plantilla "' + name + '" seleccionada. Guarda los cambios para aplicar.<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
        $('#website .card-body').first().append(msg);
    }
});
</script>
<script>
// Modal delete handler
$(document).on('click', '.template-delete-btn', function(){
    var name = $(this).data('name');
    var message = "¿Eliminar la plantilla '" + name + "'? Esta acción no se puede deshacer.";
    $('#deleteTemplateMessage').text(message);
    // build action URL - route: /templates/{name}/delete
    var action = "{{ url('/templates') }}" + '/' + encodeURIComponent(name) + '/delete';
    $('#deleteTemplateForm').attr('action', action);
    $('#deleteTemplateModal').modal('show');
});
</script>
@endsection
