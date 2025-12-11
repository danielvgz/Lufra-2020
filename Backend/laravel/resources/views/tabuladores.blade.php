@extends('layouts')
@section('content')

<div class="container-fluid">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h3 class="card-title mb-0"><i class="fas fa-list-alt mr-1"></i> Tabuladores Salariales</h3>
      <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#crearModal">
        <i class="fas fa-plus"></i> Nuevo Tabulador
      </button>
    </div>
    <div class="card-body">
      <form method="GET" action="{{ route('tabuladores.view') }}" class="mb-3">
        <div class="row">
          <div class="col-md-8">
            <input type="text" name="search" class="form-control" 
                   placeholder="Buscar por nombre o cargo..." 
                   value="{{ request('search') }}">
          </div>
          <div class="col-md-4">
            <div class="input-group">
              <select name="frecuencia" class="form-control">
                <option value="">Todas las frecuencias</option>
                <option value="semanal" {{ request('frecuencia') == 'semanal' ? 'selected' : '' }}>Semanal</option>
                <option value="quincenal" {{ request('frecuencia') == 'quincenal' ? 'selected' : '' }}>Quincenal</option>
                <option value="mensual" {{ request('frecuencia') == 'mensual' ? 'selected' : '' }}>Mensual</option>
              </select>
              <div class="input-group-append">
                <button class="btn btn-primary" type="submit">
                  <i class="fas fa-search"></i>
                </button>
                @if(request('search') || request('frecuencia'))
                  <a href="{{ route('tabuladores.view') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                  </a>
                @endif
              </div>
            </div>
          </div>
        </div>
      </form>

      @if($tabuladores->count())
        <div class="table-responsive">
          <table class="table table-sm table-hover">
            <thead>
              <tr>
                <th>Nombre</th>
                <th>Cargo</th>
                <th>Frecuencia</th>
                <th>Sueldo Base</th>
                <th>Moneda</th>
                <th>Descripción</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              @foreach($tabuladores as $t)
                <tr>
                  <td>{{ $t->nombre }}</td>
                  <td>{{ $t->cargo ?? '-' }}</td>
                  <td>
                    <span class="badge badge-{{ $t->frecuencia == 'semanal' ? 'primary' : ($t->frecuencia == 'quincenal' ? 'info' : 'success') }}">
                      {{ ucfirst($t->frecuencia) }}
                    </span>
                  </td>
                  <td>{{ number_format($t->sueldo_base, 2) }}</td>
                  <td><code>{{ $t->moneda }}</code></td>
                  <td>{{ $t->descripcion ?? '-' }}</td>
                  <td>
                    @if($t->activo)
                      <span class="badge badge-success">Activo</span>
                    @else
                      <span class="badge badge-danger">Inactivo</span>
                    @endif
                  </td>
                  <td>
                    <button class="btn btn-xs btn-info" onclick="editarTabulador({{ $t->id }}, '{{ $t->nombre }}', '{{ $t->cargo }}', '{{ $t->frecuencia }}', {{ $t->sueldo_base }}, '{{ $t->moneda }}', '{{ $t->descripcion }}')">
                      <i class="fas fa-edit"></i>
                    </button>
                    <form method="POST" action="{{ route('tabuladores.toggle', $t->id) }}" class="d-inline">
                      @csrf
                      <button class="btn btn-xs btn-{{ $t->activo ? 'warning' : 'success' }}" 
                              onclick="return confirm('¿Cambiar estado?')">
                        <i class="fas fa-{{ $t->activo ? 'eye-slash' : 'eye' }}"></i>
                      </button>
                    </form>
                    <form method="POST" action="{{ route('tabuladores.destroy', $t->id) }}" class="d-inline">
                      @csrf
                      @method('DELETE')
                      <button class="btn btn-xs btn-danger" onclick="return confirm('¿Eliminar este tabulador?')">
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
          {{ $tabuladores->appends(['search' => request('search'), 'frecuencia' => request('frecuencia')])->links('pagination::bootstrap-4') }}
        </div>
      @else
        @if(request('search') || request('frecuencia'))
          <div class="alert alert-info">
            No se encontraron tabuladores con los filtros aplicados.
            <a href="{{ route('tabuladores.view') }}" class="alert-link">Ver todos</a>
          </div>
        @else
          <p>No hay tabuladores registrados.</p>
        @endif
      @endif
    </div>
  </div>
