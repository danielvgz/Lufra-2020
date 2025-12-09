@extends('layouts')
@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-cog mr-1"></i> Configuración de la Aplicación</h3>
            </div>
            <div class="card-body">
                @if(session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif
                
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
                            
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="show_notifications" value="1" checked>
                                    <i class="fa fa-bell"></i> Mostrar notificaciones
                                </label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">
                        <i class="fas fa-save mr-1"></i> Guardar
                    </button>
                </form>
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
@endsection
