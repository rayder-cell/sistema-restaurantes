@extends('layouts.app')

@section('title', 'Dashboard — MIKHUNAWASI')
@section('page-title', 'Dashboard')

@section('content')

    {{-- Estadísticas --}}
    <div class="stats-grid">

        <div class="stat-card">
            <div class="stat-icon red"><i class="fa-solid fa-clipboard-list" style="color:#111827;"></i></div>
            <div>
                <p class="stat-label">Pedidos hoy</p>
                <p class="stat-value">{{ $pedidosHoy ?? 0 }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green"><i class="fa-solid fa-sack-dollar" style="color:#111827;"></i></div>
            <div>
                <p class="stat-label">Ventas hoy</p>
                <p class="stat-value">S/ {{ number_format($ventasHoy ?? 0, 2) }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon blue"><i class="fa-solid fa-chair" style="color:#111827;"></i></div>
            <div>
                <p class="stat-label">Mesas ocupadas</p>
                <p class="stat-value">{{ $mesasOcupadas ?? 0 }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon yellow"><i class="fa-solid fa-calendar-days" style="color:#111827;"></i></div>
            <div>
                <p class="stat-label">Reservas hoy</p>
                <p class="stat-value">{{ $reservasHoy ?? 0 }}</p>
            </div>
        </div>

    </div>

    {{-- Paneles --}}
    <div class="content-grid">

        {{-- Pedidos activos --}}
        <div class="panel">
            <div class="panel-header">
                <h3 class="panel-title">Pedidos activos</h3>
                <a href="{{ route('pedidos.index') }}" class="panel-link">Ver todos</a>
            </div>

            @if (isset($pedidosActivos) && $pedidosActivos->count() > 0)
                <div class="space-y-3">
                    @foreach ($pedidosActivos as $pedido)
                        <div class="list-item">
                            <div>
                                <p class="list-item-title">Mesa {{ $pedido->mesa?->numero ?? 'N/A' }}</p>
                                <p class="list-item-sub">{{ $pedido->created_at->diffForHumans() }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-semibold text-gray-800">
                                    S/ {{ number_format($pedido->total, 2) }}
                                </span>
                                <span
                                    class="badge
                            @if ($pedido->estado === 'pendiente') badge-yellow
                            @elseif($pedido->estado === 'en_cocina') badge-blue
                            @elseif($pedido->estado === 'listo') badge-green
                            @else badge-gray @endif">
                                    {{ ucfirst(str_replace('_', ' ', $pedido->estado)) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <p class="empty-icon"><i class="fa-solid fa-clipboard-list" style="color:#111827;"></i></p>
                    <p>No hay pedidos activos</p>
                </div>
            @endif
        </div>

        {{-- Reservas del día --}}
        <div class="panel">
            <div class="panel-header">
                <h3 class="panel-title">Reservas de hoy</h3>
                <a href="{{ route('reservas.index') }}" class="panel-link">Ver todas</a>
            </div>

            @if (isset($reservasDelDia) && $reservasDelDia->count() > 0)
                <div class="space-y-3">
                    @foreach ($reservasDelDia as $reserva)
                        <div class="list-item">
                            <div>
                                <p class="list-item-title">{{ $reserva->cliente_nombre }}</p>
                                <p class="list-item-sub">
                                    {{ \Carbon\Carbon::parse($reserva->hora)->format('H:i') }}
                                    — {{ $reserva->num_personas }} personas
                                </p>
                            </div>
                            <span
                                class="badge
                        @if ($reserva->estado === 'confirmada') badge-green
                        @elseif($reserva->estado === 'pendiente') badge-yellow
                        @else badge-red @endif">
                                {{ ucfirst($reserva->estado) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <p class="empty-icon"><i class="fa-solid fa-calendar-xmark" style="color:#111827;"></i></p>
                    <p>No hay reservas para hoy</p>
                </div>
            @endif
        </div>

    </div>

@endsection
