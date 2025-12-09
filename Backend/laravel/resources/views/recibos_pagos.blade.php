@extends('layouts')

@section('content')
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
            <?php $pagos = \Illuminate\Support\Facades\DB::table('pagos as p')->join('recibos as r','r.id','=','p.recibo_id')->join('empleados as e','e.id','=','r.empleado_id')->where('e.user_id', auth()->id())->select('p.id','p.importe','p.metodo','p.estado','p.referencia','r.id as recibo_id')->orderByDesc('p.id')->limit(50)->get(); ?>
            @if(count($pagos))
              <div class="table-responsive">
                <table class="table table-sm">
                  <thead><tr><th>Importe</th><th>Método</th><th>Descripción</th><th>Estado</th><th>Acciones</th></tr></thead>
                  <tbody>
                  @foreach($pagos as $p)
                    <tr>
                      <td>{{ number_format($p->importe,2) }}</td>
                      <td>{{ $p->metodo }}</td>
                      <td>{{ $p->referencia ?? '-' }}</td>
                      <td><span class="badge badge-{{ $p->estado === 'aceptado' ? 'success' : ($p->estado === 'rechazado' ? 'danger' : 'warning') }}">{{ $p->estado ?? 'pendiente' }}</span></td>
                      <td>
                        @if(($p->estado ?? 'pendiente') === 'pendiente')
                          <form method="POST" action="{{ route('pagos.aceptar', ['pago'=>$p->id]) }}" class="d-inline">@csrf<button class="btn btn-xs btn-success">Aceptar</button></form>
                          <form method="POST" action="{{ route('pagos.rechazar', ['pago'=>$p->id]) }}" class="d-inline" onsubmit="return confirm('¿Rechazar este pago?')">@csrf<button class="btn btn-xs btn-danger">Rechazar</button></form>
                        @else
                          <a class="btn btn-xs btn-outline-primary" target="_blank" href="{{ route('nomina.recibo.pdf', ['recibo'=>$p->recibo_id]) }}">Imprimir recibo</a>
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
            <div class="d-flex align-items-center">
              <form method="GET" action="{{ route('recibos_pagos') }}" class="form-inline mr-2">
                <input type="text" name="q" value="{{ request('q','') }}" class="form-control form-control-sm mr-2" placeholder="Buscar empleado o #recibo">
                <button class="btn btn-sm btn-outline-secondary">Buscar</button>
              </form>
              <a href="{{ route('conceptos.view') }}" class="btn btn-sm btn-outline-secondary mr-2">Conceptos</a>
              <a href="{{ route('metodos.view') }}" class="btn btn-sm btn-outline-secondary mr-2">Métodos</a>
              <a href="{{ route('monedas.view') }}" class="btn btn-sm btn-outline-secondary">Monedas</a>
            </div>
          </div>
          <div class="card-body">
            <?php
              $q = request('q');
              $recibosQuery = \Illuminate\Support\Facades\DB::table('recibos as r')
                ->leftJoin('pagos as p','p.recibo_id','=','r.id')
                ->join('empleados as e','e.id','=','r.empleado_id')
                ->join('periodos_nomina as pn','pn.id','=','r.periodo_nomina_id')
                ->leftJoin('contratos as c','c.empleado_id','=','r.empleado_id')
                ->whereNull('p.id')
                // Solo recibos de períodos cerrados y empleados con contrato activo dentro del período
                ->where('pn.estado','cerrado')
                ->where(function($w){
                  $w->whereColumn('c.fecha_inicio','<=','pn.fecha_fin')
                    ->where(function($w2){
                      $w2->whereNull('c.fecha_fin')
                         ->orWhereColumn('c.fecha_fin','>=','pn.fecha_inicio');
                    });
                });
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
                  <thead><tr><th>Recibo</th><th>Empleado</th><th>Neto</th><th>Importe</th><th>Moneda</th><th>Método</th><th>Concepto</th><th>Asignar</th></tr></thead>
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
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm mr-2" name="importe" value="{{ $r->neto }}" required style="width: 100px;">
                        </td>
                        <td>
                            <?php 
                              $monedas = \Illuminate\Support\Facades\DB::table('monedas')->orderBy('nombre')->limit(100)->get(); 
                              if ($monedas->isEmpty()) {
                                $monedas = collect([
                                  (object)['codigo' => 'VES', 'nombre' => 'Bolívar', 'simbolo' => 'Bs.'],
                                  (object)['codigo' => 'USD', 'nombre' => 'Dólar', 'simbolo' => '$']
                                ]);
                              }
                            ?>
                            <select name="moneda" class="form-control form-control-sm mr-2" required style="width: 100px;">
                              @foreach($monedas as $mon)
                                <option value="{{ $mon->codigo }}">{{ $mon->simbolo }} {{ $mon->codigo }}</option>
                              @endforeach
                            </select>
                        </td>
                        <td>
                            <?php 
                              $metodos = \Illuminate\Support\Facades\DB::table('metodos_pago')->orderBy('nombre')->limit(100)->get(); 
                              if ($metodos->isEmpty()) {
                                $metodos = collect([
                                  (object)['nombre' => 'Transferencia'],
                                  (object)['nombre' => 'Efectivo'],
                                  (object)['nombre' => 'Pago móvil']
                                ]);
                              }
                            ?>
                            <select name="metodo" class="form-control form-control-sm mr-2" required style="width: 120px;">
                              @foreach($metodos as $m)
                                <option value="{{ $m->nombre }}">{{ $m->nombre }}</option>
                              @endforeach
                            </select>
                        </td>
                        <td>
                            <?php 
                              $conceptos = \Illuminate\Support\Facades\DB::table('conceptos_pago')->orderBy('nombre')->limit(100)->get(); 
                              if ($conceptos->isEmpty()) {
                                $conceptos = collect([
                                  (object)['nombre' => 'Nómina'],
                                  (object)['nombre' => 'Bono'],
                                  (object)['nombre' => 'Anticipo'],
                                  (object)['nombre' => 'Vacaciones']
                                ]);
                              }
                            ?>
                            <select name="concepto" class="form-control form-control-sm mr-2" style="width: 120px;">
                              <option value="">-- Seleccionar --</option>
                              @foreach($conceptos as $c)
                                <option value="{{ $c->nombre }}">{{ $c->nombre }}</option>
                              @endforeach
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
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-hand-holding-usd mr-1"></i> Pago manual (sin recibo)</h3>
          </div>
          <div class="card-body">
            <?php 
              $emps = \Illuminate\Support\Facades\DB::table('empleados')->select('id','nombre','apellido')->orderBy('nombre')->limit(200)->get(); 
              $monedas = \Illuminate\Support\Facades\DB::table('monedas')->orderBy('nombre')->limit(100)->get(); 
              if ($monedas->isEmpty()) {
                $monedas = collect([
                  (object)['codigo' => 'VES', 'nombre' => 'Bolívar', 'simbolo' => 'Bs.'],
                  (object)['codigo' => 'USD', 'nombre' => 'Dólar', 'simbolo' => '$']
                ]);
              }
              $metodos = \Illuminate\Support\Facades\DB::table('metodos_pago')->orderBy('nombre')->limit(100)->get(); 
              if ($metodos->isEmpty()) {
                $metodos = collect([
                  (object)['nombre' => 'Transferencia'],
                  (object)['nombre' => 'Efectivo'],
                  (object)['nombre' => 'Pago móvil']
                ]);
              }
              $conceptos = \Illuminate\Support\Facades\DB::table('conceptos_pago')->orderBy('nombre')->limit(100)->get(); 
              if ($conceptos->isEmpty()) {
                $conceptos = collect([
                  (object)['nombre' => 'Nómina'],
                  (object)['nombre' => 'Bono'],
                  (object)['nombre' => 'Anticipo'],
                  (object)['nombre' => 'Vacaciones']
                ]);
              }
            ?>
            <form method="POST" action="{{ route('pagos.manual') }}">
              @csrf
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label class="small text-muted">Empleado</label>
                    <select name="empleado_id" class="form-control form-control-sm" required>
                      <option value="">-- Seleccionar --</option>
                      @foreach($emps as $e)
                        <option value="{{ $e->id }}">{{ $e->nombre }} {{ $e->apellido }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label class="small text-muted">Importe</label>
                    <input type="number" step="0.01" min="0" name="importe" class="form-control form-control-sm" placeholder="0.00" required>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label class="small text-muted">Moneda</label>
                    <select name="moneda" class="form-control form-control-sm" required>
                      @foreach($monedas as $mon)
                        <option value="{{ $mon->codigo }}">{{ $mon->simbolo }} {{ $mon->codigo }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label class="small text-muted">Método</label>
                    <select name="metodo" class="form-control form-control-sm" required>
                      @foreach($metodos as $m)
                        <option value="{{ $m->nombre }}">{{ $m->nombre }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="form-group">
                    <label class="small text-muted">Concepto</label>
                    <select name="descripcion" class="form-control form-control-sm">
                      <option value="">-- Opcional --</option>
                      @foreach($conceptos as $c)
                        <option value="{{ $c->nombre }}">{{ $c->nombre }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-md-1">
                  <div class="form-group">
                    <label class="small text-muted d-block">&nbsp;</label>
                    <button type="submit" class="btn btn-sm btn-primary btn-block">
                      <i class="fas fa-save"></i> Crear
                    </button>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
        @endif
      </div>

@endsection
