@extends('layouts')

@section('content')
  <div class="card">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-building mr-1"></i> Perfil de la empresa</h3>
          </div>
          <div class="card-body">
            @if(session('status'))
              <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            <form method="POST" action="{{ route('empresa.perfil.update') }}" enctype="multipart/form-data">
              @csrf
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label>Nombre de la empresa</label>
                  <input name="nombre" class="form-control" value="{{ $perfil['nombre'] ?? '' }}" placeholder="Ej: Mi Empresa S.A." />
                </div>
                <div class="form-group col-md-6">
                  <label>Identificador fiscal</label>
                  <input name="ruc" class="form-control" value="{{ $perfil['ruc'] ?? '' }}" placeholder="RUC/NIF/CUIT" />
                </div>
              </div>
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label>Correo</label>
                  <input type="email" name="correo" class="form-control" value="{{ $perfil['correo'] ?? '' }}" />
                </div>
                <div class="form-group col-md-6">
                  <label>Teléfono</label>
                  <input name="telefono" class="form-control" value="{{ $perfil['telefono'] ?? '' }}" />
                </div>
              </div>
              <div class="form-group">
                <label>Dirección</label>
                <input name="direccion" class="form-control" value="{{ $perfil['direccion'] ?? '' }}" />
              </div>
              <div class="form-group">
                <label>Logo de la empresa</label>
                <input type="file" name="logo" class="form-control-file" accept="image/*" />
                <small class="form-text text-muted">PNG/JPG, máx 2MB.</small>
                @if(!empty($perfil['logo_path']))
                  <p class="mt-2"><i class="fas fa-image"></i> Logo subido: {{ $perfil['logo_path'] }}</p>
                @endif
              </div>
              <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Guardar</button>
      </form>
    </div>
  </div>
@endsection
