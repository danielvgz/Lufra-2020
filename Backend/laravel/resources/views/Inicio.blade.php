@extends('layouts')
@section('content')
            <div class="container-fluid">
@unless($esEmpleado)
                <div class="row">
                    <div class="col-lg-2 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>{{ $empleados }}</h3>
                                <p>Empleados</p>
                            </div>
                            <div class="icon"><i class="fas fa-users"></i></div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{ $departamentos }}</h3>
                                <p>Departamentos</p>
                            </div>
                            <div class="icon"><i class="fas fa-sitemap"></i></div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner text-dark">
                                <h3>{{ $contratos }}</h3>
                                <p>Contratos</p>
                            </div>
                            <div class="icon"><i class="fas fa-file-signature"></i></div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-6">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3>{{ $periodos }}</h3>
                                <p>Periodos de nómina</p>
                            </div>
                            <div class="icon"><i class="fas fa-calendar-alt"></i></div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>{{ $recibos }}</h3>
                                <p>Recibos</p>
                            </div>
                            <div class="icon"><i class="fas fa-file-invoice-dollar"></i></div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-6">
                        <div class="small-box bg-secondary">
                            <div class="inner">
                                <h3>{{ $pagos }}</h3>
                                <p>Pagos</p>
                            </div>
                            <div class="icon"><i class="fas fa-money-check-alt"></i></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-tachometer-alt mr-1"></i> Resumen</h3>
                            </div>
                            <div class="card-body">
                                <p class="mb-2">Último periodo de nómina:</p>
                                @if($ultimoPeriodo)
                                    <p><strong>{{ $ultimoPeriodo->codigo }}</strong> ({{ $ultimoPeriodo->fecha_inicio }} a {{ $ultimoPeriodo->fecha_fin }}) - Estado: {{ $ultimoPeriodo->estado }}</p>
                                @else
                                    <p>No hay periodos registrados.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Departamentos -->
                <div class="row">
                    <div class="col-12">
                        <div class="card" id="departamentos">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-sitemap mr-1"></i> Departamentos</h3>
                            </div>
                            <div class="card-body">
                                <form method="GET" action="{{ route('home') }}" class="mb-3">
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="search_deps" class="form-control" 
                                               placeholder="Buscar por código o nombre..." 
                                               value="{{ request('search_deps') }}">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="submit">
                                                <i class="fas fa-search"></i>
                                            </button>
                                            @if(request('search_deps'))
                                                <a href="{{ route('home') }}" class="btn btn-secondary">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </form>
                                @if(count($deps))
                                    <ul class="list-unstyled mb-0">
                                        @foreach($deps as $d)
                                            <li><i class="fas fa-square text-primary mr-1"></i> {{ $d->codigo }} - {{ $d->nombre }}</li>
                                        @endforeach
                                    </ul>
                                    <div class="mt-3">
                                        {{ $deps->appends(['search_deps' => request('search_deps')])->links('pagination::bootstrap-4') }}
                                    </div>
                                @else
                                    <p>No hay departamentos.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contratos -->
                <div class="row">
                    <div class="col-12">
                        <div class="card" id="contratos">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-file-signature mr-1"></i> Contratos</h3>
                            </div>
                            <div class="card-body">
                                <form method="GET" action="{{ route('home') }}" class="mb-3">
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="search_contratos" class="form-control" 
                                               placeholder="Buscar por tipo o frecuencia..." 
                                               value="{{ request('search_contratos') }}">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="submit">
                                                <i class="fas fa-search"></i>
                                            </button>
                                            @if(request('search_contratos'))
                                                <a href="{{ route('home') }}" class="btn btn-secondary">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </form>
                                @if(count($contratosList))
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Código</th>
                                                    <th>Cédula</th>
                                                    <th>Empleado</th>
                                                    <th>Tipo</th>
                                                    <th>Frecuencia</th>
                                                    <th class="text-right">Salario</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($contratosList as $c)
                                                <tr>
                                                    <td>{{ $c->id }}</td>
                                                    <td>{{ $c->empleado_codigo ?? '-' }}</td>
                                                    <td>{{ $c->empleado_cedula ?? '-' }}</td>
                                                    <td>{{ $c->empleado_name ?? '-' }}</td>
                                                    <td>{{ $c->tipo_contrato }}</td>
                                                    <td>{{ $c->frecuencia_pago }}</td>
                                                    <td class="text-right">{{ isset($c->salario_base) ? number_format($c->salario_base, 2) : '-' }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mt-3">
                                        {{ $contratosList->appends(['search_contratos' => request('search_contratos')])->links('pagination::bootstrap-4') }}
                                    </div>
                                @else
                                    <p>No hay contratos.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Periodos de nómina -->
                <div class="row">
                    <div class="col-12">
                        <div class="card" id="periodos">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-calendar-alt mr-1"></i> Periodos de nómina</h3>
                            </div>
                            <div class="card-body">
                                <form method="GET" action="{{ route('home') }}" class="mb-3">
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="search_periodos" class="form-control" 
                                               placeholder="Buscar por código o estado..." 
                                               value="{{ request('search_periodos') }}">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="submit">
                                                <i class="fas fa-search"></i>
                                            </button>
                                            @if(request('search_periodos'))
                                                <a href="{{ route('home') }}" class="btn btn-secondary">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </form>
                                @if(count($periodosList))
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead><tr><th>Código</th><th>Inicio</th><th>Fin</th><th>Estado</th></tr></thead>
                                            <tbody>
                                            @foreach($periodosList as $p)
                                                <tr>
                                                    <td>{{ $p->codigo }}</td>
                                                    <td>{{ $p->fecha_inicio }}</td>
                                                    <td>{{ $p->fecha_fin }}</td>
                                                    <td>{{ $p->estado }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mt-3">
                                        {{ $periodosList->appends(['search_periodos' => request('search_periodos')])->links('pagination::bootstrap-4') }}
                                    </div>
                                @else
                                    <p>No hay periodos.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

@endunless

                <!-- Recibos y Pagos -->
                <div class="row">
                    <div class="col-12">
                        <div class="card" id="recibos-pagos">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-file-invoice-dollar mr-1"></i> Recibos y Pagos</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Recibos recientes</h6>
                                        <form method="GET" action="{{ route('home') }}" class="mb-3">
                                            <div class="input-group input-group-sm">
                                                <input type="text" name="search_recibos" class="form-control" 
                                                       placeholder="Buscar por estado..." 
                                                       value="{{ request('search_recibos') }}">
                                                <div class="input-group-append">
                                                    <button class="btn btn-primary" type="submit">
                                                        <i class="fas fa-search"></i>
                                                    </button>
                                                    @if(request('search_recibos'))
                                                        <a href="{{ route('home') }}" class="btn btn-secondary">
                                                            <i class="fas fa-times"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </form>
                                        @if(count($recibosList))
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Código empleado</th>
                                                            <th>Cédula</th>
                                                            <th>Empleado</th>
                                                            <th>Neto</th>
                                                            <th>Estado</th>
                                                            <th>Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($recibosList as $r)
                                                        <tr>
                                                            <td>{{ $r->empleado_codigo ?? '-' }}</td>
                                                            <td>{{ $r->empleado_cedula ?? '-' }}</td>
                                                            <td>{{ $r->empleado_name ?? '-' }}</td>
                                                            <td>{{ number_format($r->neto, 2) }}</td>
                                                            <td>{{ $r->estado }}</td>
                                                            <td>
                                                                <a class="btn btn-xs btn-outline-primary" target="_blank" href="{{ route('nomina.recibo.pdf', ['recibo'=>$r->id]) }}">Ver recibo</a>
                                                                <a class="btn btn-xs btn-outline-secondary" href="{{ route('contratos.by_employee', ['userId' => $r->empleado_user_id ?? ($r->empleado_id ?? 0)]) }}">Ver contrato</a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="mt-3">
                                                {{ $recibosList->appends(['search_recibos' => request('search_recibos')])->links('pagination::bootstrap-4') }}
                                            </div>
                                        @else
                                            <p>No hay recibos.</p>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Historial de pagos</h6>
                                        <form method="GET" action="{{ route('home') }}" class="mb-3">
                                            <div class="row">
                                                <div class="col-md-6 mb-2">
                                                    <label class="small">Método de pago</label>
                                                    <select name="metodo_pago" class="form-control form-control-sm">
                                                        <option value="">Todos los métodos</option>
                                                        @foreach($metodosPago as $metodo)
                                                            <option value="{{ $metodo }}" {{ request('metodo_pago') == $metodo ? 'selected' : '' }}>
                                                                {{ $metodo }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                @if($esEmpleado)
                                                <div class="col-md-6 mb-2">
                                                    <label class="small">Estado</label>
                                                    <select name="estado_pago" class="form-control form-control-sm">
                                                        <option value="">Todos los estados</option>
                                                        <option value="aceptado" {{ request('estado_pago') == 'aceptado' ? 'selected' : '' }}>Aceptado</option>
                                                        <option value="rechazado" {{ request('estado_pago') == 'rechazado' ? 'selected' : '' }}>Rechazado</option>
                                                        <option value="pendiente" {{ request('estado_pago') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                                    </select>
                                                </div>
                                                @endif
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-2">
                                                    <label class="small">Desde</label>
                                                    <input type="date" name="desde_pago" value="{{ request('desde_pago') }}" class="form-control form-control-sm">
                                                </div>
                                                <div class="col-md-6 mb-2">
                                                    <label class="small">Hasta</label>
                                                    <input type="date" name="hasta_pago" value="{{ request('hasta_pago') }}" class="form-control form-control-sm">
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <button class="btn btn-sm btn-primary" type="submit">
                                                    <i class="fas fa-filter"></i> Filtrar
                                                </button>
                                                @if(request('metodo_pago') || request('estado_pago') || request('desde_pago') || request('hasta_pago'))
                                                    <a href="{{ route('home') }}" class="btn btn-sm btn-secondary">
                                                        <i class="fas fa-times"></i> Limpiar
                                                    </a>
                                                @endif
                                            </div>
                                        </form>
                                        @if(count($pagosList))
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Fecha</th>
                                                            <th>Importe</th>
                                                            <th>Método</th>
                                                            <th>Estatus</th>
                                                            <th>Código</th>
                                                            <th>Cédula</th>
                                                            <th>Empleado</th>
                                                            @if(!$esEmpleado)
                                                                <th>Acciones</th>
                                                            @endif
                                                            @if($esEmpleado)
                                                                <th>Descripción</th>
                                                                <th>Acciones</th>
                                                            @endif
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($pagosList as $pg)
                                                        <tr>
                                                            <?php 
                                                                $fechaPago = isset($pg->pagado_en) && $pg->pagado_en ? $pg->pagado_en : (isset($pg->updated_at) && $pg->updated_at ? $pg->updated_at : ($pg->created_at ?? null)); 
                                                            ?>
                                                            <td>{{ $fechaPago ? \Illuminate\Support\Carbon::parse($fechaPago)->format('d/m/Y') : '—' }}</td>
                                                            <td>{{ number_format($pg->importe, 2) }}</td>
                                                            <td>{{ $pg->metodo }}</td>
                                                            <td><span class="badge bg-{{ ($pg->estado ?? 'pendiente') === 'aceptado' ? 'success' : (($pg->estado ?? 'pendiente') === 'rechazado' ? 'danger' : 'warning') }}">{{ $pg->estado ?? 'pendiente' }}</span></td>
                                                            <td>{{ $pg->empleado_codigo ?? '-' }}</td>
                                                            <td>{{ $pg->empleado_cedula ?? '-' }}</td>
                                                            <td>{{ $pg->empleado_name ?? '-' }}</td>
                                                            @if(!$esEmpleado)
                                                            <td>
                                                                <a class="btn btn-xs btn-outline-primary" target="_blank" href="{{ route('nomina.recibo.pdf', ['recibo'=>$pg->recibo_id ?? 0]) }}">Ver recibo</a>
                                                                <a class="btn btn-xs btn-outline-secondary" href="{{ route('contratos.by_employee', ['userId' => $pg->empleado_user_id ?? ($pg->empleado_id ?? 0)]) }}">Ver contrato</a>
                                                            </td>
                                                            @endif
                                                            @if($esEmpleado)
                                                            <td>{{ $pg->descripcion ?? '-' }}</td>
                                                            <td>
                                                                @if(($pg->estado ?? 'pendiente') === 'pendiente')
                                                                    <form method="POST" action="{{ route('pagos.aceptar', ['pago'=>$pg->id]) }}" class="d-inline">@csrf<button class="btn btn-xs btn-success">Aceptar</button></form>
                                                                    <form method="POST" action="{{ route('pagos.rechazar', ['pago'=>$pg->id]) }}" class="d-inline" onsubmit="return confirm('¿Rechazar este pago?')">@csrf<button class="btn btn-xs btn-danger">Rechazar</button></form>
                                                                @else
                                                                    <a class="btn btn-xs btn-outline-primary" target="_blank" href="{{ route('nomina.recibo.pdf', ['recibo'=>$pg->recibo_id ?? 0]) }}">Imprimir recibo</a>
                                                                @endif
                                                            </td>
                                                            @endif
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="mt-3">
                                                {{ $pagosList->appends([
                                                    'metodo_pago' => request('metodo_pago'),
                                                    'estado_pago' => request('estado_pago'),
                                                    'desde_pago' => request('desde_pago'),
                                                    'hasta_pago' => request('hasta_pago')
                                                ])->links('pagination::bootstrap-4') }}
                                            </div>
                                        @else
                                            <p>No hay pagos.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
                <!-- Contrato del empleado (visible solo para empleados) -->
                @if(isset($esEmpleado) && $esEmpleado)
                    <div class="row mt-3">
                        <div class="col-12">
                            @if(!empty($contratoInfo))
                                <div class="card shadow-sm border-left-primary">
                                    <div class="card-header bg-white d-flex align-items-center justify-content-between">
                                        <div>
                                            <h5 class="mb-0"><i class="fas fa-user-check mr-2 text-primary"></i>Mi contrato</h5>
                                            <small class="text-muted">Información rápida sobre tu contrato</small>
                                        </div>
                                        <div>
                                            @if(!empty($contratoInfo['estado']))
                                                @php
                                                    $estado = strtolower($contratoInfo['estado']);
                                                    $badgeClass = $estado === 'activo' ? 'bg-success' : ($estado === 'inactivo' || $estado === 'vencido' ? 'bg-danger' : 'bg-secondary');
                                                @endphp
                                                <span class="badge {{ $badgeClass }}">{{ ucfirst($contratoInfo['estado']) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <h6 class="mb-1">{{ $contratoInfo['puesto'] ?? '-' }}</h6>
                                                <p class="mb-1 text-muted">Tipo: 
                                                    @php
                                                        $tipo = $contratoInfo['tipo_contrato'] ?? '';
                                                        $tipoFmt = $tipo ? \Illuminate\Support\Str::of($tipo)->replace('_', ' ')->title() : '-';
                                                    @endphp
                                                    <strong>{{ $tipoFmt }}</strong>
                                                </p>
                                                <p class="mb-1 text-muted">Periodo: 
                                                    @php
                                                        $fi = $contratoInfo['fecha_inicio'] ?? null;
                                                        $ff = $contratoInfo['fecha_fin'] ?? null;
                                                    @endphp
                                                    <strong>{{ $fi ? \Carbon\Carbon::parse($fi)->format('d/m/Y') : '-' }}</strong>
                                                     —
                                                    <strong>{{ $ff ? \Carbon\Carbon::parse($ff)->format('d/m/Y') : 'Indefinida' }}</strong>
                                                </p>
                                                @if(!is_null($contratoInfo['salario_base']))
                                                    <p class="mb-0 text-muted">Salario base: <strong>{{ number_format($contratoInfo['salario_base'], 2) }}</strong></p>
                                                @endif
                                            </div>
                                            <div class="col-md-4 d-flex align-items-center justify-content-end">
                                                <div class="text-right">
                                                    @if(is_null($contratoInfo['days_remaining']))
                                                        <span class="badge bg-info">Vigencia indefinida</span>
                                                    @else
                                                        @if($contratoInfo['expired'])
                                                            <span class="badge bg-danger">Vencido</span>
                                                            <div class="text-muted small">Finalizó el {{ $contratoInfo['fecha_fin'] ? \Carbon\Carbon::parse($contratoInfo['fecha_fin'])->format('d/m/Y') : '-' }}</div>
                                                        @else
                                                            <span class="badge bg-primary">{{ $contratoInfo['days_remaining'] }} días restantes</span>
                                                            <div class="text-muted small">Hasta {{ $contratoInfo['fecha_fin'] ? \Carbon\Carbon::parse($contratoInfo['fecha_fin'])->format('d/m/Y') : '—' }}</div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="card">
                                    <div class="card-body">
                                        <p>No estás asignado a ningún contrato activo o vigente.</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

        </section>
    </div>

@endsection