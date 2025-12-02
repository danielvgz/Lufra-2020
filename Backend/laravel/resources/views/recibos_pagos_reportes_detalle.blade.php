<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reporte detallado de recibos y pagos</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <style>
    @media print {
      .no-print { display: none !important; }
      table { font-size: 11px; }
    }
  </style>
</head>
<body>
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
            <td>{{ $r->estado ?? 'pendiente' }}</td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  @else
    <p>No hay datos para el rango seleccionado.</p>
  @endif
</div>
</body>
</html>
