@extends('layouts')

@section('content')
  <div class="card">
          <div class="card-header"><h3 class="card-title"><i class="fas fa-file-signature mr-1"></i> Contratos</h3></div>
          <div class="card-body">
            <p class="text-muted">Gestión de contratos laborales: búsqueda, tipificación y alertas de vencimientos.</p>

            <form method="GET" action="{{ url('/contratos') }}" class="form-inline mb-3">
              <input type="text" name="q" value="{{ request('q') }}" placeholder="Empleado, puesto o #empleado" class="form-control form-control-sm mr-2" style="min-width:220px;">
              <select name="tipo" class="form-control form-control-sm mr-2">
                <option value="">Tipo de contrato</option>
                @foreach(['indefinido','termino_fijo','obra_labor','tiempo_parcial'] as $op)
                  <option value="{{ $op }}" {{ request('tipo')===$op ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ', $op)) }}</option>
                @endforeach
              </select>
              <label class="mr-2">Desde</label>
              <input type="date" name="desde" value="{{ request('desde') }}" class="form-control form-control-sm mr-2">
              <label class="mr-2">Hasta</label>
              <input type="date" name="hasta" value="{{ request('hasta') }}" class="form-control form-control-sm mr-2">
              <button class="btn btn-sm btn-primary">Filtrar</button>
              <a href="{{ url('/contratos') }}" class="btn btn-sm btn-light ml-2">Limpiar</a>
              <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-secondary ml-2">Imprimir / PDF</button>
            </form>

            @if(count($alertas))
              <div class="alert alert-warning py-2">
                <strong>Alertas:</strong> Contratos por vencer en 30 días:
                <ul class="mb-0">
                  @foreach($alertas as $a)
                    <li>#{{ $a->id }} - {{ $a->empleado_name ?? ($a->nombre ?? ($a->apellido ?? 'Empleado')) }} (vence {{ $a->fecha_fin }})</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <hr>
            <h6>Nuevo contrato</h6>
            <form method="POST" action="{{ route('contratos.store') }}" class="mb-4">
              @csrf
              <div class="form-row">
                <div class="col-md-3 mb-2">
                  <label>Empleado</label>
                  <select name="empleado_id" class="form-control form-control-sm" required>
                    <option value="">Seleccione…</option>
                    @foreach($emps as $e)
                      <option value="{{ $e->id }}">{{ $e->name }} ({{ $e->email ?? $e->id }})</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-2 mb-2">
                  <label>Tipo</label>
                  <select name="tipo_contrato" class="form-control form-control-sm">
                    <option value="">—</option>
                    @foreach(['indefinido','termino_fijo','obra_labor','tiempo_parcial'] as $op)
                      <option value="{{ $op }}">{{ ucfirst(str_replace('_',' ', $op)) }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-2 mb-2">
                  <label>Frecuencia</label>
                  <select name="frecuencia_pago" class="form-control form-control-sm" required>
                    @foreach(['mensual','quincenal','semanal'] as $fq)
                      <option value="{{ $fq }}">{{ ucfirst($fq) }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-2 mb-2">
                  <label>Puesto</label>
                  <input type="text" name="puesto" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-2 mb-2">
                  <label>Salario base</label>
                  <input type="number" step="0.01" min="0" name="salario_base" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-3 mb-2">
                  <label>Estado</label>
                  <select name="estado" class="form-control form-control-sm" required>
                    @foreach(['activo','suspendido','terminado'] as $es)
                      <option value="{{ $es }}">{{ ucfirst($es) }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="form-row">
                <div class="col-md-2 mb-2"><label>Inicio</label><input type="date" name="fecha_inicio" required class="form-control form-control-sm"></div>
                <div class="col-md-2 mb-2"><label>Fin prueba</label><input type="date" name="periodo_prueba_fin" class="form-control form-control-sm"></div>
                <div class="col-md-2 mb-2"><label>Fin</label><input type="date" name="fecha_fin" class="form-control form-control-sm"></div>
                <div class="col-md-3 mb-2 align-self-end"><button class="btn btn-sm btn-success">Crear</button></div>
              </div>
            </form>

            @if(count($items))
              <div class="table-responsive">
                <table class="table table-sm">
                  <thead>
                    <tr>
                      <th>ID</th><th>Empleado</th><th>Tipo</th><th>Frecuencia</th><th>Puesto</th><th>Inicio</th><th>Prueba fin</th><th>Fin</th><th>Salario base</th><th>Estado</th><th>Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                  @foreach($items as $c)
                    <tr>
                      <td>{{ $c->id }}</td>
                      <td>{{ $c->empleado_name ?? ($c->empleado_email ?? $c->empleado_id) }} ({{ $c->empleado_id }})</td>
                      <td>{{ $c->tipo_contrato ?? '—' }}</td>
                      <td>{{ $c->frecuencia_pago ?? '—' }}</td>
                      <td>{{ $c->puesto }}</td>
                      <td>{{ $c->fecha_inicio ?? '—' }}</td>
                      <td>{{ $c->periodo_prueba_fin ?? '—' }}</td>
                      <td>{{ $c->fecha_fin ?? '—' }}</td>
                      <td>{{ isset($c->salario_base) ? number_format($c->salario_base,2) : '—' }}</td>
                      <td>{{ $c->estado }}</td>
                      <td>
                        <a class="btn btn-xs btn-outline-primary" data-toggle="collapse" href="#edit-{{ $c->id }}">Editar</a>
                        <form method="POST" action="{{ route('contratos.destroy', ['id'=>$c->id]) }}" class="d-inline" onsubmit="return confirm('¿Eliminar contrato #{{ $c->id }}?')">
                          @csrf
                          <button class="btn btn-xs btn-outline-danger">Eliminar</button>
                        </form>
                      </td>
                    </tr>
                    <tr class="collapse" id="edit-{{ $c->id }}">
                      <td colspan="10">
                        <form method="POST" action="{{ route('contratos.update', ['id'=>$c->id]) }}">
                          @csrf
                          <div class="form-row">
                            <div class="col-md-2 mb-2"><label>Tipo</label>
                              <select name="tipo_contrato" class="form-control form-control-sm">
                                <option value="">—</option>
                                @foreach(['indefinido','termino_fijo','obra_labor','tiempo_parcial'] as $op)
                                  <option value="{{ $op }}" {{ ($c->tipo_contrato??'')===$op ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ', $op)) }}</option>
                                @endforeach
                              </select>
                            </div>
                            <div class="col-md-2 mb-2"><label>Frecuencia</label>
                              <select name="frecuencia_pago" class="form-control form-control-sm">
                                @foreach(['mensual','quincenal','semanal'] as $fq)
                                  <option value="{{ $fq }}" {{ ($c->frecuencia_pago??'')===$fq ? 'selected' : '' }}>{{ ucfirst($fq) }}</option>
                                @endforeach
                              </select>
                            </div>
                            <div class="col-md-2 mb-2"><label>Puesto</label><input type="text" name="puesto" value="{{ $c->puesto }}" class="form-control form-control-sm"></div>
                            <div class="col-md-2 mb-2"><label>Salario</label><input type="number" step="0.01" min="0" name="salario_base" value="{{ $c->salario_base }}" class="form-control form-control-sm"></div>
                            <div class="col-md-2 mb-2"><label>Estado</label>
                              <select name="estado" class="form-control form-control-sm">
                                @foreach(['activo','suspendido','terminado'] as $es)
                                  <option value="{{ $es }}" {{ $c->estado===$es ? 'selected' : '' }}>{{ ucfirst($es) }}</option>
                                @endforeach
                              </select>
                            </div>
                            <div class="col-md-2 mb-2"><label>Inicio</label><input type="date" name="fecha_inicio" value="{{ $c->fecha_inicio }}" class="form-control form-control-sm"></div>
                            <div class="col-md-2 mb-2"><label>Fin prueba</label><input type="date" name="periodo_prueba_fin" value="{{ $c->periodo_prueba_fin }}" class="form-control form-control-sm"></div>
                            <div class="col-md-2 mb-2"><label>Fin</label><input type="date" name="fecha_fin" value="{{ $c->fecha_fin }}" class="form-control form-control-sm"></div>
                            <div class="col-md-2 mb-2 align-self-end"><button class="btn btn-sm btn-primary">Guardar</button></div>
                          </div>
                        </form>
                      </td>
                    </tr>
                  @endforeach
                  </tbody>
                </table>
              </div>
              <div class="mt-3">
                {{ $items->appends(request()->query())->links('pagination::bootstrap-4') }}
              </div>
            @else
              <p>No hay contratos registrados.</p>
            @endif
      </div>
    </div>
  </div>
@endsection
