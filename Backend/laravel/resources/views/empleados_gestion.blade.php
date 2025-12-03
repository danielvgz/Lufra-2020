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
          <li class="nav-item has-treeview mt-3">
            <a href="#" class="nav-link"><i class="nav-icon fas fa-cogs"></i><p>Configuración <i class="right fas fa-angle-left"></i></p></a>
            <ul class="nav nav-treeview">

              @if(auth()->check() && auth()->user()->puede('asignar_roles'))
              <li class="nav-item"><a href="{{ url('/roles') }}" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Roles</p></a></li>
              <li class="nav-item"><a href="{{ url('/permissions') }}" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Permisos</p></a></li>
              @endif
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
              <div class="card-header"><h3 class="card-title"><i class="fas fa-user-plus mr-1"></i> Agregar empleado</h3></div>
              <div class="card-body">
                <form method="POST" action="{{ route('empleados.store') }}">
                  @csrf
                  <div class="form-row">
                    <div class="form-group col-md-4"><label>Número empleado</label><input name="numero_empleado" class="form-control" required></div>
                    <div class="form-group col-md-4"><label>Nombre</label><input name="nombre" class="form-control" required></div>
                    <div class="form-group col-md-4"><label>Apellido</label><input name="apellido" class="form-control" required></div>
                  </div>
                  <div class="form-row">
                    <div class="form-group col-md-6"><label>Correo</label><input type="email" name="correo" class="form-control" required></div>
                    <div class="form-group col-md-6"><label>Identificador fiscal</label><input name="identificador_fiscal" class="form-control"></div>
                  </div>
                  <div class="form-row">
                    <div class="form-group col-md-4"><label>Fecha nacimiento</label><input type="date" name="fecha_nacimiento" class="form-control" required></div>
                    <div class="form-group col-md-4"><label>Fecha ingreso</label><input type="date" name="fecha_ingreso" class="form-control" required></div>
                    <div class="form-group col-md-4"><label>Fecha baja</label><input type="date" name="fecha_baja" class="form-control"></div>
                  </div>
                  <div class="form-row">
                    <div class="form-group col-md-4"><label>Estado</label><input name="estado" class="form-control" value="activo" required></div>
                    <div class="form-group col-md-4"><label>Teléfono</label><input name="telefono" class="form-control"></div>
                    <div class="form-group col-md-4"><label>Dirección</label><input name="direccion" class="form-control"></div>
                  </div>
                  <div class="form-row">
                    <div class="form-group col-md-4"><label>Banco</label><input name="banco" class="form-control"></div>
                    <div class="form-group col-md-4"><label>Cuenta bancaria</label><input name="cuenta_bancaria" class="form-control"></div>
                    <div class="form-group col-md-4"><label>Notas</label><input name="notas" class="form-control"></div>
                  </div>
                  <button class="btn btn-primary"><i class="fas fa-save mr-1"></i> Guardar</button>
                </form>
              </div>
            </div>
          </div>
          <div class="col-md-7">
            <div class="card">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title"><i class="fas fa-list mr-1"></i> Lista de empleados</h3>
                <form method="GET" action="{{ url('/empleados') }}" class="form-inline">
                  <input name="q" value="{{ request('q') }}" class="form-control form-control-sm mr-2" placeholder="Buscar nombre, cédula, NSS o puesto">
                  <button class="btn btn-sm btn-outline-secondary">Buscar</button>
                </form>
              </div>
              <div class="card-body">
                <?php
                  use Illuminate\Support\Facades\DB;
                  $q = request('q');
                  $query = DB::table('empleados')->select('id','numero_empleado','nombre','apellido','correo','identificador_fiscal','fecha_nacimiento','fecha_ingreso','fecha_baja','estado','telefono','direccion','banco','cuenta_bancaria','notas')->orderBy('id','desc');
                  if ($q) {
                    $query->where('nombre','like','%'.$q.'%')
                          ->orWhere('apellido','like','%'.$q.'%')
                          ->orWhere('numero_empleado','like','%'.$q.'%')
                          ->orWhere('correo','like','%'.$q.'%')
                          ->orWhere('identificador_fiscal','like','%'.$q.'%')
                          ->orWhere('estado','like','%'.$q.'%');
                  }
                  $empleados = $query->limit(200)->get();
                ?>
                <div class="table-responsive">
                  <table class="table table-sm">
                    <thead><tr><th>ID</th><th>#</th><th>Nombre</th><th>Correo</th><th>Estado</th><th>Ingreso</th><th>Acciones</th></tr></thead>
                    <tbody>
                      @foreach($empleados as $e)
                        <tr>
                          <td>{{ $e->id }}</td>
                          <td>{{ $e->numero_empleado }}</td>
                          <td>{{ $e->nombre }} {{ $e->apellido }}</td>
                          <td>{{ $e->correo }}</td>
                          <td>{{ $e->estado }}</td>
                          <td>{{ $e->fecha_ingreso }}</td>
                          <td>
                            <button class="btn btn-xs btn-secondary" onclick="document.getElementById('edit-{{ $e->id }}').classList.toggle('d-none')">Editar</button>
                            <form method="POST" action="{{ route('empleados.destroy') }}" class="d-inline" onsubmit="return confirm('¿Eliminar empleado?')">
                              @csrf
                              <input type="hidden" name="id" value="{{ $e->id }}">
                              <button class="btn btn-xs btn-danger">Eliminar</button>
                            </form>
                          </td>
                        </tr>
                        <tr id="edit-{{ $e->id }}" class="d-none"><td colspan="7">
                          <form method="POST" action="{{ route('empleados.update') }}">
                            @csrf
                            <input type="hidden" name="id" value="{{ $e->id }}">
                            <div class="form-row">
                              <div class="form-group col-md-3"><label>#</label><input name="numero_empleado" class="form-control form-control-sm" value="{{ $e->numero_empleado }}" required></div>
                              <div class="form-group col-md-3"><label>Nombre</label><input name="nombre" class="form-control form-control-sm" value="{{ $e->nombre }}" required></div>
                              <div class="form-group col-md-3"><label>Apellido</label><input name="apellido" class="form-control form-control-sm" value="{{ $e->apellido }}" required></div>
                              <div class="form-group col-md-3"><label>Correo</label><input type="email" name="correo" class="form-control form-control-sm" value="{{ $e->correo }}" required></div>
                            </div>
                            <div class="form-row">
                              <div class="form-group col-md-3"><label>Identif. fiscal</label><input name="identificador_fiscal" class="form-control form-control-sm" value="{{ $e->identificador_fiscal }}"></div>
                              <div class="form-group col-md-3"><label>Nacimiento</label><input type="date" name="fecha_nacimiento" class="form-control form-control-sm" value="{{ $e->fecha_nacimiento }}" required></div>
                              <div class="form-group col-md-3"><label>Ingreso</label><input type="date" name="fecha_ingreso" class="form-control form-control-sm" value="{{ $e->fecha_ingreso }}" required></div>
                              <div class="form-group col-md-3"><label>Baja</label><input type="date" name="fecha_baja" class="form-control form-control-sm" value="{{ $e->fecha_baja }}"></div>
                            </div>
                            <div class="form-row">
                              <div class="form-group col-md-3"><label>Estado</label><input name="estado" class="form-control form-control-sm" value="{{ $e->estado }}" required></div>
                              <div class="form-group col-md-3"><label>Teléfono</label><input name="telefono" class="form-control form-control-sm" value="{{ $e->telefono }}"></div>
                              <div class="form-group col-md-3"><label>Dirección</label><input name="direccion" class="form-control form-control-sm" value="{{ $e->direccion }}"></div>
                              <div class="form-group col-md-3"><label>Banco</label><input name="banco" class="form-control form-control-sm" value="{{ $e->banco }}"></div>
                            </div>
                            <div class="form-row">
                              <div class="form-group col-md-6"><label>Cuenta bancaria</label><input name="cuenta_bancaria" class="form-control form-control-sm" value="{{ $e->cuenta_bancaria }}"></div>
                              <div class="form-group col-md-6"><label>Notas</label><input name="notas" class="form-control form-control-sm" value="{{ $e->notas }}"></div>
                            </div>
                            <button class="btn btn-sm btn-success">Guardar</button>
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
              <div class="card-header"><h3 class="card-title"><i class="fas fa-id-card mr-1"></i> Detalles del empleado</h3></div>
              <div class="card-body">
                <p><b>ID:</b> {{ $detalle->id }} | <b>#:</b> {{ $detalle->numero_empleado }} | <b>Nombre:</b> {{ $detalle->nombre }} {{ $detalle->apellido }} | <b>Correo:</b> {{ $detalle->correo }}</p>
                <p><b>IF:</b> {{ $detalle->identificador_fiscal }} | <b>Nacimiento:</b> {{ $detalle->fecha_nacimiento }} | <b>Ingreso:</b> {{ $detalle->fecha_ingreso }} | <b>Baja:</b> {{ $detalle->fecha_baja }} | <b>Estado:</b> {{ $detalle->estado }}</p>
                <p><b>Teléfono:</b> {{ $detalle->telefono }} | <b>Dirección:</b> {{ $detalle->direccion }} | <b>Banco:</b> {{ $detalle->banco }} | <b>Cuenta:</b> {{ $detalle->cuenta_bancaria }}</p>
                <p><b>Notas:</b> {{ $detalle->notas }}</p>
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