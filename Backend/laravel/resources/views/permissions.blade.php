@extends('layouts')

@section('content')
  <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="card-title"><i class="fas fa-key mr-1"></i> Permisos definidos</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ url('/permissions/nuevo') }}" class="form-inline mb-3">
                                    @csrf
                                    <input name="nombre" class="form-control form-control-sm mr-2" placeholder="Nuevo permiso" required />
                                    <input name="descripcion" class="form-control form-control-sm mr-2" placeholder="Descripción" />
                                    <button class="btn btn-sm btn-primary"><i class="fas fa-plus mr-1"></i> Agregar permiso</button>
                                </form>

                                @if(count($lista))
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0"><i class="fas fa-list mr-1"></i> Lista de Permisos</h6>
                                        <form method="GET" action="{{ route('permissions.index') }}" class="form-inline">
                                            <input name="search_permisos" value="{{ request('search_permisos') }}" class="form-control form-control-sm mr-2" placeholder="Buscar permiso">
                                            <button class="btn btn-outline-secondary btn-sm">Buscar</button>
                                        </form>
                                    </div>
                                    <div class="table-responsive mb-3">
                                        <table class="table table-sm">
                                            <thead><tr><th>Permiso</th><th>Descripción</th><th>Acciones</th></tr></thead>
                                            <tbody>
                                            @foreach($lista as $perm)
                                                <tr>
                                                    <td>{{ $perm->nombre }}</td>
                                                    <td><small class="text-muted">{{ $perm->descripcion }}</small></td>
                                                    <td>
                                                        <button class="btn btn-xs btn-secondary" onclick="document.getElementById('edit-perm-{{ $perm->id }}').classList.toggle('d-none')">Editar</button>
                                                        <form method="POST" action="{{ route('permissions.eliminar') }}" class="d-inline" onsubmit="return confirm('¿Eliminar permiso?')">
                                                            @csrf
                                                            <input type="hidden" name="permiso_id" value="{{ $perm->id }}">
                                                            <button class="btn btn-xs btn-danger">Eliminar</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                <tr id="edit-perm-{{ $perm->id }}" class="d-none">
                                                    <td colspan="3">
                                                        <form method="POST" action="{{ route('permissions.editar') }}" class="form-inline">
                                                            @csrf
                                                            <input type="hidden" name="permiso_id" value="{{ $perm->id }}">
                                                            <input type="text" name="nombre" value="{{ $perm->nombre }}" class="form-control form-control-sm mr-2" required>
                                                            <input type="text" name="descripcion" value="{{ $perm->descripcion }}" class="form-control form-control-sm mr-2" placeholder="Descripción">
                                                            <button class="btn btn-sm btn-success">Guardar</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mt-3">
                                        {{ $lista->appends(['search_permisos' => request('search_permisos')])->links() }}
                                    </div>
                                @else
                                    <p>No hay permisos registrados.</p>
                                @endif

                                @if(count($roles) && count($lista))
                                    <h6 class="mt-4">Asignar permisos a roles</h6>
                                    @foreach($roles as $r)
                                        <?php $permIds = \Illuminate\Support\Facades\DB::table('permiso_rol')->where('rol_id',$r->id)->pluck('permiso_id')->toArray(); ?>
                                        <form method="POST" action="{{ url('/permissions/asignar') }}" class="mb-2">
                                            @csrf
                                            <input type="hidden" name="rol_id" value="{{ $r->id }}" />
                                            <strong class="mr-2">{{ $r->nombre }}:</strong>
                                            @foreach($lista as $p)
                                                <label class="mr-2 mb-1">
                                                    <input type="checkbox" name="permisos[]" value="{{ $p->id }}" {{ in_array($p->id,$permIds) ? 'checked' : '' }}> {{ $p->nombre }}
                                                </label>
                                            @endforeach
                                            <button class="btn btn-xs btn-secondary ml-2">Guardar</button>
                                        </form>
                                    @endforeach
                                @endif

                                <p class="text-muted mt-2">Gestiona permisos: crea y asigna a roles. Solo usuarios con permiso para asignar_roles pueden ver esta sección.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
@endsection