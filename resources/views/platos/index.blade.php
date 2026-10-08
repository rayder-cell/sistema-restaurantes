<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Platos — MIKHUNAWASI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/assets/css/pages/landing.css'])
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

    <section class="section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fa-solid fa-bowl-food" style="color:var(--verde-lt); margin-right:8px;"></i>
                Todos los platos
            </h2>
        </div>

        @if($platos->isEmpty())
            <div style="grid-column:1/-1; text-align:center; padding:40px 0; color:#9ca3af;">
                <i class="fa-solid fa-bowl-food" style="font-size:32px; margin-bottom:12px; display:block;"></i>
                Aún no hay platos disponibles.
            </div>
        @else
            <div class="platos-grid">
                @foreach($platos as $p)
                    <a href="{{ route('restaurantes.show', $p->restaurante_id) }}" class="plato-card" style="text-decoration:none; color:inherit;">
                        @if($p->imagen_url)
                            <img src="{{ $p->imagen_url }}" class="plato-img-wrap" style="object-fit:cover;" alt="{{ $p->nombre }}">
                        @else
                            <div class="plato-img-wrap"><i class="fa-solid fa-bowl-food"></i></div>
                        @endif
                        <div class="plato-body">
                            <p class="plato-nombre">{{ $p->nombre }}</p>
                            <p class="plato-rest">
                                <i class="fa-solid fa-store" style="margin-right:3px;"></i>
                                {{ $p->restaurante->nombre ?? '' }}
                            </p>
                            <p class="plato-precio">S/ {{ number_format($p->precio, 2) }}</p>
                        </div>
                    </a>
                @endforeach
            </div>

            <div style="margin-top:32px;">
                {{ $platos->links() }}
            </div>
        @endif
    </section>

    <script>
        document.getElementById('navToggle').addEventListener('click', () => {
            document.getElementById('navLinks').classList.toggle('open');
        });
    </script>

</body>

</html>