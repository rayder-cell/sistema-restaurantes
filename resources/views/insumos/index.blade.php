@extends('layouts.app')

@section('title', 'Insumos — MIKHUNAWASI')
@section('page-title', 'Inventario de Insumos')

@section('content')

{{-- Stats --}}
<div class="reserva-stats-grid mb-6">
    <div class="reserva-stat-card">
        <p class="reserva-stat-label">Total insumos</p>
        <p class="reserva-stat-value">{{ $stats->total ?? 0 }}</p>
    </div>
    <div class="reserva-stat-card red">
        <p class="reserva-stat-label">Stock bajo</p>
        <p class="reserva-stat-value">{{ $stats->bajo_stock ?? 0 }}</p>
    </div>
    <div class="reserva-stat-card green">
        <p class="reserva-stat-label">Activos</p>
        <p class="reserva-stat-value">{{ $stats->activos ?? 0 }}</p>
    </div>
</div>

{{-- Toolbar --}}
<div class="page-toolbar">
    <form method="GET" action="{{ route('insumos.index') }}" class="reserva-filtros">
        <input type="text" name="buscar" value="{{ request('buscar') }}"
            placeholder="Buscar insumo..." class="form-input w-auto">
        <select name="categoria" class="form-input w-auto">
            <option value="">Todas las categorías</option>
            @foreach($categorias as $cat)
            <option value="{{ $cat }}" {{ request('categoria') === $cat ? 'selected' : '' }}>
                {{ ucfirst($cat) }}
            </option>
            @endforeach
        </select>
        <select name="stock" class="form-input w-auto">
            <option value="">Todo el stock</option>
            <option value="bajo" {{ request('stock') === 'bajo' ? 'selected' : '' }}>⚠ Stock bajo</option>
            <option value="ok"   {{ request('stock') === 'ok'   ? 'selected' : '' }}>✓ Stock OK</option>
        </select>
        <button type="submit" class="btn-submit px-4 py-2">
            <i class="fa-solid fa-filter"></i> Filtrar
        </button>
        <a href="{{ route('insumos.index') }}" class="btn-cancel">
            <i class="fa-solid fa-xmark"></i> Limpiar
        </a>
    </form>
    <a href="{{ route('insumos.create') }}" class="btn-create">
        <i class="fa-solid fa-plus"></i> Nuevo insumo
    </a>
</div>

{{-- Tabla --}}
<div class="table-card mt-4">
    <table class="data-table">
        <thead>
            <tr>
                <th>Insumo</th>
                <th>Categoría</th>
                <th>Unidad</th>
                <th>Stock actual</th>
                <th>Stock mínimo</th>
                <th>Estado stock</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($insumos as $insumo)
            <tr>
                <td class="font-medium text-gray-800">{{ $insumo->nombre }}</td>
                <td class="td-secondary">{{ ucfirst($insumo->categoria ?? '—') }}</td>
                <td class="td-secondary">{{ $insumo->unidad_medida }}</td>
                <td>
                    <span class="font-semibold {{ $insumo->stock_actual <= $insumo->stock_minimo ? 'text-red-600' : 'text-green-600' }}">
                        {{ number_format($insumo->stock_actual, 2) }}
                    </span>
                </td>
                <td class="td-secondary">{{ number_format($insumo->stock_minimo, 2) }}</td>
                <td>
                    @if($insumo->stock_actual <= $insumo->stock_minimo)
                        <span class="badge badge-red">
                            <i class="fa-solid fa-triangle-exclamation"></i> Stock bajo
                        </span>
                    @else
                        <span class="badge badge-green">
                            <i class="fa-solid fa-circle-check"></i> OK
                        </span>
                    @endif
                </td>
                <td>
                    <div class="action-buttons">
                        <a href="{{ route('insumos.kardex', $insumo->id) }}" class="btn-edit">
                            <i class="fa-solid fa-clock-rotate-left"></i> Kardex
                        </a>
                        <a href="{{ route('insumos.edit', $insumo->id) }}" class="btn-edit">
                            <i class="fa-solid fa-pen"></i> Editar
                        </a>
                        <form method="POST" action="{{ route('insumos.destroy', $insumo->id) }}"
                            id="form-eliminar-insumo-{{ $insumo->id }}">
                            @csrf @method('DELETE')
                        </form>
                        <button type="button" class="btn-delete"
                            onclick="confirmarAccion('form-eliminar-insumo-{{ $insumo->id }}', '¿Eliminar insumo?', 'Se eliminará {{ addslashes($insumo->nombre) }} permanentemente.', '#dc2626')">
                            <i class="fa-solid fa-trash"></i> Eliminar
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="td-empty">
                    <i class="fa-solid fa-box-open text-gray-300 text-2xl mb-2 block"></i>
                    No hay insumos registrados.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="pagination-wrapper">{{ $insumos->links() }}</div>
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