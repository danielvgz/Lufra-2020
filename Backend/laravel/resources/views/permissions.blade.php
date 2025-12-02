<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Permisos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a href="{{ url('/home') }}" class="nav-link"><b>Nóminas</b> Dashboard</a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            @auth
                <li class="nav-item"><a href="{{ route('perfil') }}" class="nav-link">{{ auth()->user()->name }}</a></li>
                <li class="nav-item">
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger">Cerrar sesión</button>
                    </form>
                </li>
            @endauth
        </ul>
    </nav>
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ url('/home') }}" class="brand-link">
            <span class="brand-text font-weight-light">Sistema de Nóminas</span>
        </a>
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item">
                        <a href="{{ url('/home') }}" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item"><a href="{{ url('/empleados') }}" class="nav-link"><i class="nav-icon fas fa-users"></i><p>Empleados</p></a></li>
                    <li class="nav-item"><a href="{{ url('/departamentos') }}" class="nav-link"><i class="nav-icon fas fa-sitemap"></i><p>Departamentos</p></a></li>
                    <li class="nav-item"><a href="{{ url('/nominas') }}" class="nav-link"><i class="nav-icon fas fa-calendar-alt"></i><p>Periodos de nómina</p></a></li>
                    <li class="nav-item"><a href="{{ url('/contratos') }}" class="nav-link"><i class="nav-icon fas fa-file-signature"></i><p>Contratos</p></a></li>
                    <li class="nav-item"><a href="{{ url('/recibos-pagos') }}" class="nav-link"><i class="nav-icon fas fa-file-invoice-dollar"></i><p>Recibos y Pagos</p></a></li>
                    <li class="nav-item has-treeview mt-3">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-cogs"></i>
                            <p>Configuración <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ url('/usuarios-config') }}" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Usuarios (básico)</p>
                                </a>
                            </li>
                            @if(auth()->check() && auth()->user()->puede('asignar_roles'))
                            <li class="nav-item">
                                <a href="{{ url('/roles') }}" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Roles</p>
                                </a>
                            </li>
                            @endif
                            @if(auth()->check() && auth()->user()->puede('asignar_roles'))
                            <li class="nav-item">
                                <a href="{{ url('/permissions') }}" class="nav-link active">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Permisos</p>
                                </a>
                            </li>
                            @endif
                            <li class="nav-item">
                                <a href="{{ url('/empresa/perfil') }}" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Perfil de la empresa</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>
    <div class="content-wrapper">
        <section class="content pt-3">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="card-title"><i class="fas fa-key mr-1"></i> Permisos definidos</h3>
                            </div>
                            <div class="card-body">
                                <?php
                                $lista = \Illuminate\Support\Facades\DB::table('permisos')->select('id','nombre')->orderBy('nombre')->get();
                                $roles = \Illuminate\Support\Facades\DB::table('roles')->select('id','nombre')->orderBy('nombre')->get();
                                ?>

                                <form method="POST" action="{{ url('/permissions/nuevo') }}" class="form-inline mb-3">
                                    @csrf
                                    <input name="nombre" class="form-control form-control-sm mr-2" placeholder="Nuevo permiso" required />
                                    <input name="descripcion" class="form-control form-control-sm mr-2" placeholder="Descripción" />
                                    <button class="btn btn-sm btn-primary"><i class="fas fa-plus mr-1"></i> Agregar permiso</button>
                                </form>

                                @if(count($lista))
                                    <div class="table-responsive mb-3">
                                        <table class="table table-sm">
                                            <thead><tr><th>Permiso</th><th>Acciones</th></tr></thead>
                                            <tbody>
                                            @foreach($lista as $perm)
                                                <tr>
                                                    <td>{{ $perm->nombre }}</td>
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
                                                    <td colspan="2">
                                                        <form method="POST" action="{{ route('permissions.editar') }}" class="form-inline">
                                                            @csrf
                                                            <input type="hidden" name="permiso_id" value="{{ $perm->id }}">
                                                            <input type="text" name="nombre" value="{{ $perm->nombre }}" class="form-control form-control-sm mr-2" required>
                                                            <input type="text" name="descripcion" class="form-control form-control-sm mr-2" placeholder="Descripción">
                                                            <button class="btn btn-sm btn-success">Guardar</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
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
        </section>
    </div>
    <footer class="main-footer text-sm">
        <div class="float-right d-none d-sm-inline">AdminLTE</div>
        <strong>&copy; {{ date('Y') }}.</strong> Todos los derechos reservados.
    </footer>
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>