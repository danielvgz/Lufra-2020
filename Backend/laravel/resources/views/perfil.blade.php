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