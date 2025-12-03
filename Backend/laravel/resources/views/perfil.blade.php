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