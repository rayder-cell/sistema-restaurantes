<div class="coc-card listo" id="pedido-{{ $pedido->id }}">

    {{-- Header --}}
    <div class="coc-card-header">
        <div>
            <p class="coc-card-mesa">Mesa #{{ str_pad($pedido->mesa?->numero, 2, '0', STR_PAD_LEFT) }}</p>
            <p class="coc-card-esperando">ESPERANDO MESERO</p>
        </div>
        <div class="text-right">
            <p class="coc-card-hace-label">Finalizado</p>
            <span class="coc-check-grande">
                <i class="fa-solid fa-circle-check" style="color:#16a34a; font-size:1.5rem;"></i>
            </span>
        </div>
    </div>

    {{-- Detalles --}}
    <div class="coc-card-detalles">
        @foreach($pedido->detalles as $det)
        <div class="coc-detalle-row done">
            <i class="fa-solid fa-check text-green-500 text-sm"></i>
            <span class="coc-detalle-nombre text-gray-500 line-through">
                {{ $det->cantidad }}x {{ $det->producto?->nombre }}
            </span>
        </div>
        @endforeach
    </div>

</div>