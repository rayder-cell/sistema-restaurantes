<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurantes — MIKHUNAWASI</title>
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
                <i class="fa-solid fa-store" style="color:var(--verde-lt); margin-right:8px;"></i>
                Todos los restaurantes
            </h2>
        </div>

        @if($restaurantes->isEmpty())
            <div style="grid-column:1/-1; text-align:center; padding:40px 0; color:#9ca3af;">
                <i class="fa-solid fa-store-slash" style="font-size:32px; margin-bottom:12px; display:block;"></i>
                Aún no hay restaurantes registrados.
            </div>
        @else
            <div class="rest-grid">
                @foreach($restaurantes as $r)
                    <a href="{{ route('restaurantes.show', $r) }}" class="rest-card" style="text-decoration:none; color:inherit;">
                        <div style="position:relative;">
                            @if($r->logo_url)
                                <img src="{{ $r->logo_url }}" class="rest-card-img" alt="{{ $r->nombre }}">
                            @else
                                <div class="rest-card-img-placeholder"><i class="fa-solid fa-store"></i></div>
                            @endif
                        </div>
                        <div class="rest-card-body">
                            <p class="rest-card-name">{{ $r->nombre }}</p>
                            <p class="rest-card-tipo">
                                <i class="fa-solid fa-location-dot" style="margin-right:4px;"></i>
                                {{ $r->direccion ?? 'Dirección no registrada' }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>

            <div style="margin-top:32px;">
                {{ $restaurantes->links() }}
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