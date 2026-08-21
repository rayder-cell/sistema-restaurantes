@extends('layouts.app')

@section('title', 'Proveedores — MIKHUNAWASI')
@section('page-title', 'Proveedores')

@section('content')

<div class="page-toolbar">
    <form method="GET" action="{{ route('proveedores.index') }}" class="reserva-filtros">
        <input type="text" name="buscar" value="{{ request('buscar') }}"
            placeholder="Buscar por nombre o RUC..." class="form-input w-auto">
        <button type="submit" class="btn-submit px-4 py-2">
            <i class="fa-solid fa-magnifying-glass"></i> Buscar
        </button>
        <a href="{{ route('proveedores.index') }}" class="btn-cancel">
            <i class="fa-solid fa-xmark"></i> Limpiar
        </a>
    </form>
    <a href="{{ route('proveedores.create') }}" class="btn-create">
        <i class="fa-solid fa-plus"></i> Nuevo proveedor
    </a>
</div>

<div class="table-card mt-4">
    <table class="data-table">
        <thead>
            <tr>
                <th>Proveedor</th>
                <th>RUC</th>
                <th>Teléfono</th>
                <th>Email</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($proveedores as $prov)
            <tr>
                <td>
                    <p class="font-medium text-gray-800">{{ $prov->nombre }}</p>
                    @if($prov->direccion)
                    <p class="text-xs text-gray-400">{{ Str::limit($prov->direccion, 40) }}</p>
                    @endif
                </td>
                <td class="td-secondary">{{ $prov->ruc ?? '—' }}</td>
                <td class="td-secondary">{{ $prov->telefono ?? '—' }}</td>
                <td class="td-secondary">{{ $prov->email ?? '—' }}</td>
                <td>
                    <span class="status-badge {{ $prov->activo ? 'status-active' : 'status-inactive' }}">
                        {{ $prov->activo ? 'Activo' : 'Inactivo' }}
                    </span>
                </td>
                <td>
                    <div class="action-buttons">
                        <a href="{{ route('proveedores.edit', $prov->id) }}" class="btn-edit">
                            <i class="fa-solid fa-pen"></i> Editar
                        </a>
                        <form method="POST" action="{{ route('proveedores.destroy', $prov->id) }}"
                            onsubmit="return confirm('¿Eliminar este proveedor?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-delete">
                                <i class="fa-solid fa-trash"></i> Eliminar
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="td-empty">No hay proveedores registrados.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="pagination-wrapper">{{ $proveedores->links() }}</div>
</div>

@endsection