@extends('layouts.app')

@section('title', 'Reportes — MIKHUNAWASI')
@section('page-title', 'Reportes y Estadísticas')

@section('content')

{{-- Filtro de fechas --}}
<div class="page-toolbar mb-6">
    <form method="GET" action="{{ route('reportes.ventas') }}" class="reserva-filtros">
        <div class="flex items-center gap-2">
            <label class="text-sm text-gray-500">Desde:</label>
            <input type="date" name="desde" value="{{ $desde }}" class="form-input w-auto">
        </div>
        <div class="flex items-center gap-2">
            <label class="text-sm text-gray-500">Hasta:</label>
            <input type="date" name="hasta" value="{{ $hasta }}" class="form-input w-auto">
        </div>
        <button type="submit" class="btn-submit px-4 py-2">Aplicar</button>
        <a href="{{ route('reportes.ventas') }}" class="btn-cancel">Este mes</a>
    </form>
    <p class="text-sm text-gray-400">{{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}</p>
</div>

{{-- Indicadores del día --}}
<div class="reporte-section-title">📊 Indicadores de hoy</div>
<div class="reserva-stats-grid mb-6">
    <div class="reserva-stat-card">
        <p class="reserva-stat-label">Pedidos hoy</p>
        <p class="reserva-stat-value">{{ $indicadoresHoy->total_pedidos ?? 0 }}</p>
    </div>
    <div class="reserva-stat-card green">
        <p class="reserva-stat-label">Pagados</p>
        <p class="reserva-stat-value">{{ $indicadoresHoy->pedidos_pagados ?? 0 }}</p>
    </div>
    <div class="reserva-stat-card yellow">
        <p class="reserva-stat-label">Pendientes</p>
        <p class="reserva-stat-value">{{ $indicadoresHoy->pedidos_pendientes ?? 0 }}</p>
    </div>
    <div class="reserva-stat-card">
        <p class="reserva-stat-label">Ticket promedio</p>
        <p class="reserva-stat-value">S/ {{ number_format($indicadoresHoy->ticket_promedio ?? 0, 2) }}</p>
    </div>
</div>

{{-- Resumen del período --}}
<div class="reporte-section-title">💰 Resumen del período</div>
<div class="reporte-resumen-grid mb-6">
    <div class="reporte-resumen-card purple">
        <p class="reporte-resumen-label">Total ventas</p>
        <p class="reporte-resumen-valor">S/ {{ number_format($resumen->total_ventas ?? 0, 2) }}</p>
    </div>
    <div class="reporte-resumen-card">
        <p class="reporte-resumen-label">Subtotal</p>
        <p class="reporte-resumen-valor">S/ {{ number_format($resumen->total_subtotal ?? 0, 2) }}</p>
    </div>
    <div class="reporte-resumen-card">
        <p class="reporte-resumen-label">IGV (18%)</p>
        <p class="reporte-resumen-valor">S/ {{ number_format($resumen->total_igv ?? 0, 2) }}</p>
    </div>
    <div class="reporte-resumen-card">
        <p class="reporte-resumen-label">Comprobantes</p>
        <p class="reporte-resumen-valor">{{ $resumen->total_comprobantes ?? 0 }}</p>
    </div>
    <div class="reporte-resumen-card">
        <p class="reporte-resumen-label">Ticket promedio</p>
        <p class="reporte-resumen-valor">S/ {{ number_format($resumen->ticket_promedio ?? 0, 2) }}</p>
    </div>
</div>

{{-- Grid principal --}}
<div class="reporte-main-grid">

    {{-- Productos más vendidos --}}
    <div class="reporte-panel">
        <h3 class="reporte-panel-title">🏆 Productos más vendidos</h3>
        @if($productosMasVendidos->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Producto</th>
                    <th>Vendidos</th>
                    <th>Ingresos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productosMasVendidos as $i => $prod)
                <tr>
                    <td>
                        <span class="reporte-rank {{ $i < 3 ? 'top' : '' }}">{{ $i + 1 }}</span>
                    </td>
                    <td class="font-medium text-gray-800">{{ $prod->nombre }}</td>
                    <td class="td-secondary">{{ $prod->total_vendido }}</td>
                    <td class="font-semibold text-purple-700">S/ {{ number_format($prod->total_ingresos, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="empty-state"><p class="empty-icon">📊</p><p>Sin datos en este período</p></div>
        @endif
    </div>

    {{-- Ventas por método de pago --}}
    <div class="reporte-panel">
        <h3 class="reporte-panel-title">💳 Ventas por método de pago</h3>
        @if($ventasPorMetodo->count() > 0)
        <div class="space-y-3 p-4">
            @php $totalVentas = $ventasPorMetodo->sum('total'); @endphp
            @foreach($ventasPorMetodo as $metodo)
            @php $porcentaje = $totalVentas > 0 ? round(($metodo->total / $totalVentas) * 100) : 0; @endphp
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="font-medium text-gray-700">
                        {{ $metodo->metodo === 'efectivo' ? '💵' : ($metodo->metodo === 'tarjeta' ? '💳' : '📱') }}
                        {{ ucfirst($metodo->metodo) }}
                    </span>
                    <span class="text-gray-500">S/ {{ number_format($metodo->total, 2) }} ({{ $porcentaje }}%)</span>
                </div>
                <div class="reporte-barra-bg">
                    <div class="reporte-barra-fill" style="width: {{ $porcentaje }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="empty-state"><p class="empty-icon">💳</p><p>Sin datos en este período</p></div>
        @endif
    </div>

    {{-- Ventas por categoría --}}
    <div class="reporte-panel">
        <h3 class="reporte-panel-title">📂 Ventas por categoría</h3>
        @if($ventasPorCategoria->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>Categoría</th>
                    <th>Items</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ventasPorCategoria as $cat)
                <tr>
                    <td class="font-medium text-gray-800">{{ $cat->categoria }}</td>
                    <td class="td-secondary">{{ $cat->total_items }}</td>
                    <td class="font-semibold text-purple-700">S/ {{ number_format($cat->total_ventas, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="empty-state"><p class="empty-icon">📂</p><p>Sin datos en este período</p></div>
        @endif
    </div>

    {{-- Pedidos por estado --}}
    <div class="reporte-panel">
        <h3 class="reporte-panel-title">📋 Pedidos por estado</h3>
        @if($pedidosPorEstado->count() > 0)
        <div class="space-y-3 p-4">
            @php $totalPedidos = $pedidosPorEstado->sum('cantidad'); @endphp
            @foreach($pedidosPorEstado as $estado)
            @php $pct = $totalPedidos > 0 ? round(($estado->cantidad / $totalPedidos) * 100) : 0; @endphp
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="reserva-estado-badge estado-{{ $estado->estado }}">{{ ucfirst(str_replace('_', ' ', $estado->estado)) }}</span>
                </div>
                <span class="font-semibold text-gray-700">{{ $estado->cantidad }} <span class="text-gray-400 font-normal text-xs">({{ $pct }}%)</span></span>
            </div>
            @endforeach
        </div>
        @else
        <div class="empty-state"><p class="empty-icon">📋</p><p>Sin datos en este período</p></div>
        @endif
    </div>

</div>

{{-- Ventas por día --}}
@if($ventasPorDia->count() > 0)
<div class="reporte-panel mt-6">
    <h3 class="reporte-panel-title">📅 Ventas por día</h3>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Comprobantes</th>
                    <th>Total ventas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ventasPorDia as $dia)
                <tr>
                    <td class="font-medium text-gray-800">
                        {{ \Carbon\Carbon::parse($dia->fecha)->format('d/m/Y') }}
                        <span class="text-xs text-gray-400 ml-1">{{ \Carbon\Carbon::parse($dia->fecha)->translatedFormat('l') }}</span>
                    </td>
                    <td class="td-secondary">{{ $dia->total_comprobantes }}</td>
                    <td class="font-semibold text-purple-700">S/ {{ number_format($dia->total_ventas, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
