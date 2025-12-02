<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recibo #{{ $recibo->id }}</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; margin: 24px; }
        h1 { font-size: 18px; margin: 0 0 8px; }
        h2 { font-size: 14px; margin: 16px 0 8px; }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 6px 8px; border: 1px solid #ccc; font-size: 12px; }
        .right { text-align: right; }
        .muted { color: #666; }
    </style>
</head>
<body>
    <h1>Recibo de nómina</h1>
    <p class="muted">Periodo: {{ $recibo->periodo->codigo }} ({{ $recibo->periodo->fecha_inicio }} a {{ $recibo->periodo->fecha_fin }})</p>

    <h2>Empleado</h2>
    <table>
        <tr><th>Empleado</th><td>{{ $recibo->empleado->nombre }} {{ $recibo->empleado->apellido }}</td></tr>
        <tr><th># Empleado</th><td>{{ $recibo->empleado->numero_empleado }}</td></tr>
        <tr><th>Identificador fiscal</th><td>{{ $recibo->empleado->identificador_fiscal }}</td></tr>
        <tr><th>Departamento</th><td>{{ optional($recibo->empleado->department)->name ?? 'N/A' }}</td></tr>
    </table>

    <h2>Detalle</h2>
    <table>
        <tr><th>Concepto</th><th class="right">Monto</th></tr>
        <tr><td>Salario bruto</td><td class="right">{{ number_format($recibo->bruto, 2) }}</td></tr>
        <tr><td>Deducciones - Impuesto</td><td class="right">{{ number_format($recibo->detalle_deducciones['impuesto'] ?? 0, 2) }}</td></tr>
        <tr><td>Deducciones - Seguridad social</td><td class="right">{{ number_format($recibo->detalle_deducciones['seguridad_social'] ?? 0, 2) }}</td></tr>
        <tr><th>Total deducciones</th><th class="right">{{ number_format($recibo->deducciones, 2) }}</th></tr>
        <tr><th>Neto a pagar</th><th class="right">{{ number_format($recibo->neto, 2) }}</th></tr>
    </table>

    <p class="muted">Estado: {{ $recibo->estado }} {{ $recibo->locked_at ? '(aprobado)' : '' }}</p>
</body>
</html>
