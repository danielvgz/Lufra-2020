@extends('layouts')
@section('content')

  
    <section class="content pt-3">
      <div class="container-fluid">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0"><i class="fas fa-file-invoice-dollar mr-1"></i> Obligaciones por Período</h3>
            <button class="btn btn-sm btn-primary no-print" onclick="window.print()">
              <i class="fas fa-print"></i> Imprimir / Guardar PDF
            </button>
          </div>
          <div class="card-body">
            <form method="GET" action="{{ route('recibos_pagos.obligaciones') }}" class="form-inline mb-3 no-print">
              <label class="mr-2">Desde</label>
              <input type="date" name="desde" value="{{ $desde }}" class="form-control form-control-sm mr-2">
              <label class="mr-2">Hasta</label>
              <input type="date" name="hasta" value="{{ $hasta }}" class="form-control form-control-sm mr-2">
              <button class="btn btn-sm btn-primary">Filtrar</button>
            </form>
            
            @if(count($obligaciones))
              <!-- Título para impresión -->
              <div class="print-only text-center mb-3">
                <h3>Obligaciones por Período</h3>
                @if($desde || $hasta)
                  <p class="mb-1">
                    @if($desde && $hasta)
                      Período: {{ $desde }} al {{ $hasta }}
                    @elseif($desde)
                      Desde: {{ $desde }}
                    @else
                      Hasta: {{ $hasta }}
                    @endif
                  </p>
                @endif
                <p class="text-muted small">Generado el {{ now()->format('d/m/Y H:i') }}</p>
              </div>
              
              <div class="table-responsive">
                <table class="table table-sm table-bordered">
                  <thead>
                    <tr>
                      <th>Período</th>
                      <th>Inicio</th>
                      <th>Fin</th>
                      <th>Total Recibos</th>
                      <th>Total Bruto</th>
                      <th>Total Deducciones</th>
                      <th>Total Neto</th>
                    </tr>
                  </thead>
                  <tbody>
                  @php
                    $sumRecibos = 0;
                    $sumBruto = 0;
                    $sumDeducciones = 0;
                    $sumNeto = 0;
                  @endphp
                  @foreach($obligaciones as $o)
                    @php
                      $sumRecibos += $o->total_recibos;
                      $sumBruto += $o->total_bruto;
                      $sumDeducciones += $o->total_deducciones;
                      $sumNeto += $o->total_neto;
                    @endphp
                    <tr>
                      <td>{{ $o->periodo }}</td>
                      <td>{{ $o->fecha_inicio }}</td>
                      <td>{{ $o->fecha_fin }}</td>
                      <td>{{ $o->total_recibos }}</td>
                      <td>{{ number_format($o->total_bruto, 2) }}</td>
                      <td>{{ number_format($o->total_deducciones, 2) }}</td>
                      <td>{{ number_format($o->total_neto, 2) }}</td>
                    </tr>
                  @endforeach
                  </tbody>
                  <tfoot>
                    <tr class="font-weight-bold">
                      <th colspan="3">TOTAL</th>
                      <th>{{ $sumRecibos }}</th>
                      <th>{{ number_format($sumBruto, 2) }}</th>
                      <th>{{ number_format($sumDeducciones, 2) }}</th>
                      <th>{{ number_format($sumNeto, 2) }}</th>
                    </tr>
                  </tfoot>
                </table>
              </div>
              
              <div class="mt-3 no-print">
                {{ $obligaciones->appends(['desde' => $desde, 'hasta' => $hasta])->links('pagination::bootstrap-4') }}
              </div>
            @else
              <p>No hay obligaciones en el rango seleccionado.</p>
            @endif
          </div>
        </div>
      </div>
    </section>
  
@endsection

@section('scripts')
<style>
  @media print {
    /* Ocultar elementos de navegación */
    .no-print,
    nav,
    aside,
    footer,
    .navbar,
    .sidebar,
    .card-header {
      display: none !important;
    }
    
    /* Eliminar márgenes y bordes de contenedores */
    body {
      margin: 0;
      padding: 0;
    }
    
    .content-wrapper,
    .container-fluid,
    .card,
    .card-body {
      margin: 0 !important;
      padding: 10px !important;
      border: none !important;
      box-shadow: none !important;
      background: white !important;
    }
    
    /* Mostrar el título de impresión */
    .print-only {
      display: block !important;
    }
    
    /* Ajustar la tabla para impresión */
    .table {
      font-size: 11px;
      width: 100%;
      page-break-inside: auto;
    }
    
    .table th,
    .table td {
      padding: 5px 8px;
      page-break-inside: avoid;
      page-break-after: auto;
    }
    
    .table thead {
      display: table-header-group;
    }
    
    .table tfoot {
      display: table-footer-group;
    }
  }
  
  /* Ocultar por defecto el contenido solo de impresión */
  @media screen {
    .print-only {
      display: none;
    }
  }
</style>
@endsection
