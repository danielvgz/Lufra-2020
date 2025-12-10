@extends('layouts')

@section('content')
  <div class="row">
          <div class="col-md-5">
            <div class="card">
              <div class="card-header"><h3 class="card-title"><i class="fas fa-user-plus mr-1"></i> Agregar nuevo usuario</h3></div>
              <div class="card-body">
                <form method="POST" action="{{ route('empleados.crear') }}">
                  @csrf
                  <div class="form-group"><label>Nombre</label><input name="name" class="form-control" required></div>
                  <div class="form-group"><label>Correo</label><input type="email" name="email" class="form-control" required></div>
                  <div class="form-group"><label>Contraseña</label><input type="password" name="password" class="form-control" required></div>
                  <div class="form-group"><label>Confirmar contraseña</label><input type="password" name="password_confirmation" class="form-control" required></div>
                  <button class="btn btn-primary"><i class="fas fa-save mr-1"></i> Guardar</button>
                </form>
              </div>
            </div>
          </div>
          <div class="col-md-7">
            <div class="card">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0"><i class="fas fa-list mr-1"></i> Lista de usuarios</h3>
              </div>
              <div class="card-body">
                <!-- Caja de búsqueda -->
                <form method="GET" action="{{ route('empleados.index') }}" class="mb-3">
                  <div class="input-group">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Buscar por nombre, correo o ID..." 
                           value="{{ request('search') }}">
                    <div class="input-group-append">
                      <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i> Buscar
                      </button>
                      @if(request('search'))
                        <a href="{{ route('empleados.index') }}" class="btn btn-secondary">
                          <i class="fas fa-times"></i> Limpiar
                        </a>
                      @endif
                    </div>
                  </div>
                </form>

                @if($usuarios->count())
                <div class="table-responsive">
                  <table class="table table-sm">
                    <thead><tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Acciones</th></tr></thead>
                    <tbody>
                      @foreach($usuarios as $u)
                        <tr>
                          <td>{{ $u->id }}</td>
                          <td>{{ $u->name }}</td>
                          <td>{{ $u->email }}</td>
                          <td>
                            <a href="{{ url('/empleados/detalle/'.$u->id) }}" class="btn btn-xs btn-info">Detalle</a>
                            <button class="btn btn-xs btn-warning" onclick="document.getElementById('cp-{{ $u->id }}').classList.toggle('d-none')">Contraseña</button>
                            <button class="btn btn-xs btn-secondary" onclick="document.getElementById('edit-{{ $u->id }}').classList.toggle('d-none')">Editar</button>
                            <form method="POST" action="{{ route('empleados.eliminar') }}" class="d-inline" onsubmit="return confirm('¿Eliminar usuario?')">
                              @csrf
                              <input type="hidden" name="user_id" value="{{ $u->id }}">
                              <button class="btn btn-xs btn-danger">Eliminar</button>
                            </form>
                          </td>
                        </tr>
                        <tr id="cp-{{ $u->id }}" class="d-none"><td colspan="4">
                          <form method="POST" action="{{ route('empleados.password') }}" class="form-inline">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $u->id }}">
                            <input type="password" name="password" class="form-control form-control-sm mr-2" placeholder="Nueva contraseña" required>
                            <input type="password" name="password_confirmation" class="form-control form-control-sm mr-2" placeholder="Confirmación" required>
                            <button class="btn btn-sm btn-warning">Cambiar</button>
                          </form>
                        </td></tr>
                        <tr id="edit-{{ $u->id }}" class="d-none"><td colspan="4">
                          <form method="POST" action="{{ route('empleados.editar') }}" class="form-inline mb-2">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $u->id }}">
                            <input type="text" name="name" class="form-control form-control-sm mr-2" value="{{ $u->name }}" required>
                            <input type="email" name="email" class="form-control form-control-sm mr-2" value="{{ $u->email }}" required>
                            <button class="btn btn-sm btn-success">Actualizar</button>
                          </form>
                          <?php $deps = \Illuminate\Support\Facades\DB::table('departamentos')->select('id','nombre')->orderBy('nombre')->get(); ?>
                          <form method="POST" action="{{ route('empleados.asignar_departamento') }}" class="form-inline">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $u->id }}">
                            <label class="mr-2">Departamento</label>
                            <select name="department_id" class="form-control form-control-sm mr-2" required>
                              <option value="">-- Seleccionar --</option>
                              @foreach($deps as $d)
                                <option value="{{ $d->id }}" {{ \Illuminate\Support\Facades\DB::table('empleados')->where('user_id',$u->id)->value('department_id') == $d->id ? 'selected' : '' }}>{{ $d->nombre }}</option>
                              @endforeach
                            </select>
                            <button class="btn btn-sm btn-primary">Asignar</button>
                          </form>
                        </td></tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
                <div class="mt-3">
                  {{ $usuarios->appends(['search' => request('search')])->links('pagination::bootstrap-4') }}
                </div>
                @else
                  @if(request('search'))
                    <div class="alert alert-info">
                      No se encontraron usuarios que coincidan con "{{ request('search') }}".
                      <a href="{{ route('empleados.index') }}" class="alert-link">Ver todos</a>
                    </div>
                  @else
                    <p>No hay usuarios registrados.</p>
                  @endif
                @endif
              </div>
            </div>
          </div>
        </div>

        @if(isset($detalle))
        <div class="row mt-3">
          <div class="col-12">
            <div class="card">
              <div class="card-header"><h3 class="card-title"><i class="fas fa-id-card mr-1"></i> Detalles del usuario</h3></div>
              <div class="card-body">
                <p><b>ID:</b> {{ $detalle->id }} | <b>Nombre:</b> {{ $detalle->name }} | <b>Correo:</b> {{ $detalle->email }}</p>
              </div>
            </div>
          </div>
    </div>
    @endif
  </div>
@endsection