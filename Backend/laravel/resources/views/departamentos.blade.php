<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestión de Departamentos</title>
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
          <li class="nav-item"><a href="{{ url('/empleados') }}" class="nav-link"><i class="nav-icon fas fa-users"></i><p>Empleados</p></a></li>
          <li class="nav-item"><a href="{{ url('/departamentos') }}" class="nav-link active"><i class="nav-icon fas fa-sitemap"></i><p>Departamentos</p></a></li>
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
              <div class="card-header"><h3 class="card-title"><i class="fas fa-plus mr-1"></i> Nuevo departamento</h3></div>
              <div class="card-body">
                <form method="POST" action="{{ route('departamentos.nuevo') }}">
                  @csrf
                  <div class="form-group"><label>Nombre</label><input name="name" class="form-control" required></div>
                  <div class="form-group"><label>Código</label><input name="code" class="form-control" required></div>
                  <div class="form-group"><label>Descripción</label><input name="description" class="form-control"></div>


                  <button class="btn btn-primary"><i class="fas fa-save mr-1"></i> Guardar</button>
                </form>
              </div>
            </div>
          </div>
          <div class="col-md-7">
            <div class="card">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title"><i class="fas fa-sitemap mr-1"></i> Departamentos</h3>
                <form method="GET" action="{{ url('/departamentos') }}" class="form-inline">
                  <input name="q" value="{{ request('q') }}" class="form-control form-control-sm mr-2" placeholder="Buscar nombre o código">
                  <button class="btn btn-sm btn-outline-secondary">Buscar</button>
                </form>
              </div>
              <div class="card-body">
                <?php
                  $q = request('q');
                  $query = \Illuminate\Support\Facades\DB::table('departamentos as d')->select('d.id','d.nombre','d.codigo')->orderBy('d.nombre');
                  if ($q) { $query->where(function($x) use($q){ $x->where('d.nombre','like',"%$q%")->orWhere('d.codigo','like',"%$q%"); }); }
                  $deps = $query->limit(200)->get();
                ?>
                @if(count($deps))
                  <div class="table-responsive">
                    <table class="table table-sm">
                      <thead><tr><th>Nombre</th><th>Código</th><th>Acciones</th></tr></thead>
                      <tbody>
                        @foreach($deps as $d)
                          <tr>
                            <td>{{ $d->nombre }}</td>
                            <td>{{ $d->codigo }}</td>
                            <td>
                              <button class="btn btn-xs btn-secondary" onclick="document.getElementById('edit-{{ $d->id }}').classList.toggle('d-none')">Editar</button>
                              <form method="POST" action="{{ route('departamentos.eliminar') }}" class="d-inline" onsubmit="return confirm('¿Eliminar departamento?')">
                                @csrf
                                <input type="hidden" name="id" value="{{ $d->id }}">
                                <button class="btn btn-xs btn-danger">Eliminar</button>
                              </form>
                            </td>
                          </tr>
                          <tr id="edit-{{ $d->id }}" class="d-none"><td colspan="5">
                            <form method="POST" action="{{ route('departamentos.editar') }}">
                              @csrf
                              <input type="hidden" name="id" value="{{ $d->id }}">
                              <div class="form-row">
                                <div class="form-group col-md-4"><label>Nombre</label><input name="name" class="form-control form-control-sm" value="{{ $d->nombre }}" required></div>
                                <div class="form-group col-md-3"><label>Código</label><input name="code" class="form-control form-control-sm" value="{{ $d->codigo }}" required></div>


                              </div>
                              <div class="form-group"><label>Descripción</label><input name="description" class="form-control form-control-sm" value="{{ \Illuminate\Support\Facades\DB::table('departamentos')->where('id',$d->id)->value('descripcion') }}"></div>
                              <button class="btn btn-sm btn-success">Guardar</button>
                            </form>
                          </td></tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                @else
                  <p>No hay departamentos.</p>
                @endif
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
