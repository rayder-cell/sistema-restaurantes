@extends('layouts.app')

@section('title', 'Detalle reserva — MIKHUNAWASI')
@section('page-title', 'Detalle de reserva')

@section('content')

<div class="page-toolbar">
    <a href="{{ route('reservas.index') }}" class="btn-back">← Volver</a>
    <div class="flex gap-2">
        <a href="{{ route('reservas.edit', $reserva->id) }}" class="btn-create">✏️ Editar</a>
    </div>
</div>

<div class="form-card max-w-2xl">

    {{-- Estado --}}
    <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100">
        <h3 class="font-semibold text-gray-700">Estado de la reserva</h3>
        <span class="reserva-estado-badge estado-{{ $reserva->estado }} text-sm px-4 py-1.5">
            {{ ucfirst($reserva->estado) }}
        </span>
    </div>

    {{-- Acciones rápidas --}}
    @if($reserva->estado === 'pendiente')
    <div class="flex gap-3 mb-6">
        <form method="POST" action="{{ route('reservas.estado', $reserva->id) }}">
            @csrf @method('PATCH')
            <input type="hidden" name="estado" value="confirmada">
            <button type="submit" class="btn-submit">✅ Confirmar reserva</button>
        </form>
        <form method="POST" action="{{ route('reservas.estado', $reserva->id) }}">
            @csrf @method('PATCH')
            <input type="hidden" name="estado" value="cancelada">
            <button type="submit" class="btn-delete px-4 py-2 rounded-lg"
                onclick="return confirm('¿Cancelar esta reserva?')">❌ Cancelar</button>
        </form>
    </div>
    @endif

    @if($reserva->estado === 'confirmada')
    <div class="flex gap-3 mb-6">
        <form method="POST" action="{{ route('reservas.estado', $reserva->id) }}">
            @csrf @method('PATCH')
            <input type="hidden" name="estado" value="completada">
            <button type="submit" class="btn-submit">🎉 Marcar completada</button>
        </form>
    </div>
    @endif

    {{-- Info --}}
    <div class="grid grid-cols-2 gap-4 text-sm">
        <div>
            <p class="text-gray-400 text-xs uppercase tracking-wide">Cliente</p>
            <p class="font-semibold text-gray-800 mt-0.5">{{ $reserva->cliente_nombre }}</p>
        </div>
        <div>
            <p class="text-gray-400 text-xs uppercase tracking-wide">Teléfono</p>
            <p class="font-medium text-gray-800 mt-0.5">{{ $reserva->cliente_telefono }}</p>
        </div>
        @if($reserva->cliente_email)
        <div class="col-span-2">
            <p class="text-gray-400 text-xs uppercase tracking-wide">Email</p>
            <p class="font-medium text-gray-800 mt-0.5">{{ $reserva->cliente_email }}</p>
        </div>
        @endif
        <div>
            <p class="text-gray-400 text-xs uppercase tracking-wide">Fecha</p>
            <p class="font-medium text-gray-800 mt-0.5">
                {{ \Carbon\Carbon::parse($reserva->fecha)->format('d/m/Y') }}
            </p>
        </div>
        <div>
            <p class="text-gray-400 text-xs uppercase tracking-wide">Hora</p>
            <p class="font-medium text-gray-800 mt-0.5">
                {{ \Carbon\Carbon::parse($reserva->hora)->format('H:i') }}
            </p>
        </div>
        <div>
            <p class="text-gray-400 text-xs uppercase tracking-wide">N° Personas</p>
            <p class="font-medium text-gray-800 mt-0.5">{{ $reserva->num_personas }}</p>
        </div>
        <div>
            <p class="text-gray-400 text-xs uppercase tracking-wide">Mesa</p>
            <p class="font-medium text-gray-800 mt-0.5">
                {{ $reserva->mesa_numero ? 'Mesa ' . $reserva->mesa_numero : 'Sin asignar' }}
            </p>
        </div>
        <div>
            <p class="text-gray-400 text-xs uppercase tracking-wide">Adelanto</p>
            <p class="font-medium text-gray-800 mt-0.5">S/ {{ number_format($reserva->monto_adelanto, 2) }}</p>
        </div>
        @if($reserva->observacion)
        <div class="col-span-2">
            <p class="text-gray-400 text-xs uppercase tracking-wide">Observaciones</p>
            <p class="font-medium text-gray-800 mt-0.5">{{ $reserva->observacion }}</p>
        </div>
        @endif
    </div>
</div>

@endsection
