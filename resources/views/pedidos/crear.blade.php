@extends('layouts.app')

@section('title', 'Toma de Pedidos — MIKHUNAWASI')
@section('page-title', 'Toma de Pedidos')

@push('styles')
    @vite('resources/assets/css/pages/pedidos.css')
@endpush

@section('content')

    {{-- Layout principal 3 columnas --}}
    <div class="tpd-layout">

        {{-- ══════════════════════════════════════
         COL 1: CATEGORÍAS
    ══════════════════════════════════════ --}}
        <div class="tpd-col-cats">

            {{-- Buscador --}}
            <div class="tpd-search-wrap">
                <span class="tpd-search-ico"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" id="buscador-plato" placeholder="Buscar plato..." class="tpd-search-input"
                    oninput="buscarProducto(this.value)">
            </div>

            <p class="tpd-cats-label">CATEGORÍAS</p>
            <ul class="tpd-cats-list">
                @foreach ($categorias as $cat)
                    @php
                        $nombreLower = strtolower($cat->nombre);
                        $icono = match (true) {
                            str_contains($nombreLower, 'bebida') => 'fa-mug-saucer',
                            str_contains($nombreLower, 'postre') => 'fa-ice-cream',
                            str_contains($nombreLower, 'entrada') => 'fa-bowl-food',
                            str_contains($nombreLower, 'sopa') => 'fa-bowl-rice',
                            str_contains($nombreLower, 'parrilla') => 'fa-fire-burner',
                            str_contains($nombreLower, 'pizza') => 'fa-pizza-slice',
                            str_contains($nombreLower, 'almuerzo') => 'fa-utensils',
                            default => 'fa-utensils',
                        };
                    @endphp
                    <li>
                        <button onclick="cambiarCategoria({{ $cat->id }}, this, '{{ addslashes($cat->nombre) }}')"
                            class="tpd-cat-btn {{ $cat->id == $categoriaId ? 'active' : '' }}"
                            data-id="{{ $cat->id }}">
                            <span class="tpd-cat-ico"><i class="fa-solid {{ $icono }}"></i></span>
                            {{ $cat->nombre }}
                        </button>
                    </li>
                @endforeach
            </ul>

        </div>

        {{-- ══════════════════════════════════════
         COL 2: PRODUCTOS
    ══════════════════════════════════════ --}}
        <div class="tpd-col-prods">

            {{-- Header productos --}}
            <div class="tpd-prods-header">
                <div class="flex items-center gap-3">
                    <h2 class="tpd-prods-title" id="cat-nombre-actual">
                        {{ $categorias->firstWhere('id', $categoriaId)?->nombre ?? 'Productos' }}
                    </h2>
                    <span class="tpd-prods-count" id="prods-count">
                        {{ $productos->count() }} items
                    </span>
                </div>
                {{-- Selector de mesa --}}
                <div class="tpd-mesa-selector">
                    <span class="tpd-mesa-ico"><i class="fa-solid fa-chair"></i></span>
                    <select id="select-mesa" onchange="cambiarMesa(this.value)" class="tpd-mesa-select">
                        <option value="">Seleccionar Mesa</option>
                        @foreach ($mesas as $mesa)
                            <option value="{{ $mesa->id }}" {{ $mesaActual?->id == $mesa->id ? 'selected' : '' }}>
                                Mesa {{ $mesa->numero }} ({{ ucfirst($mesa->estado) }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Grid de productos --}}
            <div class="tpd-prods-grid" id="prods-grid">
                @foreach ($productos as $prod)
                    <div class="tpd-prod-card" data-nombre="{{ strtolower($prod->nombre) }}">
                        <div class="tpd-prod-img-wrap">
                            @if ($prod->imagen_url)
                                <img src="{{ $prod->imagen_url }}" alt="{{ $prod->nombre }}" class="tpd-prod-img">
                            @else
                                <div class="tpd-prod-img-placeholder"><i class="fa-solid fa-utensils"></i></div>
                            @endif
                            <span class="tpd-prod-precio-badge">
                                S/<br>{{ number_format($prod->precio, 2) }}
                            </span>
                        </div>
                        <div class="tpd-prod-body">
                            <p class="tpd-prod-nombre">{{ $prod->nombre }}</p>
                            @if ($prod->descripcion)
                                <p class="tpd-prod-desc">{{ Str::limit($prod->descripcion, 40) }}</p>
                            @endif
                            <button
                                onclick="abrirModalObs({{ $prod->id }}, '{{ addslashes($prod->nombre) }}', {{ $prod->precio }}, '{{ $prod->imagen_url }}', '{{ addslashes($prod->categoria->nombre) }}')"
                                class="tpd-prod-agregar">
                                <i class="fa-solid fa-plus"></i> Agregar
                            </button>
                        </div>
                    </div>
                @endforeach

                {{-- Sugerencia del día --}}
                <div class="tpd-prod-card sugerencia">
                    <div class="tpd-sugerencia-body">
                        <span class="tpd-sugerencia-ico"><i class="fa-solid fa-circle-info"></i></span>
                        <p class="tpd-sugerencia-title">Sugerencia del Día</p>
                        <p class="tpd-sugerencia-text">Consultar con cocina por platos fuera de carta.</p>
                    </div>
                </div>
            </div>

        </div>

        {{-- ══════════════════════════════════════
         COL 3: RESUMEN DEL PEDIDO
    ══════════════════════════════════════ --}}
        <div class="tpd-col-resumen">

            <div class="tpd-resumen-header">
                <h3 class="tpd-resumen-title">Resumen de Pedido</h3>
                <button onclick="limpiarPedido()" class="tpd-resumen-limpiar" title="Limpiar">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>

            <div class="tpd-resumen-items" id="resumen-items">
                <div class="tpd-resumen-empty">
                    <p><i class="fa-solid fa-cart-shopping"></i></p>
                    <p>Agrega productos al pedido</p>
                </div>
            </div>

            {{-- Datos del cliente --}}
            <div class="tpd-cliente-wrap" id="cliente-wrap" style="{{ $clienteActivo ? 'display:none' : '' }}">
                <p class="tpd-cats-label">DATOS DEL CLIENTE</p>
                <input type="text" id="cliente-nombre" placeholder="Nombre" class="tpd-obs-input"
                    style="margin-bottom:8px">
                <input type="text" id="cliente-apellidos" placeholder="Apellidos" class="tpd-obs-input">
            </div>

            {{-- Observación general --}}
            <div class="tpd-obs-wrap">
                <textarea id="observacion-general" placeholder="Observación general del pedido..." class="tpd-obs-input" rows="2"></textarea>
            </div>

            {{-- Totales --}}
            <div class="tpd-totales">
                <div class="tpd-total-row">
                    <span>Subtotal</span>
                    <span id="subtotal-val">S/ 0.00</span>
                </div>
                <div class="tpd-total-row">
                    <span>IGV (18%)</span>
                    <span id="igv-val">S/ 0.00</span>
                </div>
                <div class="tpd-total-final">
                    <span>Total</span>
                    <span id="total-val">S/ 0.00</span>
                </div>
            </div>

            {{-- Botones --}}
            <div class="tpd-acciones">
                <button onclick="cancelarPedido()" class="tpd-btn-cancelar">Cancelar</button>
                <button onclick="abrirModalConfirmar()" class="tpd-btn-confirmar">
                    <i class="fa-solid fa-play"></i> Confirmar
                </button>
            </div>

        </div>

    </div>


    {{-- ══════════════════════════════════════════════
     MODAL 1: Observaciones del producto
══════════════════════════════════════════════ --}}
    <div id="modal-obs" class="tpd-modal-overlay hidden">
        <div class="tpd-modal">

            {{-- Imagen del producto --}}
            <div class="tpd-modal-img-wrap" id="modal-obs-img-wrap">
                <img id="modal-obs-img" src="" alt="" class="tpd-modal-img hidden">
                <div id="modal-obs-img-placeholder" class="tpd-modal-img-placeholder"><i
                        class="fa-solid fa-utensils"></i>
                </div>
                <div class="tpd-modal-img-overlay">
                    <h3 class="tpd-modal-prod-nombre" id="modal-obs-nombre"></h3>
                </div>
                <button onclick="cerrarModalObs()" class="tpd-modal-close-img">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="tpd-modal-body">

                {{-- Observaciones --}}
                <div class="tpd-modal-section">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-gray-400"><i class="fa-solid fa-comment-dots"></i></span>
                        <p class="text-sm font-semibold text-gray-700">Observaciones especiales</p>
                    </div>
                    <textarea id="obs-producto" rows="3" placeholder="Ej. Sin aji, poco arroz, etc." class="tpd-modal-textarea"></textarea>
                </div>

                {{-- Sugerencias rápidas (ocultas para Bebidas) --}}
                <div class="tpd-modal-section" id="seccion-sugerencias">
                    <p class="tpd-sugerencias-label">SUGERENCIAS RÁPIDAS</p>
                    <div class="tpd-sugerencias-chips">
                        <button onclick="agregarSugerencia('Sin aji')" class="tpd-chip">Sin aji</button>
                        <button onclick="agregarSugerencia('Poco arroz')" class="tpd-chip">Poco arroz</button>
                        <button onclick="agregarSugerencia('Término medio')" class="tpd-chip">Término medio</button>
                        <button onclick="agregarSugerencia('Bien Frito')" class="tpd-chip">Bien Frito</button>
                        <button onclick="agregarSugerencia('Sin cebolla')" class="tpd-chip">Sin cebolla</button>
                        <button onclick="agregarSugerencia('Sin picante')" class="tpd-chip">Sin picante</button>
                    </div>
                </div>

                {{-- Acciones --}}
                <div class="tpd-modal-acciones">
                    <button onclick="cerrarModalObs()" class="tpd-modal-btn-cancelar">Cancelar</button>
                    <button onclick="confirmarAgregarProducto()" class="tpd-modal-btn-confirmar">
                        Confirmar y Agregar
                    </button>
                </div>

            </div>
        </div>
    </div>


    {{-- ══════════════════════════════════════════════
     MODAL 2: Confirmación del pedido
══════════════════════════════════════════════ --}}
    <div id="modal-confirmar" class="tpd-modal-overlay hidden">
        <div class="tpd-modal tpd-modal-confirm">

            <div class="tpd-confirm-header">
                <div class="flex items-center gap-2">
                    <span class="text-purple-600"><i class="fa-solid fa-circle-check"></i></span>
                    <h3 class="tpd-confirm-title" id="confirm-titulo">Confirmar Pedido</h3>
                </div>
                <button onclick="cerrarModalConfirmar()" class="tpd-modal-close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="tpd-confirm-body" id="confirm-body">
                {{-- Se llena con JS --}}
            </div>

            <div class="tpd-confirm-total">
                <span>Total a Pagar</span>
                <span id="confirm-total" class="tpd-confirm-total-val">S/ 0.00</span>
            </div>

            <div class="tpd-confirm-acciones">
                <button onclick="cerrarModalConfirmar()" class="tpd-confirm-btn-volver">VOLVER</button>
                <button onclick="enviarPedido()" class="tpd-confirm-btn-enviar">
                    <i class="fa-solid fa-kitchen-set"></i> ENVIAR A COCINA
                </button>
            </div>

        </div>
    </div>


    {{-- Form oculto para enviar pedido --}}
    <form id="form-pedido" method="POST" action="{{ route('pedidos.store') }}" class="hidden">
        @csrf
        <input type="hidden" name="mesa_id" id="form-mesa-id" value="{{ $mesaActual?->id }}">
        <input type="hidden" name="items" id="form-items">
        <input type="hidden" name="observacion" id="form-observacion">
        <input type="hidden" name="cliente_nombre" id="form-cliente-nombre">
        <input type="hidden" name="cliente_apellidos" id="form-cliente-apellidos">
    </form>


    <script>
        // ── Estado ────────────────────────────────────────────────────────
        let pedido = {};
        let mesaId = {{ $mesaActual?->id ?? 'null' }};
        let prodActual = null;
        let catNombreActual = '{{ addslashes($categorias->firstWhere('id', $categoriaId)?->nombre ?? '') }}';
        let clienteYaAsignado = {{ $clienteActivo ? 'true' : 'false' }};
        const IGV = 0.18;

        // ── Modal Observaciones ───────────────────────────────────────────
        function abrirModalObs(id, nombre, precio, imagenUrl, categoriaNombre) {
            if (!mesaId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selecciona una mesa',
                    text: 'Por favor selecciona una mesa primero.',
                    confirmButtonColor: '#7e22ce'
                });
                return;
            }

            prodActual = {
                id,
                nombre,
                precio: parseFloat(precio)
            };

            // Imagen
            const img = document.getElementById('modal-obs-img');
            const placeholder = document.getElementById('modal-obs-img-placeholder');
            if (imagenUrl && imagenUrl !== 'null' && imagenUrl !== '') {
                img.src = imagenUrl;
                img.classList.remove('hidden');
                placeholder.classList.add('hidden');
            } else {
                img.classList.add('hidden');
                placeholder.classList.remove('hidden');
            }

            document.getElementById('modal-obs-nombre').textContent = nombre;
            document.getElementById('obs-producto').value = '';

            if (pedido[id]) {
                document.getElementById('obs-producto').value = pedido[id].observacion || '';
            }

            // Ocultar sugerencias rápidas si la categoría es Bebidas
            const esBebida = categoriaNombre && categoriaNombre.toLowerCase().includes('bebida');
            const seccionSugerencias = document.getElementById('seccion-sugerencias');
            if (seccionSugerencias) {
                seccionSugerencias.style.display = esBebida ? 'none' : 'block';
            }

            document.getElementById('modal-obs').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function cerrarModalObs() {
            document.getElementById('modal-obs').classList.add('hidden');
            document.body.style.overflow = '';
            prodActual = null;
        }

        function agregarSugerencia(texto) {
            const obs = document.getElementById('obs-producto');
            obs.value = obs.value ? obs.value + ', ' + texto : texto;
        }

        function confirmarAgregarProducto() {
            if (!prodActual) return;
            const obs = document.getElementById('obs-producto').value;

            if (pedido[prodActual.id]) {
                pedido[prodActual.id].cantidad++;
                pedido[prodActual.id].observacion = obs;
            } else {
                pedido[prodActual.id] = {
                    nombre: prodActual.nombre,
                    precio: prodActual.precio,
                    cantidad: 1,
                    observacion: obs,
                };
            }

            cerrarModalObs();
            renderResumen();
        }

        // ── Resumen ───────────────────────────────────────────────────────
        function renderResumen() {
            const container = document.getElementById('resumen-items');
            const items = Object.entries(pedido);

            if (items.length === 0) {
                container.innerHTML = `
            <div class="tpd-resumen-empty">
                <p><i class="fa-solid fa-cart-shopping"></i></p>
                <p>Agrega productos al pedido</p>
            </div>`;
                actualizarTotales(0);
                return;
            }

            container.innerHTML = '';
            items.forEach(([id, item]) => {
                const div = document.createElement('div');
                div.className = 'tpd-resumen-item';
                div.innerHTML = `
            <div class="tpd-ri-info">
                <p class="tpd-ri-nombre">${item.nombre}</p>
                <p class="tpd-ri-precio">S/ ${(item.precio * item.cantidad).toFixed(2)}</p>
                ${item.observacion ? `<p class="tpd-ri-obs">${item.observacion}</p>` : ''}
            </div>
            <div class="tpd-ri-qty">
                <button onclick="cambiarCantidad(${id}, -1)" class="tpd-qty-btn">−</button>
                <span class="tpd-qty-val">${item.cantidad}</span>
                <button onclick="cambiarCantidad(${id}, +1)" class="tpd-qty-btn">+</button>
            </div>
        `;
                container.appendChild(div);
            });

            const sumaLineas = items.reduce((s, [, i]) => s + i.precio * i.cantidad, 0);
            actualizarTotales(sumaLineas);
        }

        function cambiarCantidad(id, delta) {
            if (!pedido[id]) return;
            pedido[id].cantidad += delta;
            if (pedido[id].cantidad <= 0) delete pedido[id];
            renderResumen();
        }

        // Los precios de los productos YA incluyen IGV (igual que en Caja):
        // el total es la suma directa de líneas, y subtotal/IGV se derivan de ahí.
        function actualizarTotales(sumaLineas) {
            const total = sumaLineas;
            const subtotal = total / (1 + IGV);
            const igv = total - subtotal;
            document.getElementById('subtotal-val').textContent = `S/ ${subtotal.toFixed(2)}`;
            document.getElementById('igv-val').textContent = `S/ ${igv.toFixed(2)}`;
            document.getElementById('total-val').textContent = `S/ ${total.toFixed(2)}`;
        }

        function limpiarPedido() {
            if (Object.keys(pedido).length === 0) return;
            Swal.fire({
                title: '¿Limpiar pedido?',
                text: 'Se eliminarán todos los productos agregados.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, limpiar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    pedido = {};
                    renderResumen();
                }
            });
        }

        // ── Modal Confirmación ────────────────────────────────────────────
        function abrirModalConfirmar() {
            if (!mesaId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selecciona una mesa',
                    confirmButtonColor: '#7e22ce'
                });
                return;
            }
            const items = Object.entries(pedido);
            if (items.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pedido vacío',
                    text: 'Agrega al menos un producto.',
                    confirmButtonColor: '#7e22ce'
                });
                return;
            }
            if (!clienteYaAsignado) {
                const nombre = document.getElementById('cliente-nombre').value.trim();
                const apellidos = document.getElementById('cliente-apellidos').value.trim();
                if (!nombre || !apellidos) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Datos del cliente requeridos',
                        text: 'Ingresa nombre y apellidos del cliente.',
                        confirmButtonColor: '#7e22ce'
                    });
                    return;
                }
            }

            const mesaSelect = document.getElementById('select-mesa');
            const mesaNombre = mesaSelect.options[mesaSelect.selectedIndex]?.text || '';
            document.getElementById('confirm-titulo').textContent =
                `Confirmar Pedido — ${mesaNombre.split(' ').slice(0,2).join(' ')}`;

            let html = '';
            let sumaLineas = 0;
            items.forEach(([id, item]) => {
                const linea = item.precio * item.cantidad;
                sumaLineas += linea;
                html += `
            <div class="tpd-confirm-item">
                <div class="tpd-confirm-item-left">
                    <span class="tpd-confirm-qty">${item.cantidad}x</span>
                    <div>
                        <p class="tpd-confirm-nombre">${item.nombre}</p>
                        ${item.observacion ? `<p class="tpd-confirm-obs"><i class="fa-solid fa-comment-dots"></i> ${item.observacion}</p>` : ''}
                    </div>
                </div>
                <span class="tpd-confirm-precio">S/ ${linea.toFixed(2)}</span>
            </div>
        `;
            });
            document.getElementById('confirm-body').innerHTML = html;

            // Los precios ya incluyen IGV — el total a pagar es la suma directa
            const total = sumaLineas;
            document.getElementById('confirm-total').textContent = `S/ ${total.toFixed(2)}`;

            document.getElementById('modal-confirmar').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function cerrarModalConfirmar() {
            document.getElementById('modal-confirmar').classList.add('hidden');
            document.body.style.overflow = '';
        }

        // ── Enviar pedido ─────────────────────────────────────────────────
        function enviarPedido() {
            const items = Object.entries(pedido).map(([id, item]) => ({
                producto_id: parseInt(id),
                cantidad: item.cantidad,
                observacion: item.observacion || '',
            }));

            document.getElementById('form-mesa-id').value = mesaId;
            document.getElementById('form-items').value = JSON.stringify(items);
            document.getElementById('form-observacion').value = document.getElementById('observacion-general').value;

            if (!clienteYaAsignado) {
                document.getElementById('form-cliente-nombre').value = document.getElementById('cliente-nombre').value
                .trim();
                document.getElementById('form-cliente-apellidos').value = document.getElementById('cliente-apellidos').value
                    .trim();
            }

            document.getElementById('form-pedido').submit();
        }

        function cancelarPedido() {
            if (Object.keys(pedido).length > 0) {
                Swal.fire({
                    title: '¿Cancelar pedido?',
                    text: 'Se perderán los productos agregados.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Sí, cancelar',
                    cancelButtonText: 'Volver'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '{{ route('pedidos.index') }}';
                    }
                });
                return;
            }
            window.location.href = '{{ route('pedidos.index') }}';
        }

        // ── Cambiar mesa ──────────────────────────────────────────────────
        function cambiarMesa(id) {
            mesaId = id ? parseInt(id) : null;
            document.getElementById('form-mesa-id').value = mesaId || '';

            const wrap = document.getElementById('cliente-wrap');
            if (!mesaId) {
                clienteYaAsignado = false;
                wrap.style.display = '';
                return;
            }

            fetch(`/pedidos/mesa/${mesaId}/cliente-activo`)
                .then(r => r.json())
                .then(data => {
                    clienteYaAsignado = data.tiene_cliente;
                    wrap.style.display = data.tiene_cliente ? 'none' : '';
                });
        }

        // ── Cambiar categoría AJAX ────────────────────────────────────────
        function cambiarCategoria(catId, btn, nombreCategoria) {
            document.querySelectorAll('.tpd-cat-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            catNombreActual = nombreCategoria;
            document.getElementById('cat-nombre-actual').textContent = nombreCategoria;

            fetch(`/pedidos/productos?categoria_id=${catId}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => r.json())
                .then(productos => renderProductos(productos));
        }

        function renderProductos(productos) {
            const grid = document.getElementById('prods-grid');
            document.getElementById('prods-count').textContent = `${productos.length} items`;
            grid.innerHTML = '';

            productos.forEach(p => {
                const div = document.createElement('div');
                div.className = 'tpd-prod-card';
                div.dataset.nombre = p.nombre.toLowerCase();
                div.innerHTML = `
            <div class="tpd-prod-img-wrap">
                ${p.imagen_url
                    ? `<img src="${p.imagen_url}" alt="${p.nombre}" class="tpd-prod-img">`
                    : `<div class="tpd-prod-img-placeholder"><i class="fa-solid fa-utensils"></i></div>`}
                <span class="tpd-prod-precio-badge">S/<br>${parseFloat(p.precio).toFixed(2)}</span>
            </div>
            <div class="tpd-prod-body">
                <p class="tpd-prod-nombre">${p.nombre}</p>
                ${p.descripcion ? `<p class="tpd-prod-desc">${p.descripcion.substring(0,40)}</p>` : ''}
                <button onclick="abrirModalObs(${p.id}, '${p.nombre.replace(/'/g,"\\'")}', ${p.precio}, '${p.imagen_url || ''}', '${catNombreActual.replace(/'/g,"\\'")}')"
                        class="tpd-prod-agregar"><i class="fa-solid fa-plus"></i> Agregar</button>
            </div>
        `;
                grid.appendChild(div);
            });

            // Sugerencia del día
            const sug = document.createElement('div');
            sug.className = 'tpd-prod-card sugerencia';
            sug.innerHTML = `
        <div class="tpd-sugerencia-body">
            <span class="tpd-sugerencia-ico"><i class="fa-solid fa-circle-info"></i></span>
            <p class="tpd-sugerencia-title">Sugerencia del Día</p>
            <p class="tpd-sugerencia-text">Consultar con cocina por platos fuera de carta.</p>
        </div>`;
            grid.appendChild(sug);
        }

        function buscarProducto(q) {
            const term = q.toLowerCase();
            document.querySelectorAll('.tpd-prod-card:not(.sugerencia)').forEach(card => {
                card.style.display = (card.dataset.nombre || '').includes(term) ? '' : 'none';
            });
        }

        // Cerrar modales al click fuera
        document.getElementById('modal-obs').addEventListener('click', function(e) {
            if (e.target === this) cerrarModalObs();
        });
        document.getElementById('modal-confirmar').addEventListener('click', function(e) {
            if (e.target === this) cerrarModalConfirmar();
        });
    </script>

@endsection