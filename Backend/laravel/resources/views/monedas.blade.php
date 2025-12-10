@extends('layouts')

@section('content')
  <div class="row">
    <div class="col-md-4">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0"><i class="fas fa-plus mr-1"></i> Nueva Moneda</h5>
        </div>
        <div class="card-body">
          <form method="POST" action="{{ route('monedas.crear') }}">
            @csrf
            <div class="form-group">
              <label>Nombre</label>
              <input type="text" name="nombre" class="form-control" placeholder="Ej: Peso Mexicano" required>
            </div>
            <div class="form-group">
              <label>Código (ISO 4217)</label>
              <input type="text" name="codigo" class="form-control" placeholder="Ej: MXN" maxlength="3" style="text-transform: uppercase;" required>
              <small class="form-text text-muted">3 letras (estándar internacional)</small>
            </div>
            <div class="form-group">
              <label>Símbolo</label>
              <input type="text" name="simbolo" class="form-control" placeholder="Ej: $" maxlength="10">
            </div>
            <button class="btn btn-primary btn-block"><i class="fas fa-save mr-1"></i> Agregar</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-md-8">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0"><i class="fas fa-coins mr-1"></i> Monedas Disponibles</h5>
          <a href="{{ route('recibos_pagos') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i>Volver
          </a>
        </div>
        <div class="card-body">

          @if(count($items))
            <div class="table-responsive">
              <table class="table table-hover">
                <thead class="thead-light">
                  <tr>
                    <th>Nombre</th>
                    <th>Código</th>
                    <th>Símbolo</th>
                    <th width="200">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($items as $it)
                    <tr id="row-{{ $it->id }}">
                      <td id="nombre-{{ $it->id }}">{{ $it->nombre }}</td>
                      <td id="codigo-{{ $it->id }}"><span class="badge badge-info">{{ $it->codigo }}</span></td>
                      <td id="simbolo-{{ $it->id }}"><strong>{{ $it->simbolo }}</strong></td>
                      <td>
                        <button class="btn btn-xs btn-warning" onclick="editarMoneda({{ $it->id }}, '{{ $it->nombre }}', '{{ $it->codigo }}', '{{ $it->simbolo }}')">
                          <i class="fas fa-edit"></i> Editar
                        </button>
                        <form method="POST" action="{{ route('monedas.eliminar') }}" class="d-inline" onsubmit="return confirm('¿Eliminar esta moneda?')">
                          @csrf
                          <input type="hidden" name="id" value="{{ $it->id }}">
                          <button class="btn btn-xs btn-danger">
                            <i class="fas fa-trash"></i> Eliminar
                          </button>
                        </form>
                      </td>
                    </tr>
                    <tr id="edit-{{ $it->id }}" style="display: none;">
                      <td colspan="4">
                        <form method="POST" action="{{ route('monedas.editar') }}" class="form-inline">
                          @csrf
                          <input type="hidden" name="id" value="{{ $it->id }}">
                          <input type="text" name="nombre" value="{{ $it->nombre }}" class="form-control form-control-sm mr-2" placeholder="Nombre" required>
                          <input type="text" name="codigo" value="{{ $it->codigo }}" class="form-control form-control-sm mr-2" placeholder="Código" maxlength="3" style="text-transform: uppercase;" required>
                          <input type="text" name="simbolo" value="{{ $it->simbolo }}" class="form-control form-control-sm mr-2" placeholder="Símbolo" maxlength="10">
                          <button class="btn btn-sm btn-success mr-2">
                            <i class="fas fa-check"></i> Guardar
                          </button>
                          <button type="button" class="btn btn-sm btn-secondary" onclick="cancelarEdicion({{ $it->id }})">
                            <i class="fas fa-times"></i> Cancelar
                          </button>
                        </form>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            <div class="mt-3">
              {{ $items->links() }}
            </div>
          @else
            <div class="alert alert-info">
              <i class="fas fa-info-circle mr-2"></i>No hay monedas registradas. Las monedas por defecto (Bolívar y USD) se agregarán automáticamente.
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  <script>
    function editarMoneda(id, nombre, codigo, simbolo) {
      document.getElementById('row-' + id).style.display = 'none';
      document.getElementById('edit-' + id).style.display = 'table-row';
    }

    function cancelarEdicion(id) {
      document.getElementById('row-' + id).style.display = 'table-row';
      document.getElementById('edit-' + id).style.display = 'none';
    }
  </script>
@endsection
