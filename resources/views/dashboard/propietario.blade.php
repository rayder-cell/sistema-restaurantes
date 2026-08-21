@extends('layouts.app')

@section('title', 'Dashboard Propietario — MIKHUNAWASI')
@section('page-title', 'Dashboard')

@section('content')

{{-- Métricas de ventas --}}
<div class="stats-grid">

    <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-sack-dollar" style="color:#111827;"></i></div>
        <div>
            <p class="stat-label">Ventas hoy</p>
            <p class="stat-value">S/ {{ number_format($ventasHoy, 2) }}</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon blue"><i class="fa-solid fa-chart-column" style="color:#111827;"></i></div>
        <div>
            <p class="stat-label">Ventas esta semana</p>
            <p class="stat-value">S/ {{ number_format($ventasSemana, 2) }}</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon purple"><i class="fa-solid fa-chart-line" style="color:#111827;"></i></div>
        <div>
            <p class="stat-label">Ventas este mes</p>
            <p class="stat-value">S/ {{ number_format($ventasMes, 2) }}</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon yellow"><i class="fa-solid fa-calendar-days" style="color:#111827;"></i></div>
        <div>
            <p class="stat-label">Reservas hoy</p>
            <p class="stat-value">{{ $reservasHoy }}</p>
        </div>
    </div>

</div>

{{-- Paneles --}}
<div class="content-grid">

    {{-- Resumen rápido --}}
    <div class="panel">
        <div class="panel-header">
            <h3 class="panel-title">Resumen del día</h3>
            <span class="text-xs text-gray-400">{{ now()->format('d/m/Y') }}</span>
        </div>
        <div class="space-y-4">
            <div class="list-item">
                <div class="flex items-center gap-3">
                    <span class="text-xl"><i class="fa-solid fa-cart-shopping" style="color:#111827;"></i></span>
                    <p class="list-item-title">Pedidos atendidos hoy</p>
                </div>
                <span class="text-lg font-bold text-gray-800">{{ $pedidosHoy }}</span>
            </div>
            <div class="list-item">
                <div class="flex items-center gap-3">
                    <span class="text-xl"><i class="fa-solid fa-money-bill-wave" style="color:#111827;"></i></span>
                    <p class="list-item-title">Ingresos del día</p>
                </div>
                <span class="text-lg font-bold text-green-600">S/ {{ number_format($ventasHoy, 2) }}</span>
            </div>
            <div class="list-item">
                <div class="flex items-center gap-3">
                    <span class="text-xl"><i class="fa-solid fa-calendar-check" style="color:#111827;"></i></span>
                    <p class="list-item-title">Reservas confirmadas hoy</p>
                </div>
                <span class="text-lg font-bold text-gray-800">{{ $reservasHoy }}</span>
            </div>
            <div class="list-item">
                <div class="flex items-center gap-3">
                    <span class="text-xl"><i class="fa-solid fa-money-bill-trend-up" style="color:#111827;"></i></span>
                    <p class="list-item-title">Ingresos del mes</p>
                </div>
                <span class="text-lg font-bold text-purple-600">S/ {{ number_format($ventasMes, 2) }}</span>
            </div>
        </div>
    </div>

    {{-- Reservas del día --}}
    <div class="panel">
        <div class="panel-header">
            <h3 class="panel-title">Reservas de hoy</h3>
            <a href="{{ route('reservas.index') }}" class="panel-link">Ver todas</a>
        </div>

        @if($reservasDelDia->count() > 0)
            <div class="space-y-3">
                @foreach($reservasDelDia as $reserva)
                <div class="list-item">
                    <div>
                        <p class="list-item-title">{{ $reserva->cliente_nombre }}</p>
                        <p class="list-item-sub">
                            {{ \Carbon\Carbon::parse($reserva->hora)->format('H:i') }}
                            — {{ $reserva->num_personas }} personas
                        </p>
                    </div>
                    <span class="badge
                        @if($reserva->estado === 'confirmada') badge-green
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

{{-- Accesos rápidos para propietario --}}
<div class="mt-6">
    <h3 class="text-gray-600 text-sm font-semibold mb-3 uppercase tracking-wider">Accesos rápidos</h3>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">

        <a href="{{ route('usuarios.index') }}" class="stat-card hover:border-purple-300 transition-colors cursor-pointer no-underline">
            <div class="stat-icon purple"><i class="fa-solid fa-users" style="color:#111827;"></i></div>
            <div>
                <p class="stat-label">Usuarios</p>
                <p class="text-sm text-gray-600 font-medium">Mi equipo</p>
            </div>
        </a>

        <a href="{{ route('insumos.index') }}" class="stat-card hover:border-purple-300 transition-colors cursor-pointer no-underline">
            <div class="stat-icon green"><i class="fa-solid fa-box" style="color:#111827;"></i></div>
            <div>
                <p class="stat-label">Inventario</p>
                <p class="text-sm text-gray-600 font-medium">Ver stock</p>
            </div>
        </a>

        <a href="{{ route('proveedores.index') }}" class="stat-card hover:border-purple-300 transition-colors cursor-pointer no-underline">
            <div class="stat-icon blue"><i class="fa-solid fa-truck" style="color:#111827;"></i></div>
            <div>
                <p class="stat-label">Proveedores</p>
                <p class="text-sm text-gray-600 font-medium">Ver lista</p>
            </div>
        </a>

        <a href="{{ route('reportes.ventas') }}" class="stat-card hover:border-purple-300 transition-colors cursor-pointer no-underline">
            <div class="stat-icon yellow"><i class="fa-solid fa-chart-simple" style="color:#111827;"></i></div>
            <div>
                <p class="stat-label">Reportes</p>
                <p class="text-sm text-gray-600 font-medium">Ver ventas</p>
            </div>
        </a>

    </div>
</div>

@endsection