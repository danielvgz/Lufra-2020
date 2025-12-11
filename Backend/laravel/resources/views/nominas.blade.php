@extends('layouts')

@section('content')
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
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="card-title mb-0"><i class="fas fa-list mr-1"></i> Periodos existentes</h3>
                            </div>
                            <div class="card-body">
                                <!-- Caja de búsqueda -->
                                <form method="GET" action="{{ route('nominas.index') }}" class="mb-3">
                                    <div class="input-group">
                                        <input type="text" name="search" class="form-control" 
                                               placeholder="Buscar por código, fecha o estado..." 
                                               value="{{ request('search') }}">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="submit">
                                                <i class="fas fa-search"></i> Buscar
                                            </button>
                                            @if(request('search'))
                                                <a href="{{ route('nominas.index') }}" class="btn btn-secondary">
                                                    <i class="fas fa-times"></i> Limpiar
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </form>

                                @if($periodos->count())
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead><tr><th>Código</th><th>Inicio</th><th>Fin</th><th>Estado</th><th>Acciones</th></tr></thead>
                                            <tbody>
                                            @foreach($periodos as $p)
                                                <tr>
                                                    <td>{{ $p->codigo }}</td>
                                                    <td>{{ $p->fecha_inicio }}</td>
                                                    <td>{{ $p->fecha_fin }}</td>
                                                    <td>{{ $p->estado }}</td>
                                                    <td>
                                                        @if($p->estado === 'abierto')
                                                            <form method="POST" action="{{ route('nominas.periodo.cerrar') }}" class="d-inline" onsubmit="return confirm('¿Cerrar período {{ $p->codigo }}?')">
                                                                @csrf
                                                                <input type="hidden" name="periodo_id" value="{{ $p->id }}">
                                                                <button class="btn btn-sm btn-warning">Cerrar período</button>
                                                            </form>
                                                        @else
                                                            <form method="POST" action="{{ route('nominas.periodo.reabrir') }}" class="d-inline" onsubmit="return confirm('¿Reabrir período {{ $p->codigo }}?')">
                                                                @csrf
                                                                <input type="hidden" name="periodo_id" value="{{ $p->id }}">
                                                                <button class="btn btn-sm btn-success">Reabrir período</button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mt-3">
                                        {{ $periodos->appends(['search' => request('search')])->links('pagination::bootstrap-4') }}
                                    </div>
                                @else
                                    @if(request('search'))
                                        <div class="alert alert-info">
                                            No se encontraron períodos que coincidan con "{{ request('search') }}".
                                            <a href="{{ route('nominas.index') }}" class="alert-link">Ver todos</a>
                                        </div>
                                    @else
                                        <p>No hay periodos.</p>
                                    @endif
                                @endif

                                <hr>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">Historial de períodos cerrados</h5>
                                </div>
                                
                                <!-- Búsqueda para historial -->
                                <form method="GET" action="{{ route('nominas.index') }}" class="mb-3">
                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="search_cerrados" class="form-control" 
                                               placeholder="Buscar en historial..." 
                                               value="{{ request('search_cerrados') }}">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary btn-sm" type="submit">
                                                <i class="fas fa-search"></i>
                                            </button>
                                            @if(request('search_cerrados'))
                                                <a href="{{ route('nominas.index') }}?search={{ request('search') }}" class="btn btn-secondary btn-sm">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </form>

                                @if($cerrados->count())
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead><tr><th>Inicio</th><th>Fin</th><th>Código</th><th>Estado</th></tr></thead>
                                            <tbody>
                                            @foreach($cerrados as $c)
                                                <tr>
                                                    <td>{{ $c->fecha_inicio }}</td>
                                                    <td>{{ $c->fecha_fin }}</td>
                                                    <td>{{ $c->codigo }}</td>
                                                    <td>{{ $c->estado }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mt-3">
                                        {{ $cerrados->appends(['search' => request('search'), 'search_cerrados' => request('search_cerrados')])->links('pagination::bootstrap-4') }}
                                    </div>
                                @else
                                    @if(request('search_cerrados'))
                                        <div class="alert alert-info">
                                            No se encontraron períodos cerrados que coincidan con "{{ request('search_cerrados') }}".
                                            <a href="{{ route('nominas.index') }}" class="alert-link">Ver todos</a>
                                        </div>
                                    @else
                                        <p>No hay períodos cerrados.</p>
                                    @endif
                                @endif
                            </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection