@extends('layouts.app')

@section('title', 'Pedido #{{ $pedido->id }} — MIKHUNAWASI')
@section('page-title', 'Detalle de Pedido')

@section('content')

<div class="ped-header">
    <div>
        <h2 class="ped-title">Pedido #{{ $pedido->id }}</h2>
        <p class="ped-sub">Mesa {{ $pedido->mesa?->numero }} — {{ $pedido->created_at->format('d/m/Y H:i') }}</p>
    </div>
    <a href="{{ route('pedidos.index') }}" class="menu-btn-secondary">← Volver</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Detalles del pedido --}}
    <div class="lg:col-span-2 panel">
        <div class="panel-header">
            <h3 class="panel-title">Productos</h3>
            <span class="badge
                @if($pedido->estado === 'pendiente') badge-yellow
                @elseif($pedido->estado === 'en_preparacion') badge-blue
                @elseif($pedido->estado === 'listo') badge-green
                @elseif($pedido->estado === 'entregado') badge-gray
                @else badge-red @endif">
                {{ ucfirst(str_replace('_', ' ', $pedido->estado)) }}
            </span>
        </div>

        @foreach($pedido->detalles as $det)
        <div class="list-item">
            <div class="flex items-center gap-3">
                @if($det->producto?->imagen_url)
                    <img src="{{ $det->producto->imagen_url }}" class="w-12 h-12 rounded-lg object-cover">
                @else
                    <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center">🍽</div>
                @endif
                <div>
                    <p class="list-item-title">{{ $det->producto?->nombre }}</p>
                    <p class="list-item-sub">{{ $det->cantidad }} x S/ {{ number_format($det->precio_unitario, 2) }}</p>
                    @if($det->observacion)
                        <p class="text-xs text-orange-500 mt-0.5">⚠ {{ $det->observacion }}</p>
                    @endif
                </div>
            </div>
            <p class="font-semibold text-gray-800">S/ {{ number_format($det->subtotal, 2) }}</p>
        </div>
        @endforeach

        @if($pedido->observacion)
        <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-3 text-sm text-yellow-700">
            <strong>Observación:</strong> {{ $pedido->observacion }}
        </div>
        @endif
    </div>

    {{-- Resumen y acciones --}}
    <div class="space-y-4">
        <div class="panel">
            <h3 class="panel-title mb-4">Resumen</h3>
            @php
                $subtotal = $pedido->detalles->sum('subtotal');
                $igv      = $subtotal * 0.18;
                $total    = $subtotal + $igv;
            @endphp
            <div class="space-y-2 text-sm">
                <div class="flex justify-between text-gray-500">
                    <span>Subtotal</span><span>S/ {{ number_format($subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-gray-500">
                    <span>IGV (18%)</span><span>S/ {{ number_format($igv, 2) }}</span>
                </div>
                <div class="flex justify-between font-bold text-lg pt-2 border-t border-gray-100">
                    <span>Total</span>
                    <span class="text-purple-700">S/ {{ number_format($total, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Cambiar estado --}}
        @if(!in_array($pedido->estado, ['entregado', 'cancelado']))
        <div class="panel">
            <h3 class="panel-title mb-3">Cambiar Estado</h3>
            <form method="POST" action="{{ route('pedidos.estado', $pedido->id) }}">
                @csrf
                @method('PATCH')
                <select name="estado" class="menu-form-input mb-3">
                    <option value="pendiente"       {{ $pedido->estado === 'pendiente'       ? 'selected' : '' }}>Pendiente</option>
                    <option value="en_preparacion"  {{ $pedido->estado === 'en_preparacion'  ? 'selected' : '' }}>En Preparación</option>
                    <option value="listo"           {{ $pedido->estado === 'listo'           ? 'selected' : '' }}>Listo</option>
                    <option value="entregado"       {{ $pedido->estado === 'entregado'       ? 'selected' : '' }}>Entregado</option>
                </select>
                <button type="submit" class="menu-btn-guardar w-full">Actualizar Estado</button>
            </form>
        </div>

        <form method="POST" action="{{ route('pedidos.destroy', $pedido->id) }}"
              onsubmit="return confirm('¿Cancelar este pedido?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="w-full text-sm text-red-500 border border-red-300 rounded-lg py-2 hover:bg-red-50 transition-colors">
                Cancelar Pedido
            </button>
        </form>
        @endif
    </div>

</div>

@endsection
