<div class="coc-card en-preparacion" id="pedido-{{ $pedido->id }}">

    {{-- Header --}}
    <div class="coc-card-header">
        <div>
            <p class="coc-card-mesa">Mesa #{{ str_pad($pedido->mesa?->numero, 2, '0', STR_PAD_LEFT) }}</p>
            @if($pedido->usuario)
                <p class="coc-card-cocinero">
                    COCINERO: {{ strtoupper(explode(' ', $pedido->usuario->nombre)[0]) }}
                    {{ strtoupper(substr(explode(' ', $pedido->usuario->nombre)[1] ?? '', 0, 1)) }}.
                </p>
            @endif
        </div>
        <div class="text-right">
            <p class="coc-card-hace-label">Proceso</p>
            <p class="coc-card-timer proceso"
               data-created="{{ $pedido->updated_at->toIso8601String() }}"
               data-pedido-id="{{ $pedido->id }}">
                00:00 min
            </p>
        </div>
    </div>

    {{-- Detalles con estado visual --}}
    <div class="coc-card-detalles">
        @foreach($pedido->detalles as $det)
        <div class="coc-detalle-row">
            @if($det->estado === 'listo')
                <i class="fa-solid fa-circle-check text-green-500 text-sm"></i>
            @else
                <i class="fa-regular fa-circle text-gray-400 text-sm"></i>
            @endif
            <span class="coc-detalle-nombre {{ $det->estado === 'listo' ? 'line-through text-gray-400' : '' }}">
                {{ $det->cantidad }}x {{ $det->producto?->nombre }}
            </span>
        </div>
        @endforeach
    </div>

    @if($pedido->observacion)
        <div class="coc-card-obs">
            <i class="fa-solid fa-triangle-exclamation"></i> {{ $pedido->observacion }}
        </div>
    @endif

    {{-- Acción --}}
    <button onclick="cambiarEstado({{ $pedido->id }}, 'listo')"
            class="coc-btn-accion en-preparacion">
        <i class="fa-solid fa-check"></i> Listo para salir
    </button>

</div>