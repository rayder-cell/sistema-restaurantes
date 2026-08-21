@extends('layouts.app')

@section('title', 'Kardex — ' . $insumo->nombre)
@section('page-title', 'Kardex de Insumo')

@section('content')

<div class="page-toolbar">
    <a href="{{ route('insumos.index') }}" class="btn-back">← Volver</a>
</div>

{{-- Info del insumo --}}
<div class="form-card mb-6 p-6">
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
        <div>
            <p class="text-gray-400 text-xs uppercase tracking-wide">Insumo</p>
            <p class="font-semibold text-gray-800 mt-0.5">{{ $insumo->nombre }}</p>
        </div>
        <div>
            <p class="text-gray-400 text-xs uppercase tracking-wide">Unidad</p>
            <p class="font-medium text-gray-800 mt-0.5">{{ $insumo->unidad_medida }}</p>
        </div>
        <div>
            <p class="text-gray-400 text-xs uppercase tracking-wide">Stock actual</p>
            <p class="font-bold mt-0.5 {{ $insumo->stock_actual <= $insumo->stock_minimo ? 'text-red-600' : 'text-green-600' }}">
                {{ number_format($insumo->stock_actual, 2) }} {{ $insumo->unidad_medida }}
            </p>
        </div>
        <div>
            <p class="text-gray-400 text-xs uppercase tracking-wide">Stock mínimo</p>
            <p class="font-medium text-gray-800 mt-0.5">{{ number_format($insumo->stock_minimo, 2) }}</p>
        </div>
    </div>
</div>

{{-- Tabla kardex --}}
<div class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Referencia</th>
                <th>Entrada</th>
                <th>Salida</th>
                <th>Saldo</th>
                <th>Costo unit.</th>
                <th>Costo total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movimientos as $mov)
            <tr>
                <td class="td-secondary">{{ \Carbon\Carbon::parse($mov->fecha)->format('d/m/Y') }}</td>
                <td>
                    <span class="badge {{ $mov->tipo_movimiento === 'entrada' ? 'badge-green' : 'badge-red' }}">
                        {{ ucfirst($mov->tipo_movimiento) }}
                    </span>
                </td>
                <td class="td-secondary">{{ $mov->referencia_doc ?? '—' }}</td>
                <td class="text-green-600 font-medium">
                    {{ $mov->cantidad_entrada > 0 ? '+' . number_format($mov->cantidad_entrada, 2) : '—' }}
                </td>
                <td class="text-red-600 font-medium">
                    {{ $mov->cantidad_salida > 0 ? '-' . number_format($mov->cantidad_salida, 2) : '—' }}
                </td>
                <td class="font-semibold text-gray-800">{{ number_format($mov->saldo_resultante, 2) }}</td>
                <td class="td-secondary">S/ {{ number_format($mov->costo_unitario, 2) }}</td>
                <td class="td-secondary">S/ {{ number_format($mov->costo_total, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="td-empty">No hay movimientos registrados para este insumo.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="pagination-wrapper">{{ $movimientos->links() }}</div>
</div>

@endsection
