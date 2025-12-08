@extends('layouts')

@section('content')
  <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="card-title"><i class="fas fa-user-shield mr-1"></i> Roles de Usuarios</h3>
                                <form method="POST" action="{{ url('/roles/nuevo') }}" class="form-inline">
                                    @csrf
                                    <input name="nombre" class="form-control form-control-sm mr-2" placeholder="Nuevo rol" required />
                                    <button class="btn btn-sm btn-primary"><i class="fas fa-plus mr-1"></i> Agregar rol</button>
                                </form>
                            </div>
                            <div class="card-body">
                                <?php
                                use Illuminate\Support\Facades\DB;
                                $usuarios = DB::table('users')->select('id','name','email')->limit(50)->get();
                                $roles = DB::table('roles')->select('id','nombre','descripcion')->get();
                                ?>
                                @if(count($usuarios))
                                    <div class="mb-4">
                                        <h6 class="mb-2"><i class="fas fa-edit mr-1"></i> Editar / Eliminar Roles</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead><tr><th>Nombre</th><th>Descripción</th><th>Acciones</th></tr></thead>
                                                <tbody>
                                                @foreach($roles as $rol)
                                                    <tr>
                                                        <td>{{ $rol->nombre }}</td>
                                                        <td>{{ $rol->descripcion }}</td>
                                                        <td>
                                                            <button class="btn btn-xs btn-secondary" onclick="document.getElementById('edit-rol-{{ $rol->id }}').classList.toggle('d-none')">Editar</button>
                                                            <form method="POST" action="{{ route('roles.eliminar') }}" class="d-inline" onsubmit="return confirm('¿Eliminar rol?')">
                                                                @csrf
                                                                <input type="hidden" name="rol_id" value="{{ $rol->id }}">
                                                                <button class="btn btn-xs btn-danger">Eliminar</button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                    <tr id="edit-rol-{{ $rol->id }}" class="d-none">
                                                        <td colspan="3">
                                                            <form method="POST" action="{{ route('roles.editar') }}" class="form-inline">
                                                                @csrf
                                                                <input type="hidden" name="rol_id" value="{{ $rol->id }}">
                                                                <input type="text" name="nombre" value="{{ $rol->nombre }}" class="form-control form-control-sm mr-2" required>
                                                                <input type="text" name="descripcion" value="{{ $rol->descripcion }}" class="form-control form-control-sm mr-2" placeholder="Descripción">
                                                                <button class="btn btn-sm btn-success">Guardar</button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                            <tr>
                                                <th>Usuario</th>
                                                <th>Email</th>
                                                <th>Editar roles</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($usuarios as $u)
                                                <?php $rolIds = DB::table('rol_usuario')->where('user_id',$u->id)->pluck('rol_id')->toArray(); ?>
                                                <tr>
                                                    <td>{{ $u->name }}</td>
                                                    <td>{{ $u->email }}</td>
                                                    <td>
                                                        <form method="POST" action="{{ url('/roles/asignar') }}">
                                                            @csrf
                                                            <input type="hidden" name="user_id" value="{{ $u->id }}" />
                                                            @foreach($roles as $r)
                                                                <label class="mr-2 mb-1">
                                                                    <input type="checkbox" name="roles[]" value="{{ $r->id }}" {{ in_array($r->id,$rolIds) ? 'checked' : '' }}> {{ $r->nombre }}
                                                                </label>
                                                            @endforeach
                                                            <button class="btn btn-xs btn-secondary ml-2">Guardar</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p>No hay usuarios.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-key mr-1"></i> Permisos</h3>
                            </div>
                            <div class="card-body">
                                <?php $permisos = DB::table('permisos')->select('nombre','descripcion')->get(); ?>
                                @if(count($permisos))
                                    <ul class="list-unstyled mb-0">
                                        @foreach($permisos as $p)
                                            <li><i class="fas fa-check text-success mr-1"></i> {{ $p->nombre }} - {{ $p->descripcion }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p>No hay permisos definidos.</p>
                                @endif
                                <p class="text-muted mt-2">Nota: solo el administrador puede asignar roles a los usuarios.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
@endsection