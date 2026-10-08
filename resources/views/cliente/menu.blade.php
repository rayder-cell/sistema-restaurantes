<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $restaurante->nombre }} — Menú Digital</title>
    @vite(['resources/assets/css/pages/cliente.css', 'resources/assets/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="cli-body">

{{-- ══════════════════════════════════════
     HEADER
══════════════════════════════════════ --}}
<header class="cli-header">
    <div class="cli-header-inner">
        <h1 class="cli-logo">{{ $restaurante->nombre }}</h1>
        <button onclick="abrirCarrito()" class="cli-carrito-btn" id="btn-carrito">
            <i class="fa-solid fa-bag-shopping"></i>
            Pedir Ahora
            <span class="cli-carrito-count hidden" id="carrito-count">0</span>
        </button>
    </div>
</header>

{{-- ══════════════════════════════════════
     HERO
══════════════════════════════════════ --}}
<section class="cli-hero">
    <div class="cli-hero-content">
        <h2 class="cli-hero-title">Sabores que cuentan historias</h2>
        <p class="cli-hero-desc">
            Descubre nuestra propuesta gastronómica donde la tradición se encuentra
            con la innovación en cada bocado. Ingredientes frescos de la región
            seleccionados con amor.
        </p>
    </div>
    <div class="cli-hero-mesa">
        <span class="cli-mesa-badge">
            <i class="fa-solid fa-chair"></i> Mesa {{ $mesa->numero }}
        </span>
    </div>
</section>

{{-- ══════════════════════════════════════
     FILTROS POR CATEGORÍA
══════════════════════════════════════ --}}
<div class="cli-cats-wrap">
    <div class="cli-cats" id="cats-nav">
        @foreach($categorias as $cat)
            <button onclick="scrollToCategoria('cat-{{ $cat->id }}', this)"
                    class="cli-cat-pill {{ $loop->first ? 'active' : '' }}">
                {{ $cat->nombre }}
            </button>
        @endforeach
    </div>
</div>

{{-- ══════════════════════════════════════
     PRODUCTOS POR CATEGORÍA
══════════════════════════════════════ --}}
<main class="cli-main">
    @foreach($categorias as $cat)
        @if(isset($productos[$cat->id]) && $productos[$cat->id]->count() > 0)
        <section class="cli-cat-section" id="cat-{{ $cat->id }}">
            <h3 class="cli-cat-titulo">{{ $cat->nombre }}</h3>

            @foreach($productos[$cat->id] as $prod)
            <div class="cli-prod-card {{ !$prod->disponible ? 'agotado' : '' }}"
                 id="prod-card-{{ $prod->id }}">

                {{-- Imagen --}}
                @if($prod->imagen_url)
                <div class="cli-prod-img-wrap">
                    <img src="{{ $prod->imagen_url }}" alt="{{ $prod->nombre }}" class="cli-prod-img">
                    @if(!$prod->disponible)
                        <div class="cli-agotado-overlay">Agotado</div>
                    @endif
                </div>
                @endif

                {{-- Info --}}
                <div class="cli-prod-info">
                    <div class="cli-prod-top">
                        <h4 class="cli-prod-nombre">{{ $prod->nombre }}</h4>
                        <span class="cli-prod-precio">S/. {{ number_format($prod->precio, 2) }}</span>
                    </div>
                    @if($prod->descripcion)
                        <p class="cli-prod-desc">{{ Str::limit($prod->descripcion, 80) }}</p>
                    @endif

                    @if($prod->disponible)
                        {{-- Contador inline (visible cuando hay items en carrito) --}}
                        <div class="cli-prod-qty hidden" id="qty-{{ $prod->id }}">
                            <button onclick="cambiarCantidad({{ $prod->id }}, -1)" class="cli-qty-inline-btn">
                                <i class="fa-solid fa-minus"></i>
                            </button>
                            <span class="cli-qty-inline-val" id="qty-val-{{ $prod->id }}">0</span>
                            <button onclick="cambiarCantidad({{ $prod->id }}, 1)" class="cli-qty-inline-btn">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                        <button onclick="agregarAlCarrito({{ $prod->id }}, '{{ addslashes($prod->nombre) }}', {{ $prod->precio }})"
                                class="cli-btn-agregar" id="btn-agregar-{{ $prod->id }}">
                            <i class="fa-solid fa-cart-plus"></i> Agregar al pedido
                        </button>
                    @else
                        <button class="cli-btn-agotado" disabled>
                            <i class="fa-solid fa-ban"></i> Temporalmente agotado
                        </button>
                    @endif
                </div>

            </div>
            @endforeach
        </section>
        @endif
    @endforeach
</main>

{{-- ══════════════════════════════════════
     FOOTER
══════════════════════════════════════ --}}
<footer class="cli-footer">
    <h3 class="cli-footer-logo">{{ $restaurante->nombre }}</h3>
    <div class="cli-footer-links">
        <a href="#">Privacidad</a>
        <a href="#">Términos</a>
        <a href="#">Soporte</a>
    </div>
    <p class="cli-footer-copy">© {{ date('Y') }} {{ $restaurante->nombre }}. Todos los derechos reservados.</p>
</footer>

{{-- ══════════════════════════════════════
     BOTÓN FLOTANTE DEL CARRITO
══════════════════════════════════════ --}}
<button onclick="abrirCarrito()" class="cli-fab hidden" id="fab-carrito">
    <i class="fa-solid fa-bag-shopping"></i>
    <span class="cli-fab-count" id="fab-count">0</span>
    <span class="cli-fab-total" id="fab-total">S/ 0.00</span>
</button>

{{-- ══════════════════════════════════════
     CARRITO (DRAWER)
══════════════════════════════════════ --}}
<div id="carrito-overlay" class="cli-carrito-overlay hidden" onclick="cerrarCarrito()"></div>

<div id="carrito-drawer" class="cli-carrito-drawer translate-y-full">

    {{-- Header --}}
    <div class="cli-carrito-header">
        <div class="flex items-center gap-3">
            <i class="fa-solid fa-bag-shopping" style="color:#4F378A; font-size:18px;"></i>
            <h3 class="cli-carrito-title">Tu Pedido</h3>
            <span class="cli-carrito-badge" id="drawer-count">0 items</span>
        </div>
        <button onclick="cerrarCarrito()" class="cli-carrito-close">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    {{-- Mesa info --}}
    <div class="cli-carrito-mesa-info">
        <i class="fa-solid fa-chair"></i>
        Mesa {{ $mesa->numero }} — {{ $restaurante->nombre }}
    </div>

    {{-- Items --}}
    <div class="cli-carrito-items" id="carrito-items">
        <div class="cli-carrito-empty" id="carrito-empty">
            <i class="fa-solid fa-bag-shopping" style="font-size:40px; color:#d1d5db; display:block; margin-bottom:10px;"></i>
            <p style="color:#9ca3af; font-size:14px;">Tu carrito está vacío</p>
            <p style="color:#d1d5db; font-size:12px; margin-top:4px;">Agrega productos del menú</p>
        </div>
    </div>

    {{-- Observación --}}
    <div class="cli-carrito-obs">
        <label style="font-size:12px; color:#6b7280; font-weight:500; display:block; margin-bottom:6px;">
            <i class="fa-solid fa-note-sticky"></i> Indicaciones especiales
        </label>
        <textarea id="obs-pedido"
                  placeholder="Sin cebolla, sin picante, alérgico a mariscos..."
                  class="cli-obs-input" rows="2"></textarea>
    </div>

    {{-- Totales --}}
    <div class="cli-carrito-totales">
        <div class="cli-total-row">
            <span><i class="fa-solid fa-receipt" style="margin-right:6px; color:#9ca3af;"></i>Subtotal</span>
            <span id="cli-subtotal">S/ 0.00</span>
        </div>
        <div class="cli-total-row">
            <span><i class="fa-solid fa-percent" style="margin-right:6px; color:#9ca3af;"></i>IGV (18%)</span>
            <span id="cli-igv">S/ 0.00</span>
        </div>
        <div class="cli-total-final">
            <span><i class="fa-solid fa-coins" style="margin-right:6px;"></i>Total</span>
            <span id="cli-total">S/ 0.00</span>
        </div>
    </div>

    {{-- Acciones --}}
    <div class="cli-carrito-acciones">
        <button onclick="cerrarCarrito()" class="cli-btn-seguir">
            <i class="fa-solid fa-arrow-left"></i> Seguir viendo
        </button>
        <button onclick="confirmarPedido()" class="cli-btn-confirmar">
            <i class="fa-solid fa-check"></i> Confirmar Pedido
        </button>
    </div>

</div>

{{-- Form oculto --}}
<form id="form-pedido" method="POST" action="{{ route('cliente.pedido', $mesa->qr_token) }}" class="hidden">
    @csrf
    <input type="hidden" name="items" id="form-items">
    <input type="hidden" name="observacion" id="form-obs">
</form>

<style>
/* ── Botón flotante FAB ─────────────────── */
.cli-fab {
    position: fixed;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    align-items: center;
    gap: 10px;
    background: #4F378A;
    color: white;
    border: none;
    border-radius: 50px;
    padding: 12px 22px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(79,55,138,0.4);
    z-index: 50;
    transition: transform 0.2s, box-shadow 0.2s;
    white-space: nowrap;
}
.cli-fab:hover { box-shadow: 0 6px 24px rgba(79,55,138,0.5); transform: translateX(-50%) translateY(-2px); }
.cli-fab-count {
    background: white;
    color: #4F378A;
    border-radius: 50px;
    padding: 1px 8px;
    font-size: 12px;
    font-weight: 700;
}
.cli-fab-total { font-size: 13px; opacity: 0.9; }

/* ── Mesa info en drawer ───────────────── */
.cli-carrito-mesa-info {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: #F2ECF4;
    color: #4F378A;
    font-size: 13px;
    font-weight: 500;
    border-bottom: 0.5px solid #e5e7eb;
}

/* ── Badge count en header drawer ─────── */
.cli-carrito-badge {
    background: #EDE9FE;
    color: #4F378A;
    font-size: 11px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 20px;
}

/* ── Items del carrito mejorados ────────── */
.cli-carrito-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    border-bottom: 0.5px solid #f3f4f6;
    animation: fadeSlide 0.2s ease;
}
@keyframes fadeSlide {
    from { opacity: 0; transform: translateY(-8px); }
    to   { opacity: 1; transform: translateY(0); }
}
.cli-ci-info { flex: 1; min-width: 0; }
.cli-ci-nombre { font-size: 14px; font-weight: 500; color: #1f2937; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cli-ci-precio { font-size: 13px; color: #4F378A; font-weight: 600; margin-top: 2px; }
.cli-ci-unit   { font-size: 11px; color: #9ca3af; }

.cli-ci-qty {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f9fafb;
    border: 0.5px solid #e5e7eb;
    border-radius: 8px;
    padding: 4px 8px;
}
.cli-qty-btn {
    width: 26px;
    height: 26px;
    border-radius: 6px;
    border: none;
    background: white;
    color: #4F378A;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    transition: background 0.1s;
}
.cli-qty-btn:hover { background: #EDE9FE; }
.cli-qty-btn.delete { color: #dc2626; }
.cli-qty-val { font-size: 14px; font-weight: 600; color: #1f2937; min-width: 20px; text-align: center; }

/* ── Contador inline en la card ─────────── */
.cli-prod-qty {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
}
.cli-qty-inline-btn {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    border: 1.5px solid #4F378A;
    background: white;
    color: #4F378A;
    font-size: 13px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s;
}
.cli-qty-inline-btn:hover { background: #4F378A; color: white; }
.cli-qty-inline-val {
    font-size: 16px;
    font-weight: 700;
    color: #4F378A;
    min-width: 24px;
    text-align: center;
}
</style>

<script>
let carrito = {};
const IGV = 0.18;

// ── Agregar al carrito ─────────────────────────────────────────
function agregarAlCarrito(id, nombre, precio) {
    if (carrito[id]) {
        carrito[id].cantidad++;
    } else {
        carrito[id] = { nombre, precio: parseFloat(precio), cantidad: 1 };
    }
    renderCarrito();
    actualizarCardProducto(id);
    abrirCarrito();
}

// ── Cambiar cantidad ───────────────────────────────────────────
function cambiarCantidad(id, delta) {
    if (!carrito[id]) return;
    carrito[id].cantidad += delta;
    if (carrito[id].cantidad <= 0) delete carrito[id];
    renderCarrito();
    actualizarCardProducto(id);
}

// ── Actualizar card del producto en el menú ─────────────────────
function actualizarCardProducto(id) {
    const qtyWrap  = document.getElementById(`qty-${id}`);
    const btnAgregar = document.getElementById(`btn-agregar-${id}`);
    const qtyVal   = document.getElementById(`qty-val-${id}`);

    if (!qtyWrap || !btnAgregar) return;

    if (carrito[id] && carrito[id].cantidad > 0) {
        qtyWrap.classList.remove('hidden');
        btnAgregar.innerHTML = '<i class="fa-solid fa-cart-plus"></i> Agregar más';
        if (qtyVal) qtyVal.textContent = carrito[id].cantidad;
    } else {
        qtyWrap.classList.add('hidden');
        btnAgregar.innerHTML = '<i class="fa-solid fa-cart-plus"></i> Agregar al pedido';
    }
}

// ── Render carrito ─────────────────────────────────────────────
function renderCarrito() {
    const container = document.getElementById('carrito-items');
    const empty     = document.getElementById('carrito-empty');
    const items     = Object.entries(carrito);
    const count     = items.reduce((s, [, i]) => s + i.cantidad, 0);
    const subtotal  = items.reduce((s, [, i]) => s + i.precio * i.cantidad, 0);

    // Badge header
    const badge = document.getElementById('carrito-count');
    const drawerCount = document.getElementById('drawer-count');
    if (count > 0) {
        badge.textContent = count;
        badge.classList.remove('hidden');
    } else {
        badge.classList.add('hidden');
    }
    if (drawerCount) drawerCount.textContent = `${count} item${count !== 1 ? 's' : ''}`;

    // FAB flotante
    const fab = document.getElementById('fab-carrito');
    const fabCount = document.getElementById('fab-count');
    const fabTotal = document.getElementById('fab-total');
    if (count > 0) {
        fab.classList.remove('hidden');
        fabCount.textContent = count;
        fabTotal.textContent = `S/ ${subtotal.toFixed(2)}`;
    } else {
        fab.classList.add('hidden');
    }

    if (items.length === 0) {
        container.innerHTML = '';
        container.appendChild(empty);
        actualizarTotales(0);
        return;
    }

    container.innerHTML = '';
    items.forEach(([id, item]) => {
        const div = document.createElement('div');
        div.className = 'cli-carrito-item';
        div.id = `ci-${id}`;
        div.innerHTML = `
            <div class="cli-ci-info">
                <p class="cli-ci-nombre">${item.nombre}</p>
                <p class="cli-ci-unit">S/ ${item.precio.toFixed(2)} c/u</p>
                <p class="cli-ci-precio">S/ ${(item.precio * item.cantidad).toFixed(2)}</p>
            </div>
            <div class="cli-ci-qty">
                <button onclick="cambiarCantidad(${id}, -1)"
                        class="cli-qty-btn ${item.cantidad === 1 ? 'delete' : ''}"
                        title="${item.cantidad === 1 ? 'Quitar' : 'Reducir'}">
                    <i class="fa-solid fa-${item.cantidad === 1 ? 'trash' : 'minus'}"></i>
                </button>
                <span class="cli-qty-val">${item.cantidad}</span>
                <button onclick="cambiarCantidad(${id}, 1)" class="cli-qty-btn" title="Añadir">
                    <i class="fa-solid fa-plus"></i>
                </button>
            </div>
        `;
        container.appendChild(div);
    });

    actualizarTotales(subtotal);
}

function actualizarTotales(subtotal) {
    const igv   = subtotal * IGV;
    const total = subtotal + igv;
    document.getElementById('cli-subtotal').textContent = `S/ ${subtotal.toFixed(2)}`;
    document.getElementById('cli-igv').textContent      = `S/ ${igv.toFixed(2)}`;
    document.getElementById('cli-total').textContent    = `S/ ${total.toFixed(2)}`;
}

// ── Carrito drawer ─────────────────────────────────────────────
function abrirCarrito() {
    document.getElementById('carrito-overlay').classList.remove('hidden');
    document.getElementById('carrito-drawer').classList.remove('translate-y-full');
    document.body.style.overflow = 'hidden';
}
function cerrarCarrito() {
    document.getElementById('carrito-overlay').classList.add('hidden');
    document.getElementById('carrito-drawer').classList.add('translate-y-full');
    document.body.style.overflow = '';
}

// ── Confirmar pedido ───────────────────────────────────────────
function confirmarPedido() {
    const items = Object.entries(carrito);
    if (items.length === 0) {
        alert('Agrega al menos un producto a tu pedido.');
        return;
    }
    const itemsData = items.map(([id, item]) => ({
        producto_id: parseInt(id),
        cantidad:    item.cantidad,
    }));
    document.getElementById('form-items').value = JSON.stringify(itemsData);
    document.getElementById('form-obs').value   = document.getElementById('obs-pedido').value;
    document.getElementById('form-pedido').submit();
}

// ── Scroll a categoría ─────────────────────────────────────────
function scrollToCategoria(id, btn) {
    document.querySelectorAll('.cli-cat-pill').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const el = document.getElementById(id);
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
}
</script>

</body>
</html>