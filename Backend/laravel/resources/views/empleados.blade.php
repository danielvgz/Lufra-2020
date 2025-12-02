<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestión de empleados</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item"><a href="{{ url('/home') }}" class="nav-link"><b>Nóminas</b> Dashboard</a></li>
    </ul>
    <ul class="navbar-nav ml-auto">
      @auth
        <li class="nav-item"><a href="{{ route('perfil') }}" class="nav-link">{{ auth()->user()->name }}</a></li>
        <li class="nav-item">
          <form method="POST" action="{{ route('logout') }}" class="d-inline">@csrf
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
          <li class="nav-item"><a href="{{ url('/home') }}" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a></li>
          <li class="nav-item"><a href="{{ url('/empleados') }}" class="nav-link active"><i class="nav-icon fas fa-users"></i><p>Empleados</p></a></li>
          <li class="nav-item"><a href="{{ url('/departamentos') }}" class="nav-link"><i class="nav-icon fas fa-sitemap"></i><p>Departamentos</p></a></li>
          <li class="nav-item"><a href="{{ url('/nominas') }}" class="nav-link"><i class="nav-icon fas fa-calendar-alt"></i><p>Periodos de nómina</p></a></li>
          <li class="nav-item"><a href="{{ url('/contratos') }}" class="nav-link"><i class="nav-icon fas fa-file-signature"></i><p>Contratos</p></a></li>
          <li class="nav-item"><a href="{{ url('/recibos-pagos') }}" class="nav-link"><i class="nav-icon fas fa-file-invoice-dollar"></i><p>Recibos y Pagos</p></a></li>
          <li class="nav-item has-treeview mt-3">
            <a href="#" class="nav-link"><i class="nav-icon fas fa-cogs"></i><p>Configuración <i class="right fas fa-angle-left"></i></p></a>
            <ul class="nav nav-treeview">
              <li class="nav-item"><a href="{{ url('/usuarios-config') }}" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Usuarios (básico)</p></a></li>
              <li class="nav-item"><a href="{{ url('/roles') }}" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Roles</p></a></li>
              <li class="nav-item"><a href="{{ url('/permissions') }}" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Permisos</p></a></li>
              <li class="nav-item"><a href="{{ url('/empresa/perfil') }}" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Perfil de la empresa</p></a></li>
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
              <div class="card-header"><h3 class="card-title"><i class="fas fa-list mr-1"></i> Lista de usuarios</h3></div>
              <div class="card-body">
                <?php use Illuminate\Support\Facades\DB; $usuarios = DB::table('users as u')->join('rol_usuario as ru','ru.user_id','=','u.id')->join('roles as r','r.id','=','ru.rol_id')->where('r.nombre','empleado')->select('u.id','u.name','u.email')->orderBy('u.id','desc')->limit(100)->get(); ?>
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
