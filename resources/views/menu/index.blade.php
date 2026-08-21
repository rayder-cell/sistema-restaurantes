@extends('layouts.app')

@push('styles')
    @vite(['resources/assets/css/pages/menu.css'])
@endpush

@section('title', 'Gestión de Menú — MIKHUNAWASI')
@section('page-title', 'Menú')

@section('content')

    {{-- ══════════════════════════════════════════════
         TABS
    ══════════════════════════════════════════════ --}}
    <div class="menu-tabs-header">
        <div class="menu-tabs">
            <button onclick="cambiarTab('productos')" id="tab-productos"
                class="menu-tab active">
                <i class="fa-solid fa-bowl-food"></i> Productos
            </button>
            <button onclick="cambiarTab('categorias')" id="tab-categorias"
                class="menu-tab">
                <i class="fa-solid fa-tags"></i> Categorías
            </button>
        </div>
        <div id="btn-tab-productos" class="menu-tab-action">
            <button onclick="abrirModalProducto()" class="menu-btn-agregar">
                <i class="fa-solid fa-plus"></i> Agregar Producto
            </button>
        </div>
        <div id="btn-tab-categorias" class="menu-tab-action hidden">
            <button onclick="abrirModalCategoria()" class="menu-btn-agregar">
                <i class="fa-solid fa-plus"></i> Nueva Categoría
            </button>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         TAB: PRODUCTOS
    ══════════════════════════════════════════════ --}}
    <div id="panel-productos">

        {{-- Filtros por categoría --}}
        <div class="menu-filtros">
            <a href="{{ route('productos.index') }}"
                class="menu-filtro-btn {{ !request('categoria_id') ? 'active' : '' }}">
                Todos
            </a>
            @foreach ($categorias as $cat)
                <a href="{{ route('productos.index', ['categoria_id' => $cat->id]) }}"
                    class="menu-filtro-btn {{ request('categoria_id') == $cat->id ? 'active' : '' }}">
                    {{ $cat->nombre }}
                </a>
            @endforeach
        </div>

        {{-- Grid de productos --}}
        @if ($productos->count() > 0)
            <div class="menu-grid">
                @foreach ($productos as $producto)
                    <div class="menu-card {{ !$producto->disponible ? 'agotado' : '' }}">
                        <div class="menu-card-img-wrap">
                            @if ($producto->imagen_url)
                                <img src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre }}"
                                    class="menu-card-img">
                            @else
                                <div class="menu-card-img-placeholder"><i class="fa-solid fa-utensils"></i></div>
                            @endif
                            <span class="menu-card-cat-badge">{{ strtoupper($producto->categoria->nombre) }}</span>
                            @if (!$producto->disponible)
                                <div class="menu-card-agotado-overlay">AGOTADO</div>
                            @endif
                        </div>
                        <div class="menu-card-body">
                            <div class="menu-card-top">
                                <p class="menu-card-nombre">{{ $producto->nombre }}</p>
                                <p class="menu-card-precio">S/<span>{{ number_format($producto->precio, 2) }}</span></p>
                            </div>
                            @if ($producto->descripcion)
                                <p class="menu-card-desc">{{ Str::limit($producto->descripcion, 40) }}</p>
                            @endif
                            <div class="menu-card-stock-row">
                                <span class="menu-stock-label">Stock</span>
                                <label class="menu-toggle">
                                    <input type="checkbox" {{ $producto->disponible ? 'checked' : '' }}
                                        onchange="toggleDisponible({{ $producto->id }}, this)">
                                    <span class="menu-toggle-slider"></span>
                                </label>
                            </div>
                            <div class="menu-card-actions-row">
                                <button onclick="abrirModalEditar({{ $producto->id }})" class="btn-edit">
                                    <i class="fa-solid fa-pen"></i> Editar
                                </button>
                                <button onclick="eliminarProducto({{ $producto->id }}, '{{ addslashes($producto->nombre) }}')"
                                    class="btn-delete">
                                    <i class="fa-solid fa-trash"></i> Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="menu-empty">
                <p class="menu-empty-icon"><i class="fa-solid fa-utensils"></i></p>
                <p class="menu-empty-title">No hay productos en el menú</p>
                <p class="menu-empty-sub">Agrega tu primer platillo</p>
                <button onclick="abrirModalProducto()" class="menu-btn-agregar mt-4">
                    <i class="fa-solid fa-plus"></i> Agregar Producto
                </button>
            </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════
         TAB: CATEGORÍAS
    ══════════════════════════════════════════════ --}}
    <div id="panel-categorias" class="hidden">
        <div class="menu-cat-card mt-4">
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
                        <td><span class="menu-cat-count">{{ $cat->productos_count }}</span></td>
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
                                    <span class="text-xs text-gray-300 px-2" title="Tiene productos asociados">
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
                <button onclick="abrirModalCategoria()" class="menu-btn-agregar mt-4">
                    <i class="fa-solid fa-plus"></i> Nueva Categoría
                </button>
            </div>
            @endif
        </div>
    </div>


    {{-- ══════════════════════════════════════════════
         MODAL: Añadir / Editar Producto
    ══════════════════════════════════════════════ --}}
    <div id="modal-producto" class="menu-modal-overlay hidden">
        <div class="menu-modal">
            <div class="menu-modal-header">
                <div>
                    <h3 class="menu-modal-title">Añadir Nuevo Producto</h3>
                    <p class="menu-modal-sub">Completa los detalles para incluir el plato en la carta.</p>
                </div>
                <button onclick="cerrarModalProducto()" class="menu-modal-close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('productos.store') }}" enctype="multipart/form-data"
                id="form-producto" class="menu-modal-body">
                @csrf
                <div class="menu-form-group">
                    <label class="menu-form-label">Fotografía del Producto</label>
                    <div class="menu-upload-area" onclick="document.getElementById('input-imagen').click()">
                        <div id="upload-preview" class="menu-upload-preview hidden">
                            <img id="preview-img" src="" alt="preview" class="menu-preview-img">
                            <button type="button" onclick="limpiarImagen(event)" class="menu-preview-remove">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <div id="upload-placeholder" class="menu-upload-placeholder">
                            <span class="menu-upload-icon"><i class="fa-solid fa-cloud-arrow-up"></i></span>
                            <p>Arrastra una imagen o <span class="text-purple-600 underline cursor-pointer">haz clic aquí</span></p>
                            <p class="menu-upload-hint">JPG, PNG o WEBP. Máximo 5MB.</p>
                        </div>
                    </div>
                    <input type="file" id="input-imagen" name="imagen" accept="image/*" class="hidden"
                        onchange="previewImagen(this)">
                </div>
                <div class="menu-form-group">
                    <label class="menu-form-label">Nombre del Producto *</label>
                    <input type="text" name="nombre" id="nombre-crear" placeholder="Ej. Ceviche de Mero" class="menu-form-input">
                    <p class="menu-field-error hidden" id="err-nombre-crear"></p>
                </div>
                <div class="menu-form-group">
                    <label class="menu-form-label">Descripción</label>
                    <textarea name="descripcion" rows="3" placeholder="Ingredientes, origen o preparación..."
                        class="menu-form-input"></textarea>
                </div>
                <div class="menu-form-row">
                    <div class="menu-form-group">
                        <label class="menu-form-label">Categoría *</label>
                        <select name="categoria_id" id="categoria-crear" class="menu-form-input">
                            <option value="">Seleccionar...</option>
                            @foreach ($categorias as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                            @endforeach
                        </select>
                        <p class="menu-field-error hidden" id="err-categoria-crear"></p>
                    </div>
                    <div class="menu-form-group">
                        <label class="menu-form-label">Precio (S/) *</label>
                        <div class="menu-precio-wrap">
                            <span class="menu-precio-prefix">S/</span>
                            <input type="text" name="precio" id="precio-crear" placeholder="0.00"
                                inputmode="decimal" autocomplete="off"
                                class="menu-form-input menu-precio-input">
                        </div>
                        <p class="menu-field-error hidden" id="err-precio-crear"></p>
                    </div>
                </div>
                <div class="menu-form-disponible">
                    <div>
                        <p class="menu-form-label">¿Disponible ahora?</p>
                        <p class="text-xs text-gray-400">El producto aparecerá en la carta inmediatamente.</p>
                    </div>
                    <input type="hidden" name="disponible" value="0">
                    <label class="menu-toggle">
                        <input type="checkbox" name="disponible" value="1" checked>
                        <span class="menu-toggle-slider"></span>
                    </label>
                </div>
                <div class="menu-modal-actions">
                    <button type="button" onclick="cerrarModalProducto()" class="menu-btn-cancelar">Cancelar</button>
                    <button type="submit" class="menu-btn-guardar">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar Producto
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL: Editar Producto --}}
    <div id="modal-editar" class="menu-modal-overlay hidden">
        <div class="menu-modal">
            <div class="menu-modal-header">
                <div>
                    <h3 class="menu-modal-title">Editar Producto</h3>
                    <p class="menu-modal-sub">Modifica los datos del platillo.</p>
                </div>
                <button onclick="cerrarModalEditar()" class="menu-modal-close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form method="POST" id="form-editar" enctype="multipart/form-data" class="menu-modal-body">
                @csrf @method('PUT')
                <div class="menu-form-group">
                    <label class="menu-form-label">Nombre *</label>
                    <input type="text" name="nombre" id="edit-nombre" class="menu-form-input">
                    <p class="menu-field-error hidden" id="err-nombre-editar"></p>
                </div>
                <div class="menu-form-group">
                    <label class="menu-form-label">Descripción</label>
                    <textarea name="descripcion" id="edit-descripcion" rows="3" class="menu-form-input"></textarea>
                </div>
                <div class="menu-form-row">
                    <div class="menu-form-group">
                        <label class="menu-form-label">Categoría *</label>
                        <select name="categoria_id" id="edit-categoria" class="menu-form-input">
                            @foreach ($categorias as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                            @endforeach
                        </select>
                        <p class="menu-field-error hidden" id="err-categoria-editar"></p>
                    </div>
                    <div class="menu-form-group">
                        <label class="menu-form-label">Precio (S/) *</label>
                        <div class="menu-precio-wrap">
                            <span class="menu-precio-prefix">S/</span>
                            <input type="text" name="precio" id="edit-precio"
                                inputmode="decimal" autocomplete="off"
                                class="menu-form-input menu-precio-input">
                        </div>
                        <p class="menu-field-error hidden" id="err-precio-editar"></p>
                    </div>
                </div>
                <div class="menu-form-group">
                    <label class="menu-form-label">Nueva imagen <span class="text-gray-400 font-normal">(opcional)</span></label>
                    <input type="file" name="imagen" accept="image/*" class="menu-form-input">
                </div>
                <div class="menu-form-disponible">
                    <div><p class="menu-form-label">¿Disponible?</p></div>
                    <input type="hidden" name="disponible" value="0">
                    <label class="menu-toggle">
                        <input type="checkbox" name="disponible" value="1" id="edit-disponible">
                        <span class="menu-toggle-slider"></span>
                    </label>
                </div>
                <div class="menu-modal-actions">
                    <button type="button" onclick="cerrarModalEditar()" class="menu-btn-cancelar">Cancelar</button>
                    <button type="submit" class="menu-btn-guardar">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL: Nueva / Editar Categoría --}}
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
                        placeholder="Ej. Entradas, Fondos, Postres..." class="menu-form-input" required>
                </div>
                <div class="menu-form-group">
                    <label class="menu-form-label">Descripción</label>
                    <input type="text" name="descripcion" id="cat-descripcion"
                        placeholder="Descripción opcional" class="menu-form-input">
                </div>
                <div class="menu-form-row">
                    <div class="menu-form-group">
                        <label class="menu-form-label">Orden</label>
                        <input type="number" name="orden" id="cat-orden" value="0" min="0" class="menu-form-input">
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

    {{-- Forms ocultos --}}
    <form id="form-eliminar" method="POST" class="hidden">@csrf @method('DELETE')</form>
    <form id="form-eliminar-cat" method="POST" class="hidden">@csrf @method('DELETE')</form>

    {{-- Estilos del error de precio --}}
    <style>
        .menu-field-error {
            font-size: 0.75rem;
            color: #DC2626;
            margin-top: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        .menu-precio-input.input-error {
            border-color: #EF4444 !important;
            background-color: #FFF8F8 !important;
        }
    </style>

    {{-- Datos productos para JS --}}
    <script>
        const productosData = @json($productos->keyBy('id'));

        // ── TABS ─────────────────────────────────────────────────────────
        function cambiarTab(tab) {
            const panelProductos  = document.getElementById('panel-productos');
            const panelCategorias = document.getElementById('panel-categorias');
            const tabProductos    = document.getElementById('tab-productos');
            const tabCategorias   = document.getElementById('tab-categorias');
            const btnProductos    = document.getElementById('btn-tab-productos');
            const btnCategorias   = document.getElementById('btn-tab-categorias');

            if (tab === 'productos') {
                panelProductos.classList.remove('hidden');
                panelCategorias.classList.add('hidden');
                tabProductos.classList.add('active');
                tabCategorias.classList.remove('active');
                btnProductos.classList.remove('hidden');
                btnCategorias.classList.add('hidden');
            } else {
                panelProductos.classList.add('hidden');
                panelCategorias.classList.remove('hidden');
                tabProductos.classList.remove('active');
                tabCategorias.classList.add('active');
                btnProductos.classList.add('hidden');
                btnCategorias.classList.remove('hidden');
            }
        }

        // Abrir en tab correcto si viene de un redirect
        @if(session('tab') === 'categorias')
            document.addEventListener('DOMContentLoaded', () => cambiarTab('categorias'));
        @endif

        // ── PRODUCTOS ─────────────────────────────────────────────────────
        function abrirModalProducto() {
            document.getElementById('modal-producto').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        function cerrarModalProducto() {
            document.getElementById('modal-producto').classList.add('hidden');
            document.body.style.overflow = '';
            limpiarErrorCampo('precio-crear', 'err-precio-crear');
            limpiarErrorCampo('nombre-crear', 'err-nombre-crear');
            limpiarErrorCampo('categoria-crear', 'err-categoria-crear');
        }

        function previewImagen(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    document.getElementById('preview-img').src = e.target.result;
                    document.getElementById('upload-preview').classList.remove('hidden');
                    document.getElementById('upload-placeholder').classList.add('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
        function limpiarImagen(e) {
            e.stopPropagation();
            document.getElementById('input-imagen').value = '';
            document.getElementById('upload-preview').classList.add('hidden');
            document.getElementById('upload-placeholder').classList.remove('hidden');
        }

        function toggleDisponible(id, checkbox) {
            fetch(`/productos/${id}/toggle`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            }).then(r => r.json()).then(data => {
                if (!data.success) {
                    checkbox.checked = !checkbox.checked;
                    return;
                }

                if (productosData[id]) {
                    productosData[id].disponible = data.disponible;
                }

                const card = checkbox.closest('.menu-card');
                const imgWrap = card.querySelector('.menu-card-img-wrap');
                let overlay = card.querySelector('.menu-card-agotado-overlay');

                if (data.disponible) {
                    card.classList.remove('agotado');
                    if (overlay) overlay.remove();
                } else {
                    card.classList.add('agotado');
                    if (!overlay) {
                        overlay = document.createElement('div');
                        overlay.className = 'menu-card-agotado-overlay';
                        overlay.textContent = 'AGOTADO';
                        imgWrap.appendChild(overlay);
                    }
                }
            }).catch(() => { checkbox.checked = !checkbox.checked; });
        }

        function abrirModalEditar(id) {
            const p = productosData[id];
            if (!p) return;
            limpiarErrorCampo('edit-nombre', 'err-nombre-editar');
            limpiarErrorCampo('edit-categoria', 'err-categoria-editar');
            limpiarErrorCampo('edit-precio', 'err-precio-editar');
            document.getElementById('edit-nombre').value      = p.nombre;
            document.getElementById('edit-descripcion').value = p.descripcion || '';
            document.getElementById('edit-categoria').value   = p.categoria_id;
            document.getElementById('edit-precio').value      = Number(p.precio).toFixed(2);
            document.getElementById('edit-disponible').checked = p.disponible;
            document.getElementById('form-editar').action = `/productos/${id}`;
            document.getElementById('modal-editar').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        function cerrarModalEditar() {
            document.getElementById('modal-editar').classList.add('hidden');
            document.body.style.overflow = '';
            limpiarErrorCampo('edit-precio', 'err-precio-editar');
            limpiarErrorCampo('edit-nombre', 'err-nombre-editar');
            limpiarErrorCampo('edit-categoria', 'err-categoria-editar');
        }

        function eliminarProducto(id, nombre) {
            Swal.fire({
                title: '¿Eliminar producto?',
                text: `Se eliminará "${nombre}" permanentemente.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(r => {
                if (r.isConfirmed) {
                    const form = document.getElementById('form-eliminar');
                    form.action = `/productos/${id}`;
                    form.submit();
                }
            });
        }

        // ── VALIDACIÓN DE PRECIO ─────────────────────────────────────────
        // Formato válido: "56", "56.2", "56.20" — NO admite comas, negativos ni ceros a la izquierda (ej. "050").
        const REGEX_PRECIO = /^(0|[1-9]\d*)(\.\d{1,2})?$/;

        function sanitizarPrecio(input) {
            let v = input.value;

            // Solo dígitos y punto
            v = v.replace(/[^0-9.]/g, '');

            // Un solo punto decimal
            const partes = v.split('.');
            if (partes.length > 2) {
                v = partes[0] + '.' + partes.slice(1).join('');
            }

            // Máximo 2 decimales
            if (v.includes('.')) {
                const [entero, decimales] = v.split('.');
                v = entero + '.' + decimales.slice(0, 2);
            }

            // Sin ceros a la izquierda (050 -> 50), salvo "0" o "0."
            v = v.replace(/^0+(?=\d)/, '');

            input.value = v;
        }

        function precioValido(valorStr) {
            const v = valorStr.trim();
            if (v === '' || v === '.') return false;
            return REGEX_PRECIO.test(v) && parseFloat(v) > 0;
        }

        function mostrarErrorCampo(inputId, errorId, mensaje) {
            const input = document.getElementById(inputId);
            const error = document.getElementById(errorId);
            input.classList.add('input-error');
            error.textContent = mensaje;
            error.classList.remove('hidden');
        }

        function limpiarErrorCampo(inputId, errorId) {
            const input = document.getElementById(inputId);
            const error = document.getElementById(errorId);
            input.classList.remove('input-error');
            error.textContent = '';
            error.classList.add('hidden');
        }

        ['precio-crear', 'edit-precio'].forEach(id => {
            const input = document.getElementById(id);
            const errId = id === 'precio-crear' ? 'err-precio-crear' : 'err-precio-editar';

            input.addEventListener('input', function() {
                sanitizarPrecio(this);
                limpiarErrorCampo(id, errId);
            });

            input.addEventListener('blur', function() {
                if (this.value !== '' && !precioValido(this.value)) {
                    mostrarErrorCampo(id, errId, 'Precio inválido. Usa un formato como 56 o 56.20');
                }
            });
        });

        // Limpiar error de nombre/categoría al corregir
        ['nombre-crear', 'categoria-crear'].forEach(id => {
            document.getElementById(id).addEventListener('input', function() {
                limpiarErrorCampo(id, id === 'nombre-crear' ? 'err-nombre-crear' : 'err-categoria-crear');
            });
        });
        ['edit-nombre', 'edit-categoria'].forEach(id => {
            document.getElementById(id).addEventListener('input', function() {
                limpiarErrorCampo(id, id === 'edit-nombre' ? 'err-nombre-editar' : 'err-categoria-editar');
            });
        });

        document.getElementById('form-producto').addEventListener('submit', function(e) {
            let valido = true;

            const nombre = document.getElementById('nombre-crear').value.trim();
            const categoria = document.getElementById('categoria-crear').value;
            const precio = document.getElementById('precio-crear').value;

            limpiarErrorCampo('nombre-crear', 'err-nombre-crear');
            limpiarErrorCampo('categoria-crear', 'err-categoria-crear');
            limpiarErrorCampo('precio-crear', 'err-precio-crear');

            if (!nombre) {
                mostrarErrorCampo('nombre-crear', 'err-nombre-crear', '⚠︎ El nombre del producto es obligatorio.');
                valido = false;
            }

            if (!categoria) {
                mostrarErrorCampo('categoria-crear', 'err-categoria-crear', '⚠︎ Selecciona una categoría.');
                valido = false;
            }

            if (!precioValido(precio)) {
                mostrarErrorCampo('precio-crear', 'err-precio-crear', '⚠︎ Ingresa un precio válido, ej. 56 o 56.20');
                valido = false;
            }

            if (!valido) e.preventDefault();
        });

        document.getElementById('form-editar').addEventListener('submit', function(e) {
            let valido = true;

            const nombre = document.getElementById('edit-nombre').value.trim();
            const categoria = document.getElementById('edit-categoria').value;
            const precio = document.getElementById('edit-precio').value;

            limpiarErrorCampo('edit-nombre', 'err-nombre-editar');
            limpiarErrorCampo('edit-categoria', 'err-categoria-editar');
            limpiarErrorCampo('edit-precio', 'err-precio-editar');

            if (!nombre) {
                mostrarErrorCampo('edit-nombre', 'err-nombre-editar', '⚠︎ El nombre del producto es obligatorio.');
                valido = false;
            }

            if (!categoria) {
                mostrarErrorCampo('edit-categoria', 'err-categoria-editar', '⚠︎ Selecciona una categoría.');
                valido = false;
            }

            if (!precioValido(precio)) {
                mostrarErrorCampo('edit-precio', 'err-precio-editar', '⚠︎ Ingresa un precio válido, ej. 56 o 56.20');
                valido = false;
            }

            if (!valido) e.preventDefault();
        });

        // ── CATEGORÍAS ────────────────────────────────────────────────────
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
            document.getElementById('cat-nombre').value       = nombre;
            document.getElementById('cat-descripcion').value  = descripcion;
            document.getElementById('cat-orden').value        = orden;
            document.getElementById('cat-activa').checked     = activa;
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
            }).then(r => {
                if (r.isConfirmed) {
                    const form = document.getElementById('form-eliminar-cat');
                    form.action = `/categorias/${id}`;
                    form.submit();
                }
            });
        }

        // Cerrar modales al click fuera
        ['modal-producto','modal-editar','modal-categoria'].forEach(id => {
            document.getElementById(id).addEventListener('click', function(e) {
                if (e.target === this) this.classList.add('hidden'), document.body.style.overflow = '';
            });
        });
    </script>

@endsection