@extends('layouts')
@section('content')
<?php
use Illuminate\Support\Facades\DB;
$empleados = DB::table('empleados')->count();
$departamentos = DB::table('departamentos')->count();
$contratos = DB::table('contratos')->count();
$periodos = DB::table('periodos_nomina')->count();
$recibos = DB::table('recibos')->count();
$pagos = DB::table('pagos')->count();
$esEmpleado = false;
if (auth()->check()) {
    $esEmpleado = DB::table('rol_usuario as ru')
        ->join('roles as r','r.id','=','ru.rol_id')
        ->where('ru.user_id', auth()->id())
        ->where('r.nombre','empleado')
        ->exists();
}
?>

 
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
                                <?php
                                $ultimoPeriodo = DB::table('periodos_nomina')->orderByDesc('fecha_fin')->first();
                                ?>
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
                                <?php $deps = DB::table('departamentos')->select('codigo','nombre')->limit(10)->get(); ?>
                                @if(count($deps))
                                    <ul class="list-unstyled mb-0">
                                        @foreach($deps as $d)
                                            <li><i class="fas fa-square text-primary mr-1"></i> {{ $d->codigo }} - {{ $d->nombre }}</li>
                                        @endforeach
                                    </ul>
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
                                <?php $contratosList = DB::table('contratos')->select('id','tipo_contrato','frecuencia_pago','salario_base')->limit(10)->get(); ?>
                                @if(count($contratosList))
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead><tr><th>ID</th><th>Tipo</th><th>Frecuencia</th><th>Salario</th></tr></thead>
                                            <tbody>
                                            @foreach($contratosList as $c)
                                                <tr>
                                                    <td>{{ $c->id }}</td>
                                                    <td>{{ $c->tipo_contrato }}</td>
                                                    <td>{{ $c->frecuencia_pago }}</td>
                                                    <td>{{ number_format($c->salario_base, 2) }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
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
                                <?php $periodosList = DB::table('periodos_nomina')->select('codigo','fecha_inicio','fecha_fin','estado')->orderByDesc('fecha_inicio')->limit(10)->get(); ?>
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
                                <?php
                                    if ($esEmpleado) {
                                        $recibosList = DB::table('recibos as r')
                                            ->join('empleados as e','e.id','=','r.empleado_id')
                                            ->where('e.user_id', auth()->id())
                                            ->select('r.neto','r.estado')
                                            ->orderByDesc('r.id')->limit(10)->get();
                                        $pagosList = DB::table('pagos as p')
                                            ->join('recibos as r','r.id','=','p.recibo_id')
                                            ->join('empleados as e','e.id','=','r.empleado_id')
                                            ->where('e.user_id', auth()->id())
                                            ->whereIn('p.estado', ['aceptado','rechazado'])
                                            ->select('p.recibo_id','p.importe','p.metodo','p.referencia as descripcion','p.estado','p.id')
                                            ->orderByDesc('p.id')->limit(10)->get();
                                    } else {
                                        $recibosList = DB::table('recibos')->select('id','empleado_id','neto','estado')->orderByDesc('id')->limit(10)->get();
                                        $pagosList = DB::table('pagos')->select('id','recibo_id','importe','metodo')->orderByDesc('id')->limit(10)->get();
                                    }
                                ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Recibos recientes</h6>
                                        @if(count($recibosList))
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead><tr><th>Neto</th><th>Estado</th></tr></thead>
                                                    <tbody>
                                                    @foreach($recibosList as $r)
                                                        <tr>
                                                            <td>{{ number_format($r->neto, 2) }}</td>
                                                            <td>{{ $r->estado }}</td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <p>No hay recibos.</p>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Historial de pagos</h6>
                                        @if($esEmpleado)
                                            <form method="GET" action="{{ url()->current() }}" class="form-inline mb-2">
                                                <input type="text" name="q" value="{{ request('q','') }}" class="form-control form-control-sm mr-2" placeholder="Buscar por método o descripción">
                                                <label class="mr-2">Desde</label>
                                                <input type="date" name="desde" value="{{ request('desde') }}" class="form-control form-control-sm mr-2">
                                                <label class="mr-2">Hasta</label>
                                                <input type="date" name="hasta" value="{{ request('hasta') }}" class="form-control form-control-sm mr-2">
                                                <button class="btn btn-sm btn-outline-secondary">Filtrar</button>
                                            </form>
                                        @endif
                                        @if(count($pagosList))
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead><tr><th>Fecha</th><th>Importe</th><th>Método</th><th>Descripción</th><th>Estado</th>@if($esEmpleado)<th>Acciones</th>@endif</tr></thead>
                                                    <tbody>
                                                    @foreach($pagosList as $pg)
                                                        <tr>
                                                            <td>{{ \Illuminate\Support\Carbon::parse($pg->respondido_en ?? $pg->updated_at)->format('Y-m-d') }}</td>
                                                            <td>{{ number_format($pg->importe, 2) }}</td>
                                                            <td>{{ $pg->metodo }}</td>
                                                            <td>{{ $pg->descripcion ?? '-' }}</td>
                                                            <td><span class="badge badge-{{ ($pg->estado ?? 'pendiente') === 'aceptado' ? 'success' : (($pg->estado ?? 'pendiente') === 'rechazado' ? 'danger' : 'warning') }}">{{ $pg->estado ?? 'pendiente' }}</span></td>
                                                            @if($esEmpleado)
                                                            <td>
                                                                @if(($pg->estado ?? 'pendiente') !== 'pendiente')
                                                                    <a class="btn btn-xs btn-outline-primary" target="_blank" href="{{ route('nomina.recibo.pdf', ['recibo'=>$pg->recibo_id ?? 0]) }}">Imprimir recibo</a>
                                                                @endif
                                                            </td>
                                                            @endif
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
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
        </section>
    </div>

@endsection