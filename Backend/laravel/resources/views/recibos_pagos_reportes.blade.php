@extends('layouts')
@section('content')

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
@endsection
