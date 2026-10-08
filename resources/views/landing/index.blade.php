<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIKHUNAWASI — Reserva tu mesa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/assets/css/pages/landing.css'])
</head>

<body>

    <!-- NAV -->
    <nav>
        <a href="{{ route('buscar') }}" class="nav-logo">
            <i class="fa-solid fa-utensils"></i>
            MIKHUNAWASI
        </a>

        <button class="nav-toggle" id="navToggle" aria-label="Abrir menú">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="nav-links" id="navLinks">
            <a href="{{ route('restaurantes.index') }}">Restaurantes</a>
            <a href="{{ route('platos.index') }}">Platos</a>
            <a href="#">Experiencias</a>
            <a href="#">Blog</a>
            <a href="{{ route('cliente.login') }}" class="nav-btn"><i class="fa-solid fa-user"></i> Ingresar</a>
        </div>
    </nav>

    <!-- HERO -->
    <section class="hero">
        <p class="hero-eyebrow"><i class="fa-solid fa-star"></i> La plataforma gastronómica del distrito de San Jerónimo
        </p>
        <h1>Reserva tu mesa en los<br>mejores <span>restaurantes</span></h1>
        <p class="hero-sub">Encuentra restaurantes, explora platos y reserva en segundos. confirmado al
            instante.</p>

        <div class="search-box">
            <div class="search-input-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="search-input" placeholder="Buscar restaurante o plato..."
                    oninput="buscar(this.value)">
            </div>
            <div class="search-divider"></div>
            
            <button class="search-btn" onclick="buscar(document.getElementById('search-input').value)">
                <i class="fa-solid fa-magnifying-glass"></i> Buscar
            </button>
        </div>
    </section>

    <!-- STATS -->
    <div class="stats-bar">
        <div class="stat-item"><strong>+{{ number_format($totalRestaurantes) }}</strong><span>Restaurantes</span></div>
        <div class="stat-item"><strong>+{{ number_format($totalReservas) }}</strong><span>Reservas realizadas</span>
        </div>
        <div class="stat-item"><strong>100%</strong><span>Confirmado</span></div>
        <div class="stat-item"><strong>24/7</strong><span>Disponible siempre</span></div>
    </div>

    <!-- RESTAURANTES -->
    <section class="section">
        <div class="section-header">
            <h2 class="section-title" id="rest-titulo"><i class="fa-solid fa-store"
                    style="color:var(--verde-lt); margin-right:8px;"></i>Restaurantes destacados</h2>
            <a href="#" class="section-link">Ver todos <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        <div class="rest-grid" id="rest-grid">
            <!-- Cards generadas por JS -->
        </div>
    </section>

    <!-- PLATOS DESTACADOS -->
    <section class="section"
        style="background: white; border-top: 1px solid var(--gris-brd); border-bottom: 1px solid var(--gris-brd);">
        <div class="section-header">
            <h2 class="section-title"><i class="fa-solid fa-bowl-food"
                    style="color:var(--verde-lt); margin-right:8px;"></i>Platos populares</h2>
            <a href="#" class="section-link">Ver todos <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="platos-grid" id="platos-grid"></div>
    </section>

    <!-- BANNER CTA -->
    <div class="banner-cta">
        <h2>¿Tienes un restaurante?</h2>
        <p>Únete a MIKHUNAWASI y llena tus mesas con reservas online confirmadas al instante.</p>
        <a href="#"><i class="fa-solid fa-handshake"></i> Únete gratis</a>
    </div>

    <!-- FOOTER -->
    <footer>
        <div class="footer-top">
            <div>
                <div class="footer-brand"><i class="fa-solid fa-utensils"></i> MIKHUNAWASI</div>
                <p class="footer-desc">La manera más fácil y rápida de reservar en los mejores restaurantes del Perú.
                    Gratis, confirmado, siempre.</p>
            </div>
            <div class="footer-col">
                <h4>Plataforma</h4>
                <a href="#">Restaurantes</a>
                <a href="#">Experiencias</a>
                <a href="#">Platos</a>
                <a href="#">Reservas</a>
            </div>
            <div class="footer-col">
                <h4>Empresa</h4>
                <a href="#">Quiénes somos</a>
                <a href="#">Blog</a>
                <a href="#">Contáctanos</a>
                <a href="#">Afilia tu local</a>
            </div>
            <div class="footer-col">
                <h4>Legal</h4>
                <a href="#">Privacidad</a>
                <a href="#">Términos</a>
                <a href="#">Cookies</a>
            </div>
        </div>
        <div class="footer-bottom">
            <span>© 2026 MIKHUNAWASI. Todos los derechos reservados.</span>
            <span>Hecho con <i class="fa-solid fa-heart" style="color:#C49A2A;"></i> en Perú</span>
        </div>
    </footer>

    <!-- MODAL RESERVA -->
    <div class="modal-overlay" id="modal-overlay" onclick="cerrarModal(event)">
        <div class="modal">
            <div class="modal-header">
                <div>
                    <h3 id="modal-title">Reservar mesa</h3>
                    <p class="modal-header-sub">Confirmación inmediata · Gratis</p>
                </div>
                <button class="modal-close" onclick="cerrarModalBtn()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <div class="modal-rest-info">
                    <div class="modal-rest-icon"><i class="fa-solid fa-utensils"></i></div>
                    <div>
                        <div class="modal-rest-name" id="modal-rest-name">—</div>
                        <div class="modal-rest-tipo" id="modal-rest-tipo">—</div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label"><i class="fa-solid fa-calendar"></i> Fecha</label>
                        <input type="date" class="form-control" id="res-fecha">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fa-solid fa-clock"></i> Hora</label>
                        <select class="form-control" id="res-hora">
                            <option>12:00</option>
                            <option>12:30</option>
                            <option>13:00</option>
                            <option>13:30</option>
                            <option>14:00</option>
                            <option>19:00</option>
                            <option>19:30</option>
                            <option>20:00</option>
                            <option>20:30</option>
                            <option>21:00</option>
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
                        <option>5 personas</option>
                        <option>6+ personas</option>
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
                        <input type="text" class="form-control" placeholder="9XXXXXXXX" maxlength="9"
                            id="res-tel">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fa-solid fa-note-sticky"></i> Indicaciones (opcional)</label>
                    <input type="text" class="form-control"
                        placeholder="Cumpleaños, sin gluten, mesa exterior...">
                </div>

                <button class="btn-reservar" onclick="confirmarReserva()">
                    <i class="fa-solid fa-circle-check"></i> Confirmar reserva gratis
                </button>
            </div>
        </div>
    </div>

    <script>
        // ── Datos reales desde el servidor ──────────────────────────────
        const restaurantes = @json($restaurantes);
        const platos = @json($platos);

        let restFiltrados = [...restaurantes];

        // ── Render restaurantes ────────────────────────────────────────
        function renderRestaurantes(lista) {
            const grid = document.getElementById('rest-grid');
            if (lista.length === 0) {
                grid.innerHTML = `<div style="grid-column:1/-1; text-align:center; padding:40px 0; color:#9ca3af;">
      <i class="fa-solid fa-magnifying-glass" style="font-size:32px; margin-bottom:12px; display:block;"></i>
      No encontramos restaurantes. Prueba con otra búsqueda.
    </div>`;
                return;
            }
            grid.innerHTML = lista.map(r => `
    <div class="rest-card">
      <div style="position:relative;">
        ${r.logo_url
          ? `<img src="${r.logo_url}" class="rest-card-img" alt="${r.nombre}">`
          : `<div class="rest-card-img-placeholder"><i class="fa-solid fa-store"></i></div>`
        }
      </div>
      <div class="rest-card-body">
        <p class="rest-card-name">${r.nombre}</p>
        <p class="rest-card-tipo"><i class="fa-solid fa-location-dot" style="margin-right:4px;"></i>${r.direccion ?? 'Dirección no registrada'}</p>
      </div>
    </div>
  `).join('');
        }

        // ── Render platos ──────────────────────────────────────────────
        function renderPlatos() {
            const grid = document.getElementById('platos-grid');
            if (platos.length === 0) {
                grid.innerHTML = `<div style="grid-column:1/-1; text-align:center; padding:20px 0; color:#9ca3af;">
      Aún no hay platos disponibles.
    </div>`;
                return;
            }
            grid.innerHTML = platos.map(p => `
    <div class="plato-card">
      ${p.imagen_url
        ? `<img src="${p.imagen_url}" class="plato-img-wrap" style="object-fit:cover;" alt="${p.nombre}">`
        : `<div class="plato-img-wrap"><i class="fa-solid fa-bowl-food"></i></div>`
      }
      <div class="plato-body">
        <p class="plato-nombre">${p.nombre}</p>
        <p class="plato-rest"><i class="fa-solid fa-store" style="margin-right:3px;"></i>${p.restaurante_nombre}</p>
        <p class="plato-precio">S/ ${parseFloat(p.precio).toFixed(2)}</p>
      </div>
    </div>
  `).join('');
        }

        // ── Búsqueda (por nombre o dirección reales) ────────────────────
        function buscar(q) {
            const term = q.toLowerCase().trim();
            restFiltrados = restaurantes.filter(r =>
                r.nombre.toLowerCase().includes(term) ||
                (r.direccion ?? '').toLowerCase().includes(term)
            );
            renderRestaurantes(restFiltrados);
        }

        // ── Modal reserva ──────────────────────────────────────────────
        function abrirModal(id) {
            const r = restaurantes.find(x => x.id === id);
            if (!r) return;
            document.getElementById('modal-title').textContent = `Reservar en ${r.nombre}`;
            document.getElementById('modal-rest-name').textContent = r.nombre;
            document.getElementById('modal-rest-tipo').textContent = r.direccion ?? '';
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

        // ── Init ───────────────────────────────────────────────────────
        renderRestaurantes(restaurantes);
        renderPlatos();

        document.getElementById('navToggle').addEventListener('click', () => {
            document.getElementById('navLinks').classList.toggle('open');
        });
    </script>
</body>

</html>
