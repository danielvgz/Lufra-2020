<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Recibos y Pagos</title>
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
    <a href="{{ url('/home') }}" class="brand-link"><span class="brand-text font-weight-light">Sistema de Nóminas</span></a>
    <div class="sidebar">
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <li class="nav-item"><a href="{{ url('/home') }}" class="nav-link"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a></li>
          <li class="nav-item"><a href="{{ url('/empleados') }}" class="nav-link"><i class="nav-icon fas fa-users"></i><p>Empleados</p></a></li>
          <li class="nav-item"><a href="{{ url('/departamentos') }}" class="nav-link"><i class="nav-icon fas fa-sitemap"></i><p>Departamentos</p></a></li>
          <li class="nav-item"><a href="{{ url('/nominas') }}" class="nav-link"><i class="nav-icon fas fa-calendar-alt"></i><p>Periodos de nómina</p></a></li>
          <li class="nav-item"><a href="{{ url('/contratos') }}" class="nav-link"><i class="nav-icon fas fa-file-signature"></i><p>Contratos</p></a></li>
          <li class="nav-item"><a href="{{ url('/recibos-pagos') }}" class="nav-link active"><i class="nav-icon fas fa-file-invoice-dollar"></i><p>Recibos y Pagos</p></a></li>
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
        <?php
          $esEmpleado = false;
          if (auth()->check()) {
            $esEmpleado = \Illuminate\Support\Facades\DB::table('rol_usuario as ru')
              ->join('roles as r','r.id','=','ru.rol_id')
              ->where('ru.user_id', auth()->id())
              ->where('r.nombre','empleado')
              ->exists();
          }
        ?>
        @if($esEmpleado)
        <div class="card">
          <div class="card-header"><h3 class="card-title"><i class="fas fa-file-invoice-dollar mr-1"></i> Mis pagos</h3></div>
          <div class="card-body">
            <?php $pagos = \Illuminate\Support\Facades\DB::table('pagos as p')->join('recibos as r','r.id','=','p.recibo_id')->join('empleados as e','e.id','=','r.empleado_id')->where('e.user_id', auth()->id())->select('p.id','p.importe','p.metodo','p.estado','r.id as recibo_id')->orderByDesc('p.id')->limit(50)->get(); ?>
            @if(count($pagos))
              <div class="table-responsive">
                <table class="table table-sm">
                  <thead><tr><th>Pago</th><th>Recibo</th><th>Importe</th><th>Método</th><th>Estado</th><th>Acciones</th></tr></thead>
                  <tbody>
                  @foreach($pagos as $p)
                    <tr>
                      <td>#{{ $p->id }}</td>
                      <td>#{{ $p->recibo_id }}</td>
                      <td>{{ number_format($p->importe,2) }}</td>
                      <td>{{ $p->metodo }}</td>
                      <td><span class="badge badge-{{ $p->estado === 'aceptado' ? 'success' : ($p->estado === 'rechazado' ? 'danger' : 'warning') }}">{{ $p->estado ?? 'pendiente' }}</span></td>
                      <td>
                        @if(($p->estado ?? 'pendiente') === 'pendiente')
                          <form method="POST" action="{{ route('pagos.aceptar', ['pago'=>$p->id]) }}" class="d-inline">@csrf<button class="btn btn-xs btn-success">Aceptar</button></form>
                          <form method="POST" action="{{ route('pagos.rechazar', ['pago'=>$p->id]) }}" class="d-inline" onsubmit="return confirm('¿Rechazar este pago?')">@csrf<button class="btn btn-xs btn-danger">Rechazar</button></form>
                        @else
                          —
                        @endif
                      </td>
                    </tr>
                  @endforeach
                  </tbody>
                </table>
              </div>
            @else
              <p>No hay pagos asignados.</p>
            @endif
          </div>
        </div>
        @else
        <div class="card">
          <div class="card-header"><h3 class="card-title"><i class="fas fa-file-invoice-dollar mr-1"></i> Acciones</h3></div>
          <div class="card-body">
            <?php $periodos = \Illuminate\Support\Facades\DB::table('periodos_nomina')->orderByDesc('fecha_inicio')->limit(24)->get(); ?>
            @if(count($periodos))
              <div class="table-responsive">
                <table class="table table-sm">
                  <thead><tr><th>Código</th><th>Inicio</th><th>Fin</th><th>Estado</th><th>Acciones</th></tr></thead>
                  <tbody>
                  @foreach($periodos as $p)
                    <tr>
                      <td>{{ $p->codigo }}</td>
                      <td>{{ $p->fecha_inicio }}</td>
                      <td>{{ $p->fecha_fin }}</td>
                      <td><span class="badge badge-{{ $p->estado === 'cerrado' ? 'success' : 'secondary' }}">{{ $p->estado }}</span></td>
                      <td>
                        <a class="btn btn-xs btn-outline-primary" href="{{ route('nomina.banco', ['periodo'=>$p->id]) }}">Archivo banco</a>
                        <a class="btn btn-xs btn-outline-secondary" href="{{ route('nomina.obligaciones', ['periodo_id'=>$p->id]) }}">Obligaciones</a>
                        <a class="btn btn-xs btn-outline-info" href="{{ route('recibos_pagos.reportes', ['desde'=>null,'hasta'=>null]) }}">Reportes</a>
                      </td>
                    </tr>
                  @endforeach
                  </tbody>
                </table>
              </div>
            @else
              <p>No hay periodos de nómina.</p>
            @endif
          </div>
        </div>

        <div class="card mt-3">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title mb-0"><i class="fas fa-money-check-alt mr-1"></i> Pagos por asignar (recibos sin pago)</h3>
            <form method="GET" action="{{ route('recibos_pagos') }}" class="form-inline">
              <input type="text" name="q" value="{{ request('q','') }}" class="form-control form-control-sm mr-2" placeholder="Buscar empleado o #recibo">
              <button class="btn btn-sm btn-outline-secondary">Buscar</button>
            </form>
          </div>
          <div class="card-body">
            <?php
              $q = request('q');
              $recibosQuery = \Illuminate\Support\Facades\DB::table('recibos as r')
                ->leftJoin('pagos as p','p.recibo_id','=','r.id')
                ->join('empleados as e','e.id','=','r.empleado_id')
                ->whereNull('p.id');
              if ($q) {
                $recibosQuery->where(function($w) use ($q){
                  $w->where('e.nombre','like',"%{$q}%")
                    ->orWhere('e.apellido','like',"%{$q}%");
                  if (is_numeric($q)) { $w->orWhere('r.id','=',$q); }
                });
              }
              $recibosSinPago = $recibosQuery->select('r.id','e.nombre','e.apellido','r.neto')->orderByDesc('r.id')->limit(30)->get();
            ?>
            @if(count($recibosSinPago))
              <div class="table-responsive">
                <table class="table table-sm">
                  <thead><tr><th>Recibo</th><th>Empleado</th><th>Neto</th><th>Importe</th><th>Método</th><th>Asignar</th></tr></thead>
                  <tbody>
                    @foreach($recibosSinPago as $r)
                      <tr>
                        <td>#{{ $r->id }}</td>
                        <td>{{ $r->nombre }} {{ $r->apellido }}</td>
                        <td>{{ number_format($r->neto,2) }}</td>
                        <td>
                          <form method="POST" action="{{ route('pagos.asignar') }}" class="form-inline mb-0">
                            @csrf
                            <input type="hidden" name="recibo_id" value="{{ $r->id }}">
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm mr-2" name="importe" value="{{ $r->neto }}" required>
                        </td>
                        <td>
                            <select name="metodo" class="form-control form-control-sm mr-2" required>
                              <option value="Transferencia">Transferencia</option>
                              <option value="Efectivo">Efectivo</option>
                            </select>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary">Asignar</button>
                          </form>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @else
              <p>No hay recibos pendientes de pago.</p>
            @endif
          </div>
        </div>

        <div class="card mt-3">
          <div class="card-header"><h3 class="card-title"><i class="fas fa-hand-holding-usd mr-1"></i> Pago manual (sin recibo)</h3></div>
          <div class="card-body">
            <?php $emps = \Illuminate\Support\Facades\DB::table('empleados')->select('id','nombre','apellido')->orderBy('nombre')->limit(200)->get(); ?>
            <form method="POST" action="{{ route('pagos.manual') }}" class="form-inline">
              @csrf
              <label class="mr-2">Empleado</label>
              <select name="empleado_id" class="form-control form-control-sm mr-2" required>
                <option value="">-- Seleccionar --</option>
                @foreach($emps as $e)
                  <option value="{{ $e->id }}">{{ $e->nombre }} {{ $e->apellido }}</option>
                @endforeach
              </select>
              <label class="mr-2">Importe</label>
              <input type="number" step="0.01" min="0" name="importe" class="form-control form-control-sm mr-2" required>
              <label class="mr-2">Método</label>
              <select name="metodo" class="form-control form-control-sm mr-2" required>
                <option value="Transferencia">Transferencia</option>
                <option value="Efectivo">Efectivo</option>
              </select>
              <label class="mr-2">Concepto</label>
              <select name="descripcion" class="form-control form-control-sm mr-2">
                <option value="">-- Seleccionar --</option>
                <option value="Impuesto">Impuesto</option>
                <option value="Pago seguro social">Pago seguro social</option>
                <option value="Deducción">Deducción</option>
              </select>
              <button class="btn btn-sm btn-primary">Crear pago</button>
            </form>
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
