<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $restaurante->nombre }} — MIKHUNAWASI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/assets/css/pages/landing.css', 'resources/assets/css/pages/restaurante-show.css'])

</head>

<body>

    <nav>
        <a href="{{ route('buscar') }}" class="nav-logo">
            <i class="fa-solid fa-utensils"></i>
            MIKHUNAWASI
        </a>

        <button class="nav-toggle" id="navToggle" aria-label="Abrir menú">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="nav-links" id="navLinks">
            <a href="{{ route('buscar') }}">Inicio</a>
            <a href="{{ route('restaurantes.index') }}">Restaurantes</a>
            <a href="{{ route('platos.index') }}">Platos</a>
            <a href="#">Experiencias</a>
            <a href="#">Blog</a>
            <a href="{{ route('cliente.login') }}" class="nav-btn"><i class="fa-solid fa-user"></i> Ingresar</a>
        </div>
    </nav>

    <div style="padding: 14px 5%; background:#fff; border-bottom:1px solid var(--gris-brd, #e5e3dc);">
        <a href="{{ route('restaurantes.index') }}" style="display:inline-flex; align-items:center; gap:6px; color:var(--verde, #1b3a2d); text-decoration:none; font-size:14px; font-weight:600;">
            <i class="fa-solid fa-arrow-left"></i> Volver a restaurantes
        </a>
    </div>

    <!-- HEADER -->
    <div class="rest-header"
        style="background-image: url('{{ $restaurante->logo_url ?? '' }}'); background-color: var(--verde-dk, #0f3d26);">
        <div class="rest-header-overlay"></div>
        <div class="rest-header-content">
            <h1>{{ $restaurante->nombre }}</h1>
            <div class="rest-header-meta">
                <span><i class="fa-solid fa-location-dot"></i> {{ $restaurante->direccion ?? 'Dirección no registrada' }}</span>
            </div>
        </div>
    </div>

    <!-- TABS -->
    <div class="rest-tabs">
        <button class="rest-tab-btn active" data-tab="resumen">Resumen</button>
        <button class="rest-tab-btn" data-tab="ubicacion">Ubicación</button>
        <button class="rest-tab-btn" data-tab="platos">Platos populares</button>
        <button class="rest-tab-btn" data-tab="galeria">Galería</button>
    </div>

    <!-- PANEL: RESUMEN -->
    <div class="rest-tab-panel active" id="tab-resumen">
        <h2 style="margin-top:0;">Acerca de {{ $restaurante->nombre }}</h2>
        <p style="color:#4b5563; line-height:1.7;">
            {{ $restaurante->descripcion ?? 'Este restaurante aún no agregó una descripción.' }}
        </p>
        <button class="btn-reservar" style="max-width:260px;" onclick="abrirModalReserva()">
            <i class="fa-solid fa-circle-check"></i> Reservar mesa
        </button>
    </div>

    <!-- PANEL: UBICACIÓN -->
    <div class="rest-tab-panel" id="tab-ubicacion">
        <h2 style="margin-top:0;">Ubicación</h2>
        @if($restaurante->mapa_embed_url)
            <iframe class="rest-map-frame" src="{{ $restaurante->mapa_embed_url }}"
                allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        @else
            <div class="rest-productos-empty">
                <i class="fa-solid fa-map-location-dot" style="font-size:32px; margin-bottom:12px; display:block;"></i>
                Este restaurante aún no configuró su ubicación en el mapa.
            </div>
        @endif
        <p style="margin-top:16px; color:#4b5563;">
            <i class="fa-solid fa-location-dot"></i> {{ $restaurante->direccion ?? 'Dirección no registrada' }}
        </p>
    </div>

    <!-- PANEL: PLATOS -->
    <div class="rest-tab-panel" id="tab-platos">
        <h2 style="margin-top:0;">Platos populares</h2>
        @if($restaurante->productos->isEmpty())
            <div class="rest-productos-empty">
                <i class="fa-solid fa-bowl-food" style="font-size:32px; margin-bottom:12px; display:block;"></i>
                Aún no hay platos disponibles.
            </div>
        @else
            <div class="platos-grid">
                @foreach($restaurante->productos as $p)
                    <div class="plato-card">
                        @if($p->imagen_url)
                            <img src="{{ $p->imagen_url }}" class="plato-img-wrap" style="object-fit:cover;" alt="{{ $p->nombre }}">
                        @else
                            <div class="plato-img-wrap"><i class="fa-solid fa-bowl-food"></i></div>
                        @endif
                        <div class="plato-body">
                            <p class="plato-nombre">{{ $p->nombre }}</p>
                            <p class="plato-precio">S/ {{ number_format($p->precio, 2) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- PANEL: GALERÍA -->
    <div class="rest-tab-panel" id="tab-galeria">
        <h2 style="margin-top:0;">Galería</h2>
        @if($restaurante->galeria->isEmpty())
            <div class="rest-galeria-empty">
                <i class="fa-solid fa-images" style="font-size:32px; margin-bottom:12px; display:block;"></i>
                Este restaurante aún no subió fotos a su galería.
            </div>
        @else
            <div class="rest-galeria-grid">
                @foreach($restaurante->galeria as $foto)
                    <img src="{{ $foto->imagen_url }}" alt="Foto de {{ $restaurante->nombre }}">
                @endforeach
            </div>
        @endif
    </div>

    <!-- MODAL RESERVA (reutilizado del landing) -->
    <div class="modal-overlay" id="modal-overlay" onclick="cerrarModal(event)">
        <div class="modal">
            <div class="modal-header">
                <div>
                    <h3 id="modal-title">Reservar en {{ $restaurante->nombre }}</h3>
                    <p class="modal-header-sub">Confirmación inmediata · Gratis</p>
                </div>
                <button class="modal-close" onclick="cerrarModalBtn()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label"><i class="fa-solid fa-calendar"></i> Fecha</label>
                        <input type="date" class="form-control" id="res-fecha">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fa-solid fa-clock"></i> Hora</label>
                        <select class="form-control" id="res-hora">
                            <option>12:00</option>
                            <option>13:00</option>
                            <option>19:00</option>
                            <option>20:00</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="fa-solid fa-users"></i> N° de personas</label>
                    <select class="form-control" id="res-personas">
                        <option>1 persona</option>
                        <option selected>2 personas</option>
                        <option>3 personas</option>
                        <option>4 personas</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="fa-solid fa-user"></i> Nombre completo</label>
                    <input type="text" class="form-control" placeholder="Tu nombre" id="res-nombre">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label"><i class="fa-solid fa-envelope"></i> Correo</label>
                        <input type="text" class="form-control" placeholder="correo@ejemplo.com" id="res-email">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fa-solid fa-phone"></i> Teléfono</label>
                        <input type="text" class="form-control" placeholder="9XXXXXXXX" maxlength="9" id="res-tel">
                    </div>
                </div>
                <button class="btn-reservar" onclick="confirmarReserva()">
                    <i class="fa-solid fa-circle-check"></i> Confirmar reserva gratis
                </button>
            </div>
        </div>
    </div>

    <script>
        // ── Tabs ─────────────────────────────────────────────
        document.querySelectorAll('.rest-tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.rest-tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.rest-tab-panel').forEach(p => p.classList.remove('active'));
                btn.classList.add('active');
                document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
            });
        });

        // ── Modal reserva ────────────────────────────────────
        function abrirModalReserva() {
            const hoy = new Date().toISOString().split('T')[0];
            document.getElementById('res-fecha').min = hoy;
            document.getElementById('res-fecha').value = hoy;
            document.getElementById('modal-overlay').classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function cerrarModal(e) {
            if (e.target === document.getElementById('modal-overlay')) cerrarModalBtn();
        }

        function cerrarModalBtn() {
            document.getElementById('modal-overlay').classList.remove('open');
            document.body.style.overflow = '';
        }

        function confirmarReserva() {
            const nombre = document.getElementById('res-nombre').value.trim();
            const email = document.getElementById('res-email').value.trim();
            const tel = document.getElementById('res-tel').value.trim();
            const fecha = document.getElementById('res-fecha').value;

            if (!nombre || !email || !tel || !fecha) {
                alert('Por favor completa todos los campos obligatorios.');
                return;
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                alert('Ingresa un correo válido.');
                return;
            }
            if (!/^9\d{8}$/.test(tel)) {
                alert('El teléfono debe tener 9 dígitos y empezar con 9.');
                return;
            }

            cerrarModalBtn();
            alert(`¡Reserva confirmada para ${nombre}!\nRecibirás la confirmación en ${email}.`);
        }
    </script>
    <script>
        document.getElementById('navToggle').addEventListener('click', () => {
            document.getElementById('navLinks').classList.toggle('open');
        });
    </script>

</body>

</html>