</div>

<!-- Modal Crear -->
<div class="modal fade" id="crearModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="{{ route('tabuladores.store') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Nuevo Tabulador</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Nombre <span class="text-danger">*</span></label>
            <input type="text" name="nombre" class="form-control" placeholder="ej: Tabulador Gerencial" required>
          </div>
          <div class="form-group">
            <label>Cargo</label>
            <input type="text" name="cargo" class="form-control" placeholder="ej: Gerente, Asistente, Operador">
          </div>
          <div class="form-group">
            <label>Frecuencia <span class="text-danger">*</span></label>
            <select name="frecuencia" class="form-control" required>
              <option value="">-- Seleccionar --</option>
              <option value="semanal">Semanal</option>
              <option value="quincenal">Quincenal</option>
              <option value="mensual">Mensual</option>
            </select>
          </div>
          <div class="form-group">
            <label>Sueldo Base <span class="text-danger">*</span></label>
            <input type="number" step="0.01" min="0" name="sueldo_base" class="form-control" placeholder="0.00" required>
          </div>
          <div class="form-group">
            <label>Moneda <span class="text-danger">*</span></label>
            <select name="moneda" class="form-control" required>
              <?php 
                $monedas = DB::table('monedas')->orderBy('nombre')->get(); 
                if ($monedas->isEmpty()) {
                  $monedas = collect([
                    (object)['codigo' => 'VES', 'nombre' => 'Bolívar'],
                    (object)['codigo' => 'USD', 'nombre' => 'Dólar']
                  ]);
                }
              ?>
              @foreach($monedas as $m)
                <option value="{{ $m->codigo }}">{{ $m->codigo }} - {{ $m->nombre }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label>Descripción</label>
            <textarea name="descripcion" class="form-control" rows="2"></textarea>
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
          <h5 class="modal-title">Editar Tabulador</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Nombre <span class="text-danger">*</span></label>
            <input type="text" name="nombre" id="edit_nombre" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Cargo</label>
            <input type="text" name="cargo" id="edit_cargo" class="form-control">
          </div>
          <div class="form-group">
            <label>Frecuencia <span class="text-danger">*</span></label>
            <select name="frecuencia" id="edit_frecuencia" class="form-control" required>
              <option value="semanal">Semanal</option>
              <option value="quincenal">Quincenal</option>
              <option value="mensual">Mensual</option>
            </select>
          </div>
          <div class="form-group">
            <label>Sueldo Base <span class="text-danger">*</span></label>
            <input type="number" step="0.01" min="0" name="sueldo_base" id="edit_sueldo_base" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Moneda <span class="text-danger">*</span></label>
            <select name="moneda" id="edit_moneda" class="form-control" required>
              <?php 
                $monedas = DB::table('monedas')->orderBy('nombre')->get(); 
                if ($monedas->isEmpty()) {
                  $monedas = collect([
                    (object)['codigo' => 'VES', 'nombre' => 'Bolívar'],
                    (object)['codigo' => 'USD', 'nombre' => 'Dólar']
                  ]);
                }
              ?>
              @foreach($monedas as $m)
                <option value="{{ $m->codigo }}">{{ $m->codigo }} - {{ $m->nombre }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label>Descripción</label>
            <textarea name="descripcion" id="edit_descripcion" class="form-control" rows="2"></textarea>
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
function editarTabulador(id, nombre, cargo, frecuencia, sueldoBase, moneda, descripcion) {
  document.getElementById('formEditar').action = '/tabuladores/' + id;
  document.getElementById('edit_nombre').value = nombre;
  document.getElementById('edit_cargo').value = cargo || '';
  document.getElementById('edit_frecuencia').value = frecuencia;
  document.getElementById('edit_sueldo_base').value = sueldoBase;
  document.getElementById('edit_moneda').value = moneda;
  document.getElementById('edit_descripcion').value = descripcion || '';
  $('#editarModal').modal('show');
}
</script>

@endsection
