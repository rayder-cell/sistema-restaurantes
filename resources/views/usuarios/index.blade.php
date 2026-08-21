@extends('layouts.app')

@section('title', 'Usuarios — MIKHUNAWASI')
@section('page-title', 'Usuarios')

@section('content')

<div class="page-toolbar">
    <div>
        <h2 class="page-subtitle">Gestión de usuarios del sistema</h2>
    </div>
    <a href="{{ route('usuarios.create') }}" class="btn-create">
        <i class="fa-solid fa-plus"></i> Nuevo usuario
    </a>
</div>

{{-- Tabla --}}
<div class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Email</th>
                <th>Rol</th>
                @if(session('usuario_rol') === 'superadmin')
                <th>Restaurante</th>
                @endif
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($usuarios as $usuario)
            <tr>
                <td>
                    <div class="user-cell">
                        <div class="user-avatar">{{ strtoupper(substr($usuario->nombre, 0, 2)) }}</div>
                        <span class="user-cell-name">{{ $usuario->nombre }}</span>
                    </div>
                </td>
                <td class="td-secondary">{{ $usuario->email }}</td>
                <td>
                    <span class="role-badge role-{{ $usuario->rol?->nombre }}">
                        {{ ucfirst($usuario->rol?->nombre ?? '—') }}
                    </span>
                </td>
                @if(session('usuario_rol') === 'superadmin')
                <td class="td-secondary">{{ $usuario->restaurante?->nombre ?? '—' }}</td>
                @endif
                <td>
                    <span class="status-badge {{ $usuario->activo ? 'status-active' : 'status-inactive' }}">
                        {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
                    </span>
                </td>
                <td>
                    <div class="action-buttons">
                        <a href="{{ route('usuarios.edit', $usuario) }}" class="btn-edit" title="Editar">
                            <i class="fa-solid fa-pen"></i> Editar
                        </a>

                        <form method="POST" action="{{ route('usuarios.toggle', $usuario) }}"
                            id="form-toggle-{{ $usuario->id }}">
                            @csrf @method('PATCH')
                        </form>
                        <button type="button"
                            onclick="confirmarAccion('form-toggle-{{ $usuario->id }}', '{{ $usuario->activo ? '¿Desactivar usuario?' : '¿Activar usuario?' }}', 'Se {{ $usuario->activo ? 'desactivará' : 'activará' }} a {{ addslashes($usuario->nombre) }}.', '{{ $usuario->activo ? '#f59e0b' : '#16a34a' }}')"
                            class="btn-toggle {{ $usuario->activo ? 'btn-deactivate' : 'btn-activate' }}"
                            title="{{ $usuario->activo ? 'Desactivar' : 'Activar' }}">
                            <i class="fa-solid {{ $usuario->activo ? 'fa-ban' : 'fa-check' }}"></i>
                            {{ $usuario->activo ? 'Desactivar' : 'Activar' }}
                        </button>

                        @if($usuario->id !== session('usuario_id'))
                        <form method="POST" action="{{ route('usuarios.destroy', $usuario) }}"
                            id="form-eliminar-{{ $usuario->id }}">
                            @csrf @method('DELETE')
                        </form>
                        <button type="button"
                            onclick="confirmarAccion('form-eliminar-{{ $usuario->id }}', '¿Eliminar usuario?', 'Se eliminará a {{ addslashes($usuario->nombre) }} permanentemente.', '#dc2626')"
                            class="btn-delete" title="Eliminar">
                            <i class="fa-solid fa-trash"></i> Eliminar
                        </button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="td-empty">No hay usuarios registrados.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination-wrapper">
        {{ $usuarios->links() }}
    </div>
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