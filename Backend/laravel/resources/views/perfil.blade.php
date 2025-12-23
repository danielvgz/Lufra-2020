@extends('layouts')
@section('content')

      <div class="container-fluid">
        
        <div class="card">
          <div class="card-header"><h3 class="card-title"><i class="fas fa-user mr-1"></i> Mi perfil</h3></div>
          <div class="card-body">
            <form method="POST" action="{{ route('perfil.update') }}">
              @csrf
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label>Nombre</label>
                  <input type="text" class="form-control" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                </div>
                <div class="form-group col-md-6">
                  <label>Email</label>
                  <input type="email" class="form-control" name="email" value="{{ old('email', auth()->user()->email) }}" required>
                </div>
              </div>
              @php
                $employee = null;
                try {
                    if (auth()->check()) {
                        $employee = \App\Models\Employee::where('user_id', auth()->id())->first();
                    }
                } catch (\Throwable $e) {
                    $employee = null;
                }
              @endphp
              @if($employee || (auth()->check() && (auth()->user()->tieneRol('empleado') || auth()->user()->tieneRol('Empleado'))))
                <div class="form-row">
                  <div class="form-group col-md-4">
                    <label>Cédula de identidad</label>
                    <input type="text" class="form-control" name="cedula" value="{{ old('cedula', optional($employee)->cedula) }}">
                  </div>
                  <div class="form-group col-md-5">
                    <label>Dirección</label>
                    <input type="text" class="form-control" name="direccion" value="{{ old('direccion', optional($employee)->direccion) }}">
                  </div>
                  <div class="form-group col-md-3">
                    <label>Talla de ropa</label>
                    <select name="talla_ropa" class="form-control">
                      @php $talla = old('talla_ropa', optional($employee)->talla_ropa); @endphp
                      <option value="">--</option>
                      <option value="XS" {{ $talla == 'XS' ? 'selected' : '' }}>XS</option>
                      <option value="S" {{ $talla == 'S' ? 'selected' : '' }}>S</option>
                      <option value="M" {{ $talla == 'M' ? 'selected' : '' }}>M</option>
                      <option value="L" {{ $talla == 'L' ? 'selected' : '' }}>L</option>
                      <option value="XL" {{ $talla == 'XL' ? 'selected' : '' }}>XL</option>
                      <option value="XXL" {{ $talla == 'XXL' ? 'selected' : '' }}>XXL</option>
                    </select>
                  </div>
                </div>
              @endif
              @php
                $showNotifications = null;
                $user = auth()->user();
                $canToggle = false;
                if ($user) {
                    // allow roles 'Administrador' or 'empleado' (case-insensitive)
                    $canToggle = $user->tieneRol('Administrador') || $user->tieneRol('administrador') || $user->tieneRol('empleado') || $user->tieneRol('Empleado');
                    if ($canToggle) {
                        $prefKey = 'user_' . $user->id . '_show_notifications';
                        $showNotifications = \App\Models\Settings::where('key', $prefKey)->value('value');
                    }
                }
              @endphp
              @if($canToggle)
                <div class="form-group form-check">
                  <input type="checkbox" class="form-check-input" id="show_notifications" name="show_notifications" {{ old('show_notifications', $showNotifications) == '1' ? 'checked' : '' }}>
                  <label class="form-check-label" for="show_notifications">Mostrar notificaciones en mi panel</label>
                </div>
              @endif
              <hr>
              <h5>Cambiar contraseña</h5>
              <div class="form-row">
                <div class="form-group col-md-4">
                  <label>Contraseña actual</label>
                  <input type="password" class="form-control @error('current_password') is-invalid @enderror" name="current_password" autocomplete="current-password">
                  @error('current_password')
                    <small class="text-danger">{{ $message }}</small>
                  @enderror
                </div>
                <div class="form-group col-md-4">
                  <label>Nueva contraseña</label>
                  <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" autocomplete="new-password">
                  @error('password')
                    <small class="text-danger">{{ $message }}</small>
                  @enderror
                </div>
                <div class="form-group col-md-4">
                  <label>Confirmar nueva contraseña</label>
                  <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" name="password_confirmation" autocomplete="new-password">
                </div>
              </div>
              <button class="btn btn-primary">Guardar cambios</button>
              <a href="{{ url('/home') }}" class="btn btn-secondary ml-2">Cancelar</a>
            </form>
           
            <hr>
            <form method="POST" action="{{ route('perfil.desactivar') }}" onsubmit="return confirm('¿Desactivar tu cuenta? No podrás acceder hasta que un admin la reactive.');">
              @csrf
              <button class="btn btn-outline-danger">Desactivar cuenta</button>
            </form>
          </div>
        </div>
      </div>
@endsection