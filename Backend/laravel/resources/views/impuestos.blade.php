@extends('layouts')
@section('content')

<div class="container-fluid">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h3 class="card-title mb-0"><i class="fas fa-percentage mr-1"></i> Impuestos</h3>
      <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#crearModal">
        <i class="fas fa-plus"></i> Nuevo Impuesto
      </button>
    </div>
    <div class="card-body">
      <form method="GET" action="{{ route('impuestos.view') }}" class="mb-3">
        <div class="input-group">
          <input type="text" name="search" class="form-control" 
                 placeholder="Buscar por nombre o código..." 
                 value="{{ request('search') }}">
          <div class="input-group-append">
            <button class="btn btn-primary" type="submit">
              <i class="fas fa-search"></i> Buscar
            </button>
            @if(request('search'))
              <a href="{{ route('impuestos.view') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Limpiar
              </a>
            @endif
          </div>
        </div>
      </form>

      @if($impuestos->count())
        <div class="table-responsive">
          <table class="table table-sm table-hover">
            <thead>
              <tr>
                <th>Nombre</th>
                <th>Código</th>
                <th>Porcentaje</th>
                <th>Descripción</th>
                <th>Por Defecto</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              @foreach($impuestos as $i)
                <tr>
                  <td>{{ $i->nombre }}</td>
                  <td><code>{{ $i->codigo }}</code></td>
                  <td>{{ number_format($i->porcentaje, 2) }}%</td>
                  <td>{{ $i->descripcion ?? '-' }}</td>
                  <td>
                    @if($i->por_defecto)
                      <span class="badge badge-success"><i class="fas fa-check"></i> Sí</span>
                    @else
                      <span class="badge badge-secondary">No</span>
                    @endif
                  </td>
                  <td>
                    @if($i->activo)
                      <span class="badge badge-success">Activo</span>
                    @else
                      <span class="badge badge-danger">Inactivo</span>
                    @endif
                  </td>
                  <td>
                    <button class="btn btn-xs btn-info" onclick="editarImpuesto({{ $i->id }}, '{{ $i->nombre }}', '{{ $i->codigo }}', {{ $i->porcentaje }}, '{{ $i->descripcion }}', {{ $i->por_defecto ? 'true' : 'false' }})">
                      <i class="fas fa-edit"></i>
                    </button>
                    <form method="POST" action="{{ route('impuestos.toggle', $i->id) }}" class="d-inline">
                      @csrf
                      <button class="btn btn-xs btn-{{ $i->activo ? 'warning' : 'success' }}" 
                              onclick="return confirm('¿Cambiar estado?')">
                        <i class="fas fa-{{ $i->activo ? 'eye-slash' : 'eye' }}"></i>
                      </button>
                    </form>
                    <form method="POST" action="{{ route('impuestos.destroy', $i->id) }}" class="d-inline">
                      @csrf
                      @method('DELETE')
                      <button class="btn btn-xs btn-danger" onclick="return confirm('¿Eliminar este impuesto?')">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="mt-3">
          {{ $impuestos->appends(['search' => request('search')])->links('pagination::bootstrap-4') }}
        </div>
      @else
        @if(request('search'))
          <div class="alert alert-info">
            No se encontraron impuestos que coincidan con "{{ request('search') }}".
            <a href="{{ route('impuestos.view') }}" class="alert-link">Ver todos</a>
          </div>
        @else
          <p>No hay impuestos registrados.</p>
        @endif
      @endif
    </div>
  </div>
</div>

<!-- Modal Crear -->
<div class="modal fade" id="crearModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="{{ route('impuestos.store') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Nuevo Impuesto</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Nombre <span class="text-danger">*</span></label>
            <input type="text" name="nombre" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Código <span class="text-danger">*</span></label>
            <input type="text" name="codigo" class="form-control" placeholder="ej: IVA, ISLR" required>
          </div>
          <div class="form-group">
            <label>Porcentaje <span class="text-danger">*</span></label>
            <input type="number" step="0.01" min="0" max="100" name="porcentaje" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Descripción</label>
            <textarea name="descripcion" class="form-control" rows="2"></textarea>
          </div>
          <div class="form-group">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="por_defecto" name="por_defecto" value="1">
              <label class="custom-control-label" for="por_defecto">Establecer como impuesto por defecto</label>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="editarModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" id="formEditar">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title">Editar Impuesto</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Nombre <span class="text-danger">*</span></label>
            <input type="text" name="nombre" id="edit_nombre" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Código <span class="text-danger">*</span></label>
            <input type="text" name="codigo" id="edit_codigo" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Porcentaje <span class="text-danger">*</span></label>
            <input type="number" step="0.01" min="0" max="100" name="porcentaje" id="edit_porcentaje" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Descripción</label>
            <textarea name="descripcion" id="edit_descripcion" class="form-control" rows="2"></textarea>
          </div>
          <div class="form-group">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" class="custom-control-input" id="edit_por_defecto" name="por_defecto" value="1">
              <label class="custom-control-label" for="edit_por_defecto">Establecer como impuesto por defecto</label>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Actualizar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function editarImpuesto(id, nombre, codigo, porcentaje, descripcion, porDefecto) {
  document.getElementById('formEditar').action = '/impuestos/' + id;
  document.getElementById('edit_nombre').value = nombre;
  document.getElementById('edit_codigo').value = codigo;
  document.getElementById('edit_porcentaje').value = porcentaje;
  document.getElementById('edit_descripcion').value = descripcion || '';
  document.getElementById('edit_por_defecto').checked = porDefecto;
  $('#editarModal').modal('show');
}
</script>

@endsection
