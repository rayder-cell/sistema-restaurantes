<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>Pedido confirmado — {{ $restaurante->nombre }}</title>
    @vite(['resources/assets/css/pages/cliente.css', 'resources/assets/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="cli-body">

<header class="cli-header">
    <div class="cli-header-inner">
        <h1 class="cli-logo">{{ $restaurante->nombre }}</h1>
        <span class="cli-mesa-badge-sm">
            <i class="fa-solid fa-chair"></i> Mesa {{ $mesa->numero }}
        </span>
    </div>
</header>

<main class="cli-confirmacion">

    <div class="cli-conf-card">

        {{-- Ícono de éxito --}}
        <div class="cli-conf-ico">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <h2 class="cli-conf-title">¡Pedido enviado!</h2>
        <p class="cli-conf-sub">Tu pedido fue recibido y está siendo preparado en cocina.</p>

        {{-- Estado del pedido --}}
        <div class="cli-estado-wrap" id="estado-wrap">
            @if($pedido)
                <div class="cli-estado-badge {{ $pedido->estado }}" id="estado-badge">
                    @if($pedido->estado === 'pendiente')
                        <i class="fa-solid fa-hourglass-half"></i> Pendiente
                    @elseif($pedido->estado === 'en_preparacion')
                        <i class="fa-solid fa-fire-burner"></i> En preparación
                    @elseif($pedido->estado === 'listo')
                        <i class="fa-solid fa-bell-concierge"></i> ¡Listo para servir!
                    @else
                        <i class="fa-solid fa-box"></i> {{ ucfirst($pedido->estado) }}
                    @endif
                </div>

                {{-- Detalle del pedido --}}
                <div class="cli-conf-detalles">
                    @foreach($pedido->detalles as $det)
                    <div class="cli-conf-item">
                        <span>
                            <i class="fa-solid fa-circle-dot" style="color:#9ca3af; font-size:10px; margin-right:4px;"></i>
                            {{ $det->cantidad }}x {{ $det->producto?->nombre }}
                        </span>
                        <span>S/ {{ number_format($det->subtotal, 2) }}</span>
                    </div>
                    @endforeach
                    <div class="cli-conf-total">
                        <span><i class="fa-solid fa-coins" style="margin-right:6px;"></i>Total estimado</span>
                        <span>S/ {{ number_format($pedido->detalles->sum('subtotal') * 1.18, 2) }}</span>
                    </div>
                </div>
            @endif
        </div>

        {{-- Botón volver al menú --}}
        <a href="{{ route('cliente.menu', $mesa->qr_token) }}" class="cli-btn-volver">
            <i class="fa-solid fa-arrow-left"></i> Ver más del menú
        </a>

    </div>

</main>

<style>
.cli-conf-ico {
    font-size: 52px;
    color: #16a34a;
    margin-bottom: 12px;
    animation: popIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
@keyframes popIn {
    from { transform: scale(0); opacity: 0; }
    to   { transform: scale(1); opacity: 1; }
}

.cli-estado-badge {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 8px 18px;
    border-radius: 50px;
    font-size: 14px;
    font-weight: 600;
    margin: 12px 0;
}
.cli-estado-badge.pendiente {
    background: #FEF9C3;
    color: #854D0E;
}
.cli-estado-badge.en_preparacion {
    background: #DBEAFE;
    color: #1d4ed8;
}
.cli-estado-badge.listo {
    background: #DCFCE7;
    color: #16a34a;
    animation: pulsar 1.5s infinite;
}
.cli-estado-badge.entregado {
    background: #EDE9FE;
    color: #4F378A;
}
@keyframes pulsar {
    0%, 100% { box-shadow: 0 0 0 0 rgba(22,163,74,0.3); }
    50%       { box-shadow: 0 0 0 8px rgba(22,163,74,0); }
}

.cli-conf-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    font-size: 13px;
    color: #4b5563;
    border-bottom: 0.5px solid #f3f4f6;
}
.cli-conf-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0 0;
    font-size: 15px;
    font-weight: 700;
    color: #1f2937;
    margin-top: 4px;
}
</style>

<script>
const estados = {
    'pendiente':      '<i class="fa-solid fa-hourglass-half"></i> Pendiente',
    'en_preparacion': '<i class="fa-solid fa-fire-burner"></i> En preparación',
    'listo':          '<i class="fa-solid fa-bell-concierge"></i> ¡Listo para servir!',
    'entregado':      '<i class="fa-solid fa-circle-check"></i> ¡Entregado!',
};

function verificarEstado() {
    fetch('{{ route("cliente.estado", $mesa->qr_token) }}')
        .then(r => r.json())
        .then(data => {
            if (!data.estado) return;
            const badge = document.getElementById('estado-badge');
            if (!badge) return;
            badge.className = `cli-estado-badge ${data.estado}`;
            badge.innerHTML = estados[data.estado] || data.estado;
        })
        .catch(() => {});
}

setInterval(verificarEstado, 15000);
</script>

</body>
</html>