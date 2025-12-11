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
        <tr><td>Devengado</td><td class="right">{{ number_format($recibo->devengado ?? $recibo->bruto, 2) }}</td></tr>
        @if($recibo->impuesto_monto > 0 || $recibo->impuesto_id)
            @php
                $impuesto = $recibo->impuesto_id ? \Illuminate\Support\Facades\DB::table('impuestos')->where('id', $recibo->impuesto_id)->first() : null;
            @endphp
            <tr>
                <td>Impuesto {{ $impuesto ? $impuesto->nombre . ' (' . number_format($impuesto->porcentaje, 2) . '%)' : '' }}</td>
                <td class="right">-{{ number_format($recibo->impuesto_monto ?? 0, 2) }}</td>
            </tr>
        @endif
        @if($recibo->deducciones > 0 && $recibo->deducciones != $recibo->impuesto_monto)
            <tr><td>Otras deducciones</td><td class="right">-{{ number_format($recibo->deducciones - ($recibo->impuesto_monto ?? 0), 2) }}</td></tr>
        @endif
        <tr><th>Neto a pagar</th><th class="right">{{ number_format($recibo->neto, 2) }}</th></tr>
    </table>

    @php
        $pagosFinalizados = $recibo->pagos->whereIn('estado', ['aceptado', 'rechazado']);
    @endphp
    
    @if($pagosFinalizados->count() > 0)
        <h2>Pagos</h2>
        <table>
            <tr><th>Fecha</th><th>Método</th><th>Moneda</th><th>Referencia</th><th class="right">Importe</th></tr>
            @foreach($pagosFinalizados as $pago)
                <?php 
                    $monedaInfo = null;
                    if ($pago->moneda) {
                        $monedaInfo = \Illuminate\Support\Facades\DB::table('monedas')->where('codigo', $pago->moneda)->first();
                    }
                ?>
                <tr>
                    <td>{{ $pago->pagado_at ? $pago->pagado_at->format('d/m/Y') : $pago->created_at->format('d/m/Y') }}</td>
                    <td>{{ $pago->metodo }}</td>
                    <td>{{ $monedaInfo ? $monedaInfo->simbolo . ' ' . $monedaInfo->codigo : ($pago->moneda ?? 'N/A') }}</td>
                    <td>{{ $pago->referencia }}</td>
                    <td class="right">{{ $monedaInfo ? $monedaInfo->simbolo : '' }} {{ number_format($pago->importe, 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <th colspan="4" class="right">Total pagos:</th>
                <th class="right">{{ number_format($pagosFinalizados->sum('importe'), 2) }}</th>
            </tr>
        </table>
    @endif
</body>
</html>
