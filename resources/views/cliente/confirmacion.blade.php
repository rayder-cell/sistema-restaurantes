<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>Pedido confirmado — {{ $restaurante->nombre }}</title>
    @vite(['resources/assets/css/pages/cliente.css', 'resources/assets/js/app.js'])
</head>
<body class="cli-body">

<header class="cli-header">
    <div class="cli-header-inner">
        <h1 class="cli-logo">{{ $restaurante->nombre }}</h1>
        <span class="cli-mesa-badge-sm">Mesa {{ $mesa->numero }}</span>
    </div>
</header>

<main class="cli-confirmacion">

    <div class="cli-conf-card">
        {{-- Ícono de éxito --}}
        <div class="cli-conf-ico">✅</div>
        <h2 class="cli-conf-title">¡Pedido enviado!</h2>
        <p class="cli-conf-sub">Tu pedido fue recibido y está siendo preparado en cocina.</p>

        {{-- Estado del pedido --}}
        <div class="cli-estado-wrap" id="estado-wrap">
            @if($pedido)
                <div class="cli-estado-badge {{ $pedido->estado }}">
                    @if($pedido->estado === 'pendiente')       ⏳ Pendiente
                    @elseif($pedido->estado === 'en_preparacion') 🍳 En preparación
                    @elseif($pedido->estado === 'listo')       ✅ ¡Listo para servir!
                    @else                                         📦 {{ ucfirst($pedido->estado) }}
                    @endif
                </div>

                {{-- Detalle del pedido --}}
                <div class="cli-conf-detalles">
                    @foreach($pedido->detalles as $det)
                    <div class="cli-conf-item">
                        <span>{{ $det->cantidad }}x {{ $det->producto?->nombre }}</span>
                        <span>S/ {{ number_format($det->subtotal, 2) }}</span>
                    </div>
                    @endforeach
                    <div class="cli-conf-total">
                        <span>Total estimado</span>
                        <span>S/ {{ number_format($pedido->detalles->sum('subtotal') * 1.18, 2) }}</span>
                    </div>
                </div>
            @endif
        </div>

        {{-- Botón volver al menú --}}
        <a href="{{ route('cliente.menu', $mesa->qr_token) }}" class="cli-btn-volver">
            ← Ver más del menú
        </a>
    </div>

</main>

<script>
// Polling del estado cada 15 segundos
function verificarEstado() {
    fetch('{{ route("cliente.estado", $mesa->qr_token) }}')
        .then(r => r.json())
        .then(data => {
            if (!data.estado) return;

            const badge = document.querySelector('.cli-estado-badge');
            if (!badge) return;

            const estados = {
                'pendiente':      '⏳ Pendiente',
                'en_preparacion': '🍳 En preparación',
                'listo':          '✅ ¡Listo para servir!',
                'entregado':      '🎉 ¡Entregado!',
            };

            badge.className = `cli-estado-badge ${data.estado}`;
            badge.textContent = estados[data.estado] || data.estado;

            if (data.estado === 'listo') {
                badge.classList.add('pulse');
            }
        })
        .catch(() => {});
}

setInterval(verificarEstado, 15000);
</script>

</body>
</html>
