<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Archivo Banco</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <style>
    @media print {
      .no-print { display: none !important; }
      table { font-size: 11px; }
    }
       body { font-family: Arial, Helvetica, sans-serif; margin: 24px; }
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
</head>
<body>
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
    <h4>Archivo Banco - Pagos Aceptados</h4>
    <button class="btn btn-sm btn-primary no-print" onclick="window.print()">Imprimir / Guardar PDF</button>
  </div>

  <form method="GET" action="{{ route('recibos_pagos.archivo_banco') }}" class="form-inline no-print mb-3">
    <label class="mr-2">Desde</label>
    <input type="date" name="desde" value="{{ $desde }}" class="form-control form-control-sm mr-2">
    <label class="mr-2">Hasta</label>
    <input type="date" name="hasta" value="{{ $hasta }}" class="form-control form-control-sm mr-2">
    <button class="btn btn-sm btn-secondary">Actualizar</button>
  </form>

  @if(count($pagos))
    <div class="table-responsive">
      <table class="table table-sm table-bordered">
        <thead>
          <tr>
            <th>Empleado</th>
            <th>Número de Cuenta</th>
            <th>Importe</th>
            <th>Moneda</th>
            <th>Período</th>
            <th>Fecha</th>
          </tr>
        </thead>
        <tbody>
        @foreach($pagos as $p)
          <tr>
            <td>{{ $p->nombre }} {{ $p->apellido }}</td>
            <td>{{ $p->numero_cuenta ?? 'N/A' }}</td>
            <td>{{ number_format($p->importe, 2) }}</td>
            <td>{{ $p->moneda }}</td>
            <td>{{ $p->periodo }}</td>
            <td>{{ \Carbon\Carbon::parse($p->created_at)->format('d/m/Y') }}</td>
          </tr>
        @endforeach
        </tbody>
        <tfoot>
          <tr>
            <th colspan="2">Total</th>
            <th>{{ number_format($pagos->sum('importe'), 2) }}</th>
            <th colspan="3"></th>
          </tr>
        </tfoot>
      </table>
    </div>
  @else
    <p>No hay pagos aceptados en el rango seleccionado.</p>
  @endif
</div>
</body>
</html>
