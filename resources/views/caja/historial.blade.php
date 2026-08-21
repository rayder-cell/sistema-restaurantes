@extends('layouts.app')

@section('title', 'Historial de Comprobantes — MIKHUNAWASI')
@section('page-title', 'Historial de Comprobantes')

@section('content')

<div class="page-toolbar">
    <form method="GET" action="{{ route('caja.historial') }}" class="reserva-filtros">
        <input type="date" name="fecha" value="{{ request('fecha', now()->toDateString()) }}"
            class="form-input w-auto">
        <select name="tipo" class="form-input w-auto">
            <option value="">Todos</option>
            <option value="boleta"  {{ request('tipo') === 'boleta'  ? 'selected' : '' }}>Boleta</option>
            <option value="factura" {{ request('tipo') === 'factura' ? 'selected' : '' }}>Factura</option>
        </select>
        <button type="submit" class="btn-submit px-4 py-2">Filtrar</button>
    </form>
    <div class="flex items-center gap-4">
        <span class="text-sm text-gray-500">Total del día: <strong class="text-gray-800">S/ {{ number_format($totalDia, 2) }}</strong></span>
        <a href="{{ route('caja.index') }}" class="btn-back">← Volver a caja</a>
    </div>
</div>

<div class="table-card mt-4">
    <table class="data-table">
        <thead>
            <tr>
                <th>Comprobante</th>
                <th>Mesa</th>
                <th>Cliente</th>
                <th>Subtotal</th>
                <th>IGV</th>
                <th>Total</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($comprobantes as $c)
            <tr>
                <td>
                    <p class="font-medium text-gray-800">{{ strtoupper($c->tipo) }}</p>
                    <p class="text-xs text-gray-400">{{ $c->serie }}-{{ str_pad($c->numero_correlativo, 8, '0', STR_PAD_LEFT) }}</p>
                </td>
                <td class="td-secondary">{{ $c->mesa_numero ? 'Mesa ' . $c->mesa_numero : '—' }}</td>
                <td class="td-secondary">{{ $c->cliente_nombre ?? '—' }}</td>
                <td class="td-secondary">S/ {{ number_format($c->subtotal, 2) }}</td>
                <td class="td-secondary">S/ {{ number_format($c->igv, 2) }}</td>
                <td class="font-semibold text-gray-800">S/ {{ number_format($c->total, 2) }}</td>
                <td class="td-secondary">{{ \Carbon\Carbon::parse($c->emitido_at)->format('d/m/Y H:i') }}</td>
                <td>
                    @if($c->anulado)
                    <span class="badge badge-red">Anulado</span>
                    @else
                    <span class="badge badge-green">Válido</span>
                    @endif
                </td>
                <td>
                    @if(!$c->anulado)
                    <form method="POST" action="{{ route('caja.anular', $c->id) }}"
                        onsubmit="return confirm('¿Anular este comprobante?')">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn-delete">Anular</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="td-empty">No hay comprobantes para esta fecha.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="pagination-wrapper">{{ $comprobantes->links() }}</div>
</div>

@endsection
