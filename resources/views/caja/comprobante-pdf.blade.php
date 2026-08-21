<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ ucfirst($comprobante->tipo) }} {{ $serie->serie }}-{{ str_pad($comprobante->numero_correlativo, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        @include('caja.comprobante-pdf-styles')
    </style>
</head>
<body>

    {{-- Encabezado --}}
    <div class="centrado negrita nombre-restaurante">{{ strtoupper($restaurante->nombre) }}</div>
    <div class="centrado">
        @if(!empty($restaurante->direccion)){{ $restaurante->direccion }}<br>@endif
        @if(!empty($restaurante->telefono))Telf: {{ $restaurante->telefono }}<br>@endif
        @if(!empty($restaurante->ruc))RUC: {{ $restaurante->ruc }}@endif
    </div>

    <div class="centrado negrita">{{ strtoupper($comprobante->tipo) }} DE VENTA ELECTRÓNICA</div>
    <div class="centrado">F. PAGO: {{ strtoupper($metodoPago->metodo) }}</div>
    <div class="centrado">{{ $serie->serie }}-{{ str_pad($comprobante->numero_correlativo, 6, '0', STR_PAD_LEFT) }}</div>
    <div class="centrado">{{ \Carbon\Carbon::parse($comprobante->emitido_at)->format('d/m/Y') }} {{ \Carbon\Carbon::parse($comprobante->emitido_at)->format('h:i A') }}</div>

    <div class="separador">-------------------------------------------------</div>

    {{-- Datos del cliente --}}
    <div class="centrado">
        @if(!empty($comprobante->cliente_ruc))
            RUC/DNI: {{ $comprobante->cliente_ruc }}<br>
        @endif
        {{ $comprobante->cliente_nombre ?? 'Cliente' }}
        @if(!empty($mesa->mesa_numero))
            <br>Mesa: {{ str_pad($mesa->mesa_numero, 2, '0', STR_PAD_LEFT) }}
        @endif
    </div>

    <div class="separador">-------------------------------------------------</div>

    {{-- Detalle de productos --}}
    <table class="detalle">
        <thead>
            <tr>
                <th class="izq">CANT.</th>
                <th class="der">P. UNIT.</th>
                <th class="der">P. TOT.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detalles as $item)
            <tr>
                <td colspan="3" class="nombre-producto">{{ $item->producto_nombre }}</td>
            </tr>
            <tr>
                <td class="izq">{{ $item->cantidad }} UND</td>
                <td class="der">S/ {{ number_format($item->precio_unitario, 2) }}</td>
                <td class="der">S/ {{ number_format($item->cantidad * $item->precio_unitario, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="separador">-------------------------------------------------</div>

    {{-- Totales --}}
    <table class="totales">
        <tr>
            <td class="izq">Subtotal</td>
            <td class="der">S/ {{ number_format($comprobante->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td class="izq">IGV (18%)</td>
            <td class="der">S/ {{ number_format($comprobante->igv, 2) }}</td>
        </tr>
        <tr class="total">
            <td class="izq negrita">Total</td>
            <td class="der negrita">S/ {{ number_format($comprobante->total, 2) }}</td>
        </tr>
        <tr>
            <td class="izq">Pago</td>
            <td class="der">S/ {{ number_format($metodoPago->monto, 2) }}</td>
        </tr>
        <tr>
            <td class="izq">Vuelto</td>
            <td class="der">S/ {{ number_format($metodoPago->vuelto, 2) }}</td>
        </tr>
    </table>

    <div class="separador">-------------------------------------------------</div>

    <div class="centrado">SON: {{ strtoupper(\App\Helpers\NumeroALetras::convertir($comprobante->total)) }} SOLES</div>

    <div class="separador">-------------------------------------------------</div>

    <div class="centrado">{{ strtoupper($metodoPago->metodo) }} S/ {{ number_format($metodoPago->monto, 2) }}</div>

    <div class="separador">-------------------------------------------------</div>

    {{-- Pie de página --}}
    <div class="centrado footer">
        Gracias por su visita<br>
        {{ strtoupper($restaurante->nombre) }}
    </div>

</body>
</html>