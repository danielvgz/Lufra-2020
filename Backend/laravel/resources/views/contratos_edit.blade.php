@extends('layouts')
@section('content')
  <div class="card">
    <div class="card-header"><h3 class="card-title">Editar contrato #{{ $contrato->id }}</h3></div>
    <div class="card-body">
      <form method="POST" action="{{ route('contratos.update', ['id' => $contrato->id]) }}">
        @csrf
        <div class="form-row">
          <div class="col-md-3 mb-2"><label>Tipo</label>
            <select name="tipo_contrato" class="form-control form-control-sm">
              <option value="">—</option>
              @foreach(['indefinido','termino_fijo','obra_labor','tiempo_parcial'] as $op)
                <option value="{{ $op }}" {{ ($contrato->tipo_contrato??'')===$op ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ', $op)) }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2 mb-2"><label>Frecuencia</label>
            <select name="frecuencia_pago" class="form-control form-control-sm">
              @foreach(['mensual','quincenal','semanal'] as $fq)
                <option value="{{ $fq }}" {{ ($contrato->frecuencia_pago??'')===$fq ? 'selected' : '' }}>{{ ucfirst($fq) }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3 mb-2"><label>Puesto</label><input type="text" name="puesto" value="{{ $contrato->puesto }}" class="form-control form-control-sm"></div>
          <div class="col-md-2 mb-2"><label>Salario</label><input type="number" step="0.01" min="0" name="salario_base" value="{{ $contrato->salario_base }}" class="form-control form-control-sm"></div>
          <div class="col-md-2 mb-2"><label>Estado</label>
            <select name="estado" class="form-control form-control-sm">
              @foreach(['activo','suspendido','terminado'] as $es)
                <option value="{{ $es }}" {{ $contrato->estado===$es ? 'selected' : '' }}>{{ ucfirst($es) }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-row mt-2">
          <div class="col-md-2 mb-2"><label>Inicio</label><input type="date" name="fecha_inicio" value="{{ $contrato->fecha_inicio }}" class="form-control form-control-sm"></div>
          <div class="col-md-2 mb-2"><label>Fin prueba</label><input type="date" name="periodo_prueba_fin" value="{{ $contrato->periodo_prueba_fin }}" class="form-control form-control-sm"></div>
          <div class="col-md-2 mb-2"><label>Fin</label><input type="date" name="fecha_fin" value="{{ $contrato->fecha_fin }}" class="form-control form-control-sm"></div>
          <div class="col-md-3 mb-2 align-self-end"><button class="btn btn-primary">Guardar</button>
            <a href="{{ route('contratos.show', ['id'=>$contrato->id]) }}" class="btn btn-secondary ml-2">Cancelar</a>
          </div>
        </div>
      </form>
    </div>
  </div>
@endsection
