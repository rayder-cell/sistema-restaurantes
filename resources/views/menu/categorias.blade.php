@extends('layouts.app')

@push('styles')
    @vite(['resources/assets/css/pages/menu.css'])
@endpush

@section('title', 'Categorías de Menú — MIKHUNAWASI')
@section('page-title', 'Categorías')

@section('content')

<div class="menu-page-header">
    <div>
        <h2 class="menu-page-title">Categorías del Menú</h2>
        <p class="menu-page-sub">Organiza tus platillos por categorías.</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('productos.index') }}" class="menu-btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Ver Menú
        </a>
        <button onclick="abrirModalCategoria()" class="menu-btn-agregar">
            <i class="fa-solid fa-plus"></i> Nueva Categoría
        </button>
    </div>
</div>

{{-- Tabla de categorías --}}
<div class="menu-cat-card">
    @if($categorias->count() > 0)
        <table class="menu-cat-table">
            <thead>
                <tr>
                    <th>NOMBRE</th>
                    <th>DESCRIPCIÓN</th>
                    <th>PRODUCTOS</th>
                    <th>ORDEN</th>
                    <th>ESTADO</th>
                    <th>ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categorias as $cat)
                <tr>
                    <td class="font-semibold text-gray-800">{{ $cat->nombre }}</td>
                    <td class="text-gray-500 text-sm">{{ $cat->descripcion ?? '—' }}</td>
                    <td>
                        <span class="menu-cat-count">{{ $cat->productos_count }}</span>
                    </td>
                    <td class="text-gray-500 text-sm">{{ $cat->orden }}</td>
                    <td>
                        @if($cat->activa)
                            <span class="badge badge-green">Activa</span>
                        @else
                            <span class="badge badge-gray">Inactiva</span>
                        @endif
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button onclick="editarCategoria({{ $cat->id }}, '{{ addslashes($cat->nombre) }}', '{{ addslashes($cat->descripcion ?? '') }}', {{ $cat->orden }}, {{ $cat->activa ? 'true' : 'false' }})"
                                    class="btn-edit">
                                <i class="fa-solid fa-pen"></i> Editar
                            </button>
                            @if($cat->productos_count === 0)
                                <button onclick="eliminarCategoria({{ $cat->id }}, '{{ addslashes($cat->nombre) }}')"
                                        class="btn-delete">
                                    <i class="fa-solid fa-trash"></i> Eliminar
                                </button>
                            @else
                                <span class="text-xs text-gray-300" title="No se puede eliminar: tiene productos asociados">
                                    <i class="fa-solid fa-trash"></i> Eliminar
                                </span>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="menu-empty py-12">
            <p class="menu-empty-icon"><i class="fa-solid fa-folder-open"></i></p>
            <p class="menu-empty-title">No hay categorías creadas</p>
            <p class="menu-empty-sub">Crea tu primera categoría para organizar el menú</p>
            <button onclick="abrirModalCategoria()" class="menu-btn-agregar mt-4">
                <i class="fa-solid fa-plus"></i> Nueva Categoría
            </button>
        </div>
    @endif
</div>


{{-- Modal nueva categoría --}}
<div id="modal-categoria" class="menu-modal-overlay hidden">
    <div class="menu-modal" style="max-width:480px;">
        <div class="menu-modal-header">
            <div>
                <h3 class="menu-modal-title" id="modal-cat-titulo">Nueva Categoría</h3>
            </div>
            <button onclick="cerrarModalCategoria()" class="menu-modal-close">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form method="POST" id="form-categoria" class="menu-modal-body">
            @csrf
            <div id="method-field"></div>

            <div class="menu-form-group">
                <label class="menu-form-label">Nombre *</label>
                <input type="text" name="nombre" id="cat-nombre"
                       placeholder="Ej. Entradas, Fondos, Postres..."
                       class="menu-form-input" required>
            </div>
            <div class="menu-form-group">
                <label class="menu-form-label">Descripción</label>
                <input type="text" name="descripcion" id="cat-descripcion"
                       placeholder="Descripción opcional"
                       class="menu-form-input">
            </div>
            <div class="menu-form-row">
                <div class="menu-form-group">
                    <label class="menu-form-label">Orden de aparición</label>
                    <input type="number" name="orden" id="cat-orden"
                           value="0" min="0" class="menu-form-input">
                </div>
                <div class="menu-form-group" id="activa-group" style="display:none;">
                    <label class="menu-form-label">¿Activa?</label>
                    <label class="menu-toggle mt-2">
                        <input type="checkbox" name="activa" id="cat-activa" checked>
                        <span class="menu-toggle-slider"></span>
                    </label>
                </div>
            </div>

            <div class="menu-modal-actions">
                <button type="button" onclick="cerrarModalCategoria()" class="menu-btn-cancelar">Cancelar</button>
                <button type="submit" class="menu-btn-guardar">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<form id="form-eliminar-cat" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
function abrirModalCategoria() {
    document.getElementById('modal-cat-titulo').textContent = 'Nueva Categoría';
    document.getElementById('form-categoria').action = '{{ route("categorias.store") }}';
    document.getElementById('method-field').innerHTML = '';
    document.getElementById('cat-nombre').value = '';
    document.getElementById('cat-descripcion').value = '';
    document.getElementById('cat-orden').value = 0;
    document.getElementById('activa-group').style.display = 'none';
    document.getElementById('modal-categoria').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function cerrarModalCategoria() {
    document.getElementById('modal-categoria').classList.add('hidden');
    document.body.style.overflow = '';
}
function editarCategoria(id, nombre, descripcion, orden, activa) {
    document.getElementById('modal-cat-titulo').textContent = 'Editar Categoría';
    document.getElementById('form-categoria').action = `/categorias/${id}`;
    document.getElementById('method-field').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('cat-nombre').value = nombre;
    document.getElementById('cat-descripcion').value = descripcion;
    document.getElementById('cat-orden').value = orden;
    document.getElementById('cat-activa').checked = activa;
    document.getElementById('activa-group').style.display = 'block';
    document.getElementById('modal-categoria').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function eliminarCategoria(id, nombre) {
    Swal.fire({
        title: '¿Eliminar categoría?',
        text: `Se eliminará "${nombre}" permanentemente.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('form-eliminar-cat');
            form.action = `/categorias/${id}`;
            form.submit();
        }
    });
}
document.getElementById('modal-categoria').addEventListener('click', function(e) {
    if (e.target === this) cerrarModalCategoria();
});
</script>

@endsection