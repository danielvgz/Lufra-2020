@extends('layouts')
@section('content')

  <style>
    @media print {
      .no-print { display: none !important; }
      table { font-size: 11px; }
    }
        h1 { font-size: 18px; margin: 0 0 8px; }
        h2 { font-size: 14px; margin: 16px 0 8px; }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 6px 8px; border: 1px solid #ccc; font-size: 12px; }
        .right { text-align: right; }
        .muted { color: #666; }
        .header { display:flex; flex-direction: column; align-items: flex-start; margin-bottom: 24px; }
        .header img {max-width: 100px; max-height: 60px; margin-bottom: 8px; }
        .header .brand-text { font-size: 20px; font-weight: bold; margin-bottom: 4px; }
        .header .company-info { font-size: 12px; color: #666; margin-bottom: 2px; }
  </style>
 <div class="header">
                @if(config('settings.image'))
                    <img src="{{ asset('storage/settings/') }}/{{ config('settings.image') }}" alt="Logo">
                @endif
                <span class="brand-text">
                    {{ config('settings.app_name', config('app.name', 'Sistema de Nóminas')) }}
                </span>
                @if(config('settings.register_number'))
                    <small class="company-info"><i class="fas fa-id-card mr-1"></i>{{ config('settings.register_number') }}</small>
                @endif
                @if(config('settings.app_email'))
                    <small class="company-info"><i class="fas fa-envelope mr-1"></i>{{ config('settings.app_email') }}</small>
                @endif

                @if (config('settings.app_address1') || config('settings.app_address2'))
                    <small class="company-info"><i class="fas fa-map-marker-alt mr-1"></i>
                        {{ config('settings.app_address1') }}
                        @if (config('settings.app_address1') && config('settings.app_address2'))
                            ,
                        @endif
                        {{ config('settings.app_address2') }}
                    </small>
                @endif

                @if( config('settings.city'))
                    <small class="company-info"><i class="fas fa-city mr-1"></i>{{ config('settings.city') }}</small>
                @endif

                @if(config('settings.state') || config('settings.zip_code'))
                    <small class="company-info"><i class="fas fa-map-marked-alt mr-1"></i>
                        {{ config('settings.state') }}
                        @if (config('settings.state') && config('settings.zip_code'))
                            ,
                        @endif
                        {{ config('settings.zip_code') }}
                    </small>
                @endif
            </div>
<div class="container mt-3">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Reporte detallado de recibos y pagos</h4>
    <button class="btn btn-sm btn-primary no-print" onclick="window.print()">Imprimir / Guardar PDF</button>
  </div>

  <form method="GET" action="{{ route('recibos_pagos.reportes_detalle') }}" class="form-inline no-print mb-3">
    <label class="mr-2">Desde</label>
    <input type="date" name="desde" value="{{ $desde }}" class="form-control form-control-sm mr-2">
    <label class="mr-2">Hasta</label>
    <input type="date" name="hasta" value="{{ $hasta }}" class="form-control form-control-sm mr-2">
    <label class="mr-2">Buscar</label>
    <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Empleado, período o #recibo" class="form-control form-control-sm mr-2" style="min-width:220px;">
    <button class="btn btn-sm btn-secondary">Actualizar</button>
  </form>

  @if(count($rows))
    <div class="table-responsive">
      <table class="table table-sm table-bordered">
        <thead>
          <tr>
            <th>Período</th>
            <th>Inicio</th>
            <th>Fin</th>
            <th>Recibo</th>
            <th>Empleado</th>
            <th>Neto</th>
            <th>Pago</th>
            <th>Método</th>
            <th>Importe pago</th>
            <th>Descripción</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody>
        @foreach($rows as $r)
          <tr>
            <td>{{ $r->periodo }}</td>
            <td>{{ $r->fecha_inicio }}</td>
            <td>{{ $r->fecha_fin }}</td>
            <td>#{{ $r->recibo_id }}</td>
            <td>{{ $r->nombre }} {{ $r->apellido }}</td>
            <td>{{ number_format($r->neto,2) }}</td>
            <td>{{ $r->pago_id ? ('#'.$r->pago_id) : '—' }}</td>
            <td>{{ $r->metodo ?? '—' }}</td>
            <td>{{ $r->importe ? number_format($r->importe,2) : '—' }}</td>
            <td>{{ $r->descripcion ?? '—' }}</td>
            <td>{{ $r->estado ?? 'pendiente' }}</td>
          </tr>
         
        @endforeach
        <!-- <tr>
            <td colspan="6" class="muted">total: |</td>
            <td colspan="9" class="muted">total: |</td>
          </tr> -->
        </tbody>
      </table>
    </div>
  @else
    <p>No hay datos para el rango seleccionado.</p>
  @endif
</div>

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