@extends('layouts')
@section('content')

            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-5">
                        <div class="card">
                            <div class="card-header"><h3 class="card-title"><i class="fas fa-plus mr-1"></i> Crear periodo</h3></div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('nominas.periodo.crear') }}" class="form">
                                    @csrf
                                    <div class="form-group">
                                        <label>Frecuencia</label>
                                        <select name="frecuencia" class="form-control" required>
                                            <option value="semanal">Semanal</option>
                                            <option value="quincenal">Quincenal</option>
                                            <option value="mensual">Mensual</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Fecha inicio</label>
                                        <input type="date" name="fecha_inicio" class="form-control" required>
                                    </div>
                                    <button class="btn btn-primary">Crear</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="card">
                            <div class="card-header"><h3 class="card-title"><i class="fas fa-list mr-1"></i> Periodos existentes</h3></div>
                            <div class="card-body">
                                <?php $periodos = \Illuminate\Support\Facades\DB::table('periodos_nomina')->select('codigo','fecha_inicio','fecha_fin','estado')->orderByDesc('fecha_inicio')->limit(100)->get(); ?>
                                @if(count($periodos))
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead><tr><th>Código</th><th>Inicio</th><th>Fin</th><th>Estado</th></tr></thead>
                                            <tbody>
                                            @foreach($periodos as $p)
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
            </div>
@endsection