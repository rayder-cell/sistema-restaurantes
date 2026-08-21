<div class="coc-card pendiente" id="pedido-{{ $pedido->id }}">

    {{-- Badge urgente (aparece después de 10 min) --}}
    <div class="coc-urgente-badge hidden">
        <i class="fa-solid fa-triangle-exclamation"></i> ¡URGENTE!
    </div>

    {{-- Header --}}
    <div class="coc-card-header">
        <div>
            <p class="coc-card-mesa">Mesa #{{ str_pad($pedido->mesa?->numero, 2, '0', STR_PAD_LEFT) }}</p>
        </div>
        <div class="text-right">
            <p class="coc-card-hace-label">Hace</p>
            <p class="coc-card-timer urgente"
               data-created="{{ $pedido->created_at->toIso8601String() }}"
               data-pedido-id="{{ $pedido->id }}">
                00:00 min
            </p>
        </div>
    </div>

    {{-- Detalles --}}
    <div class="coc-card-detalles">
        @foreach($pedido->detalles as $det)
        <div class="coc-detalle-row">
            <span class="coc-detalle-qty">{{ $det->cantidad }}x</span>
            <span class="coc-detalle-nombre">{{ $det->producto?->nombre }}</span>
            @if($det->observacion)
                <span class="coc-detalle-obs">{{ $det->observacion }}</span>
            @endif
        </div>
        @endforeach
    </div>

    @if($pedido->observacion)
        <div class="coc-card-obs">
            <i class="fa-solid fa-triangle-exclamation"></i> {{ $pedido->observacion }}
        </div>
    @endif

    {{-- Acción --}}
    <button onclick="cambiarEstado({{ $pedido->id }}, 'en_preparacion')"
            class="coc-btn-accion pendiente">
        <i class="fa-solid fa-play"></i> Comenzar Preparación
    </button>

</div>