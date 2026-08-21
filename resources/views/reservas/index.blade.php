@extends('layouts.app')

@section('title', 'Reservas — MIKHUNAWASI')
@section('page-title', 'Reservas')

@section('content')

    {{-- Stats --}}
    <div class="reserva-stats-grid">
        <div class="reserva-stat-card">
            <p class="reserva-stat-label">Total hoy</p>
            <p class="reserva-stat-value">{{ $statsHoy->total ?? 0 }}</p>
        </div>
        <div class="reserva-stat-card green">
            <p class="reserva-stat-label">Confirmadas</p>
            <p class="reserva-stat-value">{{ $statsHoy->confirmadas ?? 0 }}</p>
        </div>
        <div class="reserva-stat-card yellow">
            <p class="reserva-stat-label">Pendientes</p>
            <p class="reserva-stat-value">{{ $statsHoy->pendientes ?? 0 }}</p>
        </div>
        <div class="reserva-stat-card red">
            <p class="reserva-stat-label">Canceladas</p>
            <p class="reserva-stat-value">{{ $statsHoy->canceladas ?? 0 }}</p>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="page-toolbar mt-6">
        <form method="GET" action="{{ route('reservas.index') }}" class="reserva-filtros">
            <input type="date" name="fecha" value="{{ request('fecha') }}" class="form-input w-auto">
            <select name="estado" class="form-input w-auto text-sm">
                <option value="">Todos los estados</option>
                <option value="pendiente" {{ request('estado') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                <option value="confirmada" {{ request('estado') === 'confirmada' ? 'selected' : '' }}>Confirmada</option>
                <option value="cancelada" {{ request('estado') === 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                <option value="completada" {{ request('estado') === 'completada' ? 'selected' : '' }}>Completada</option>
            </select>
            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar cliente..."
                class="form-input w-auto">
            <button type="submit" class="btn-submit px-4 py-2">
                <i class="fa-solid fa-magnifying-glass"></i> Filtrar
            </button>
            <a href="{{ route('reservas.index') }}" class="btn-cancel">Limpiar</a>
        </form>
        <a href="{{ route('reservas.create') }}" class="btn-create">
            <i class="fa-solid fa-plus"></i> Nueva reserva
        </a>
    </div>

    {{-- Tabla --}}
    <div class="table-card mt-4">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Fecha y Hora</th>
                    <th>Mesa</th>
                    <th>Personas</th>
                    <th>Adelanto</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reservas as $reserva)
                    <tr>
                        <td>
                            <p class="font-medium text-gray-800">{{ $reserva->cliente_nombre }}</p>
                            <p class="text-xs text-gray-400">{{ $reserva->cliente_telefono }}</p>
                        </td>
                        <td class="td-secondary">
                            <p>{{ \Carbon\Carbon::parse($reserva->fecha)->format('d/m/Y') }}</p>
                            <p class="text-xs">{{ \Carbon\Carbon::parse($reserva->hora)->format('H:i') }}</p>
                        </td>
                        <td class="td-secondary">
                            {{ $reserva->mesa_numero ? 'Mesa ' . $reserva->mesa_numero : '—' }}
                        </td>
                        <td class="td-secondary text-center">{{ $reserva->num_personas }}</td>
                        <td class="td-secondary">
                            S/ {{ number_format($reserva->monto_adelanto, 2) }}
                        </td>
                        <td>
                            <span class="reserva-estado-badge estado-{{ $reserva->estado }}">
                                {{ ucfirst($reserva->estado) }}
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('reservas.show', $reserva->id) }}" class="btn-edit">
                                    <i class="fa-solid fa-eye"></i> Ver
                                </a>
                                <a href="{{ route('reservas.edit', $reserva->id) }}" class="btn-edit">
                                    <i class="fa-solid fa-pen"></i> Editar
                                </a>

                                @if ($reserva->estado === 'pendiente')
                                    <form method="POST" action="{{ route('reservas.estado', $reserva->id) }}"
                                        id="form-confirmar-{{ $reserva->id }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="estado" value="confirmada">
                                    </form>
                                    <button type="button"
                                        onclick="confirmarAccion('form-confirmar-{{ $reserva->id }}', '¿Confirmar reserva?', 'Se confirmará la reserva de {{ addslashes($reserva->cliente_nombre) }}.', '#16a34a')"
                                        class="btn-activate">
                                        <i class="fa-solid fa-check"></i> Confirmar
                                    </button>
                                @endif

                                @if (in_array($reserva->estado, ['pendiente', 'confirmada']))
                                    <form method="POST" action="{{ route('reservas.estado', $reserva->id) }}"
                                        id="form-cancelar-{{ $reserva->id }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="estado" value="cancelada">
                                    </form>
                                    <button type="button"
                                        onclick="confirmarAccion('form-cancelar-{{ $reserva->id }}', '¿Cancelar reserva?', 'Se cancelará la reserva de {{ addslashes($reserva->cliente_nombre) }}.', '#f59e0b')"
                                        class="btn-deactivate">
                                        <i class="fa-solid fa-ban"></i> Cancelar
                                    </button>
                                @endif

                                <form method="POST" action="{{ route('reservas.destroy', $reserva->id) }}"
                                    id="form-eliminar-{{ $reserva->id }}">
                                    @csrf @method('DELETE')
                                </form>
                                <button type="button"
                                    onclick="confirmarAccion('form-eliminar-{{ $reserva->id }}', '¿Eliminar reserva?', 'Se eliminará permanentemente la reserva de {{ addslashes($reserva->cliente_nombre) }}.', '#dc2626')"
                                    class="btn-delete">
                                    <i class="fa-solid fa-trash"></i> Eliminar
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="td-empty">No hay reservas registradas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="pagination-wrapper">{{ $reservas->links() }}</div>
    </div>

    <script>
        function confirmarAccion(formId, titulo, texto, colorBoton = '#7e22ce') {
            Swal.fire({
                title: titulo,
                text: texto,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: colorBoton,
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(formId).submit();
                }
            });
        }
    </script>

@endsection