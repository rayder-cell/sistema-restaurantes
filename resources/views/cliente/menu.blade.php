<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $restaurante->nombre }} — Menú Digital</title>
    @vite(['resources/assets/css/pages/cliente.css', 'resources/assets/js/app.js'])
</head>
<body class="cli-body">

{{-- ══════════════════════════════════════
     HEADER
══════════════════════════════════════ --}}
<header class="cli-header">
    <div class="cli-header-inner">
        <h1 class="cli-logo">{{ $restaurante->nombre }}</h1>
        <button onclick="abrirCarrito()" class="cli-carrito-btn">
            Pedir Ahora
            @if(true)
                <span class="cli-carrito-count hidden" id="carrito-count">0</span>
            @endif
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
        <span class="cli-mesa-badge">🪑 Mesa {{ $mesa->numero }}</span>
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
            <div class="cli-prod-card {{ !$prod->disponible ? 'agotado' : '' }}">

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
                        <button onclick="agregarAlCarrito({{ $prod->id }}, '{{ addslashes($prod->nombre) }}', {{ $prod->precio }})"
                                class="cli-btn-agregar">
                            🛒 Agregar al pedido
                        </button>
                    @else
                        <button class="cli-btn-agotado" disabled>
                            Temporalmente agotado
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
     CARRITO (DRAWER)
══════════════════════════════════════ --}}
<div id="carrito-overlay" class="cli-carrito-overlay hidden" onclick="cerrarCarrito()"></div>
<div id="carrito-drawer" class="cli-carrito-drawer translate-y-full">

    <div class="cli-carrito-header">
        <h3 class="cli-carrito-title">Tu Pedido</h3>
        <button onclick="cerrarCarrito()" class="cli-carrito-close">✕</button>
    </div>

    <div class="cli-carrito-items" id="carrito-items">
        <div class="cli-carrito-empty" id="carrito-empty">
            <p>🛒</p>
            <p>Tu carrito está vacío</p>
        </div>
    </div>

    <div class="cli-carrito-obs">
        <textarea id="obs-pedido" placeholder="¿Alguna indicación especial? (Sin cebolla, sin picante...)"
                  class="cli-obs-input" rows="2"></textarea>
    </div>

    <div class="cli-carrito-totales">
        <div class="cli-total-row">
            <span>Subtotal</span>
            <span id="cli-subtotal">S/ 0.00</span>
        </div>
        <div class="cli-total-row">
            <span>IGV (18%)</span>
            <span id="cli-igv">S/ 0.00</span>
        </div>
        <div class="cli-total-final">
            <span>Total</span>
            <span id="cli-total">S/ 0.00</span>
        </div>
    </div>

    <div class="cli-carrito-acciones">
        <button onclick="cerrarCarrito()" class="cli-btn-seguir">Seguir viendo</button>
        <button onclick="confirmarPedido()" class="cli-btn-confirmar">
            Confirmar Pedido →
        </button>
    </div>

</div>

{{-- Form oculto para enviar pedido --}}
<form id="form-pedido" method="POST" action="{{ route('cliente.pedido', $mesa->qr_token) }}" class="hidden">
    @csrf
    <input type="hidden" name="items" id="form-items">
    <input type="hidden" name="observacion" id="form-obs">
</form>


<script>
let carrito = {}; // { id: { nombre, precio, cantidad } }
const IGV = 0.18;

// ── Agregar al carrito ─────────────────────────────────────────
function agregarAlCarrito(id, nombre, precio) {
    if (carrito[id]) {
        carrito[id].cantidad++;
    } else {
        carrito[id] = { nombre, precio: parseFloat(precio), cantidad: 1 };
    }
    renderCarrito();
    abrirCarrito();
}

// ── Cambiar cantidad ───────────────────────────────────────────
function cambiarCantidad(id, delta) {
    if (!carrito[id]) return;
    carrito[id].cantidad += delta;
    if (carrito[id].cantidad <= 0) delete carrito[id];
    renderCarrito();
}

// ── Render carrito ─────────────────────────────────────────────
function renderCarrito() {
    const container = document.getElementById('carrito-items');
    const empty     = document.getElementById('carrito-empty');
    const items     = Object.entries(carrito);
    const count     = items.reduce((s, [, i]) => s + i.cantidad, 0);

    // Badge contador
    const badge = document.getElementById('carrito-count');
    if (count > 0) {
        badge.textContent = count;
        badge.classList.remove('hidden');
    } else {
        badge.classList.add('hidden');
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
        div.innerHTML = `
            <div class="cli-ci-info">
                <p class="cli-ci-nombre">${item.nombre}</p>
                <p class="cli-ci-precio">S/. ${(item.precio * item.cantidad).toFixed(2)}</p>
            </div>
            <div class="cli-ci-qty">
                <button onclick="cambiarCantidad(${id}, -1)" class="cli-qty-btn">−</button>
                <span class="cli-qty-val">${item.cantidad}</span>
                <button onclick="cambiarCantidad(${id}, +1)" class="cli-qty-btn">+</button>
            </div>
        `;
        container.appendChild(div);
    });

    const subtotal = items.reduce((s, [, i]) => s + i.precio * i.cantidad, 0);
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
