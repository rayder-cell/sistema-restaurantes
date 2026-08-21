@extends('layouts.superadmin')

@section('title', 'Detalle — ' . $restaurante->nombre)

@section('content')

<div class="sa-page-header">
    <div>
        <p class="sa-page-sub">RESTAURANTE</p>
        <h2 class="sa-page-title">{{ $restaurante->nombre }}</h2>
    </div>
    <div class="flex gap-3">
        <a href="{{ route('superadmin.restaurantes.edit', $restaurante) }}" class="sa-btn-registrar">
            <i class="fa-solid fa-pen"></i> Editar
        </a>
        <a href="{{ route('superadmin.dashboard') }}" class="sa-btn-cancelar">
            <i class="fa-solid fa-arrow-left"></i> Volver
        </a>
    </div>
</div>

{{-- Info general --}}
<div class="sa-card mb-6 p-6">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div>
            <p class="sa-metric-label">NOMBRE</p>
            <p class="sa-rest-nombre mt-1">{{ $restaurante->nombre }}</p>
        </div>
        <div>
            <p class="sa-metric-label">RUC</p>
            <p class="sa-rest-nombre mt-1">{{ $restaurante->ruc }}</p>
        </div>
        <div>
            <p class="sa-metric-label">ESTADO</p>
            <div class="mt-1 flex items-center gap-3">
                @if($restaurante->estado === 'activo')
                    <span class="sa-estado-activo">● Activo</span>
                    {{-- Botón suspender desde show --}}
                    <button
                        type="button"
                        onclick="confirmarSuspender({{ $restaurante->id }}, '{{ addslashes($restaurante->nombre) }}')"
                        class="sa-btn-suspender text-xs">
                        SUSPENDER
                    </button>
                @elseif($restaurante->estado === 'suspendido')
                    <span class="sa-estado-suspendido">● Suspendido</span>
                    <button
                        type="button"
                        onclick="confirmarReactivar({{ $restaurante->id }}, '{{ addslashes($restaurante->nombre) }}')"
                        class="sa-btn-reactivar text-xs">
                        REACTIVAR
                    </button>
                @else
                    <span class="sa-estado-inactivo">● Inactivo</span>
                @endif
            </div>
        </div>
        <div class="sm:col-span-3">
            <p class="sa-metric-label">DIRECCIÓN</p>
            <p class="sa-rest-nombre mt-1">{{ $restaurante->direccion }}</p>
        </div>
    </div>
</div>

{{-- Series de comprobante --}}
<div class="sa-card mb-6 p-6">
    <h3 class="sa-page-title text-base mb-4"><i class="fa-solid fa-file-lines"></i> Series de Comprobante</h3>
    <div class="grid grid-cols-2 gap-4">
        @forelse($restaurante->seriesComprobante as $serie)
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <p class="sa-metric-label">{{ strtoupper($serie->tipo) }}</p>
                <p class="sa-rest-nombre mt-1">{{ $serie->serie }}</p>
                <p class="sa-rest-ruc">Correlativo: {{ $serie->correlativo_actual }}</p>
            </div>
        @empty
            <p class="text-gray-400 text-sm col-span-2">Sin series de comprobante registradas.</p>
        @endforelse
    </div>
</div>

{{-- Usuarios del restaurante --}}
<div class="sa-card p-6">
    <h3 class="sa-page-title text-base mb-4">
        <i class="fa-solid fa-users"></i> Usuarios ({{ $restaurante->usuarios->count() }})
    </h3>

    @if($restaurante->usuarios->count() > 0)
        <table class="sa-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($restaurante->usuarios as $usuario)
                    <tr>
                        <td>{{ $usuario->nombre }}</td>
                        <td>{{ $usuario->email }}</td>
                        <td>{{ ucfirst($usuario->rol?->nombre ?? '—') }}</td>
                        <td>
                            <span class="{{ $usuario->activo ? 'sa-estado-activo' : 'sa-estado-inactivo' }}">
                                {{ $usuario->activo ? '● Activo' : '● Inactivo' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-gray-400 text-sm text-center py-6">No hay usuarios registrados.</p>
    @endif
</div>

{{-- Forms ocultos para acciones POST --}}
<form id="form-suspender" method="POST" action="" class="hidden">
    @csrf @method('PATCH')
</form>
<form id="form-reactivar" method="POST" action="" class="hidden">
    @csrf @method('PATCH')
</form>

@endsection

@push('scripts')
<script>
    function confirmarSuspender(id, nombre) {
        Swal.fire({
            title: '¿Suspender restaurante?',
            html: `El restaurante <strong>${nombre}</strong> quedará inactivo y sus usuarios no podrán acceder al sistema.`,
            icon: 'warning',
            iconColor: '#EF4444',
            showCancelButton: true,
            confirmButtonText: 'Sí, suspender',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#6B7280',
            reverseButtons: true,
            focusCancel: true,
        }).then(result => {
            if (result.isConfirmed) {
                const form = document.getElementById('form-suspender');
                form.action = `/superadmin/restaurantes/${id}/suspender`;
                form.submit();
            }
        });
    }

    function confirmarReactivar(id, nombre) {
        Swal.fire({
            title: '¿Reactivar restaurante?',
            html: `El restaurante <strong>${nombre}</strong> volverá a estar activo y sus usuarios podrán acceder al sistema.`,
            icon: 'question',
            iconColor: '#7C3AED',
            showCancelButton: true,
            confirmButtonText: 'Sí, reactivar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#7C3AED',
            cancelButtonColor: '#6B7280',
            reverseButtons: true,
        }).then(result => {
            if (result.isConfirmed) {
                const form = document.getElementById('form-reactivar');
                form.action = `/superadmin/restaurantes/${id}/reactivar`;
                form.submit();
            }
        });
    }
</script>
@endpush