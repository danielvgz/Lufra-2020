<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Perfil de la empresa</title>
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
              <li class="nav-item">
                <a href="{{ url('/roles') }}" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Roles y permisos</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('/empresa/perfil') }}" class="nav-link active">
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
        <div class="card">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-building mr-1"></i> Perfil de la empresa</h3>
          </div>
          <div class="card-body">
            @if(session('status'))
              <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            <form method="POST" action="{{ route('empresa.perfil.update') }}" enctype="multipart/form-data">
              @csrf
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label>Nombre de la empresa</label>
                  <input name="nombre" class="form-control" value="{{ $perfil['nombre'] ?? '' }}" placeholder="Ej: Mi Empresa S.A." />
                </div>
                <div class="form-group col-md-6">
                  <label>Identificador fiscal</label>
                  <input name="ruc" class="form-control" value="{{ $perfil['ruc'] ?? '' }}" placeholder="RUC/NIF/CUIT" />
                </div>
              </div>
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label>Correo</label>
                  <input type="email" name="correo" class="form-control" value="{{ $perfil['correo'] ?? '' }}" />
                </div>
                <div class="form-group col-md-6">
                  <label>Teléfono</label>
                  <input name="telefono" class="form-control" value="{{ $perfil['telefono'] ?? '' }}" />
                </div>
              </div>
              <div class="form-group">
                <label>Dirección</label>
                <input name="direccion" class="form-control" value="{{ $perfil['direccion'] ?? '' }}" />
              </div>
              <div class="form-group">
                <label>Logo de la empresa</label>
                <input type="file" name="logo" class="form-control-file" accept="image/*" />
                <small class="form-text text-muted">PNG/JPG, máx 2MB.</small>
                @if(!empty($perfil['logo_path']))
                  <p class="mt-2"><i class="fas fa-image"></i> Logo subido: {{ $perfil['logo_path'] }}</p>
                @endif
              </div>
              <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Guardar</button>
            </form>
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
