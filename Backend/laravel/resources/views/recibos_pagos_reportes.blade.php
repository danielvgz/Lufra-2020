<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reportes de Recibos</title>
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
  </nav>

  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ url('/home') }}" class="brand-link"><span class="brand-text font-weight-light">Sistema de Nóminas</span></a>
    <div class="sidebar">
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <li class="nav-item"><a href="{{ url('/home') }}" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a></li>
          <li class="nav-item"><a href="{{ url('/empleados') }}" class="nav-link"><i class="nav-icon fas fa-users"></i><p>Empleados</p></a></li>
          <li class="nav-item"><a href="{{ url('/departamentos') }}" class="nav-link"><i class="nav-icon fas fa-sitemap"></i><p>Departamentos</p></a></li>
          <li class="nav-item"><a href="{{ url('/nominas') }}" class="nav-link"><i class="nav-icon fas fa-calendar-alt"></i><p>Periodos de nómina</p></a></li>
          <li class="nav-item"><a href="{{ url('/recibos-pagos') }}" class="nav-link"><i class="nav-icon fas fa-file-invoice-dollar"></i><p>Recibos y Pagos</p></a></li>
          <li class="nav-item"><a href="{{ url('/recibos-pagos/reportes') }}" class="nav-link active"><i class="nav-icon fas fa-chart-bar"></i><p>Reportes</p></a></li>
        </ul>
      </nav>
    </div>
  </aside>

  <div class="content-wrapper">
    <section class="content pt-3">
      <div class="container-fluid">
        <div class="card">
          <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> Reportes por período y rango de fechas</h3></div>
          <div class="card-body">
            <form method="GET" action="{{ route('recibos_pagos.reportes') }}" class="form-inline mb-3">
              <label class="mr-2">Desde</label>
              <input type="date" name="desde" value="{{ $desde }}" class="form-control form-control-sm mr-2">
              <label class="mr-2">Hasta</label>
              <input type="date" name="hasta" value="{{ $hasta }}" class="form-control form-control-sm mr-2">
              <button class="btn btn-sm btn-primary">Filtrar</button>
            </form>
            @if(count($periodos))
              <div class="table-responsive">
                <table class="table table-sm">
                  <thead><tr><th>Período</th><th>Inicio</th><th>Fin</th><th>Recibos</th><th>Total Neto</th><th>Detalle</th></tr></thead>
                  <tbody>
                  @foreach($periodos as $p)
                    <tr>
                      <td>{{ $p->codigo }}</td>
                      <td>{{ $p->fecha_inicio }}</td>
                      <td>{{ $p->fecha_fin }}</td>
                      <td>{{ $p->recibos }}</td>
                      <td>{{ number_format($p->total_neto,2) }}</td>
                      <td><a class="btn btn-xs btn-outline-primary" href="{{ route('recibos_pagos.reportes_detalle', ['desde'=>$desde, 'hasta'=>$hasta]) }}">Ver detalle / Imprimir</a></td>
                    </tr>
                  @endforeach
                  </tbody>
                </table>
              </div>
            @else
              <p>No hay recibos en el rango seleccionado.</p>
            @endif
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
