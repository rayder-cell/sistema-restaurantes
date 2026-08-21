@extends('layouts.app')

@section('title', 'Pedidos — MIKHUNAWASI')
@section('page-title', 'Pedidos')

@section('content')

    <div class="ped-header">
        <div>
            <h2 class="ped-title">Pedidos Activos</h2>
            <p class="ped-sub">Gestiona los pedidos en curso de tu restaurante.</p>
        </div>
        @if (session('usuario_rol') !== 'cajero')
            <a href="{{ route('pedidos.crear') }}" class="ped-btn-nuevo">
                <i class="fa-solid fa-plus"></i> Nuevo Pedido
            </a>
        @endif
    </div>

    @if (session('usuario_rol') !== 'cajero')
        {{-- Seleccionar mesa --}}
        <div class="ped-mesas-grid" id="ped-mesas-grid">
            @foreach ($mesas as $mesa)
                @php
                    $dueno = $duenoPorMesa[$mesa->id] ?? null;
                    $esAjena = $mesa->estado === 'ocupada' && !$verTodo && $dueno !== null && $dueno !== $usuarioId;
                @endphp
                @if ($esAjena)
                    <div class="ped-mesa-card {{ $mesa->estado }}" style="opacity: 0.5; cursor: not-allowed;"
                        title="Atendida por otro mesero">
                        <p class="ped-mesa-numero">Mesa {{ $mesa->numero }}</p>
                        <p class="ped-mesa-cap">{{ $mesa->capacidad }} personas</p>
                        <span class="ped-mesa-estado">
                            <i class="fa-solid fa-user-clock text-gray-400 text-xs"></i> Atendida por otro mesero
                        </span>
                    </div>
                @else
                    <a href="{{ route('pedidos.crear', ['mesa_id' => $mesa->id]) }}"
                        class="ped-mesa-card {{ $mesa->estado }}">
                        <p class="ped-mesa-numero">Mesa {{ $mesa->numero }}</p>
                        <p class="ped-mesa-cap">{{ $mesa->capacidad }} personas</p>
                        <span class="ped-mesa-estado">
                            @if ($mesa->estado === 'libre')
                                <i class="fa-solid fa-circle text-green-500 text-xs"></i> Libre
                            @elseif($mesa->estado === 'ocupada')
                                <i class="fa-solid fa-circle text-red-500 text-xs"></i> Ocupada
                            @else
                                <i class="fa-solid fa-circle text-yellow-500 text-xs"></i> Reservada
                            @endif
                        </span>
                    </a>
                @endif
            @endforeach
        </div>
    @endif

    {{-- Pedidos activos --}}
    <div id="ped-seccion-lista">
        @if ($pedidos->count() > 0)
            <h3 class="ped-section-title" id="ped-section-title">
                <i class="fa-solid fa-fire text-orange-500"></i>
                EN CURSO ({{ $pedidos->count() }})
            </h3>
            <div class="ped-list" id="ped-list">
                @foreach ($pedidos as $pedido)
                    <div class="ped-item">
                        {{-- Mesa badge --}}
                        <div class="ped-item-mesa">
                            {{ $pedido->mesa_numero ? 'Mesa ' . $pedido->mesa_numero : 'Pedido #' . $pedido->id }}
                        </div>

                        {{-- Info central --}}
                        <div class="ped-item-info">
                            <div class="ped-item-meta">
                                <span><i class="fa-solid fa-box fa-xs text-gray-400"></i> {{ $pedido->detalles->count() }}
                                    producto(s)</span>
                                <span><i class="fa-regular fa-clock fa-xs text-gray-400"></i>
                                    {{ $pedido->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="ped-item-tags">
                                @foreach ($pedido->detalles->take(3) as $det)
                                    <span class="ped-item-tag">{{ $det->cantidad }}x {{ $det->producto?->nombre }}</span>
                                @endforeach
                                @if ($pedido->detalles->count() > 3)
                                    <span class="ped-item-tag-more">+{{ $pedido->detalles->count() - 3 }} más</span>
                                @endif
                            </div>
                        </div>

                        {{-- Total + Estado + Acción --}}
                        <div class="ped-item-right">
                            <p class="ped-item-total">S/ {{ number_format($pedido->total, 2) }}</p>
                            <span
                                class="badge
                        @if ($pedido->estado === 'pendiente') badge-yellow
                        @elseif($pedido->estado === 'en_preparacion') badge-blue
                        @elseif($pedido->estado === 'listo') badge-green
                        @else badge-gray @endif">
                                {{ ucfirst(str_replace('_', ' ', $pedido->estado)) }}
                            </span>
                            @if (session('usuario_rol') !== 'cajero')
                                <a href="{{ route('pedidos.show', $pedido->id) }}" class="ped-item-ver">
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state mt-6">
                <p class="empty-icon"><i class="fa-solid fa-clipboard-list text-gray-300 text-5xl"></i></p>
                <p>No hay pedidos activos. Selecciona una mesa para comenzar.</p>
            </div>
        @endif
    </div>

    <script>
        const ES_CAJERO = {{ session('usuario_rol') === 'cajero' ? 'true' : 'false' }};

        function badgeClase(estado) {
            if (estado === 'pendiente') return 'badge-yellow';
            if (estado === 'en_preparacion') return 'badge-blue';
            if (estado === 'listo') return 'badge-green';
            return 'badge-gray';
        }

        function estadoLegible(estado) {
            return estado.replace('_', ' ').replace(/^\w/, c => c.toUpperCase());
        }

        function renderPedidoItem(p) {
            let tagsHtml = '';
            p.detalles.forEach(d => {
                tagsHtml += `<span class="ped-item-tag">${d.cantidad}x ${d.nombre}</span>`;
            });
            if (p.detalles_count > 3) {
                tagsHtml += `<span class="ped-item-tag-more">+${p.detalles_count - 3} más</span>`;
            }

            const flechaHtml = ES_CAJERO ? '' : `
                <a href="/pedidos/${p.id}" class="ped-item-ver">
                    <i class="fa-solid fa-arrow-right"></i>
                </a>`;

            const etiquetaMesa = p.mesa_numero ? 'Mesa ' + p.mesa_numero : 'Pedido #' + p.id;

            return `
                <div class="ped-item">
                    <div class="ped-item-mesa">${etiquetaMesa}</div>
                    <div class="ped-item-info">
                        <div class="ped-item-meta">
                            <span><i class="fa-solid fa-box fa-xs text-gray-400"></i> ${p.detalles_count} producto(s)</span>
                            <span><i class="fa-regular fa-clock fa-xs text-gray-400"></i> ${p.created_at_diff}</span>
                        </div>
                        <div class="ped-item-tags">${tagsHtml}</div>
                    </div>
                    <div class="ped-item-right">
                        <p class="ped-item-total">S/ ${parseFloat(p.total).toFixed(2)}</p>
                        <span class="badge ${badgeClase(p.estado)}">${estadoLegible(p.estado)}</span>
                        ${flechaHtml}
                    </div>
                </div>`;
        }

        function renderMesaCardPedidos(m) {
            if (m.esAjena) {
                return `
                    <div class="ped-mesa-card ${m.estado}" style="opacity: 0.5; cursor: not-allowed;" title="Atendida por otro mesero">
                        <p class="ped-mesa-numero">Mesa ${m.numero}</p>
                        <p class="ped-mesa-cap">${m.capacidad} personas</p>
                        <span class="ped-mesa-estado">
                            <i class="fa-solid fa-user-clock text-gray-400 text-xs"></i> Atendida por otro mesero
                        </span>
                    </div>`;
            }

            let estadoHtml = '';
            if (m.estado === 'libre') {
                estadoHtml = `<i class="fa-solid fa-circle text-green-500 text-xs"></i> Libre`;
            } else if (m.estado === 'ocupada') {
                estadoHtml = `<i class="fa-solid fa-circle text-red-500 text-xs"></i> Ocupada`;
            } else {
                estadoHtml = `<i class="fa-solid fa-circle text-yellow-500 text-xs"></i> Reservada`;
            }

            return `
                <a href="/pedidos/nuevo?mesa_id=${m.id}" class="ped-mesa-card ${m.estado}">
                    <p class="ped-mesa-numero">Mesa ${m.numero}</p>
                    <p class="ped-mesa-cap">${m.capacidad} personas</p>
                    <span class="ped-mesa-estado">${estadoHtml}</span>
                </a>`;
        }

        function actualizarPedidos() {
            fetch('{{ route('pedidos.actualizar') }}')
                .then(r => r.json())
                .then(data => {
                    // Mesas (solo si no es cajero, ya que ese grid no existe para ellos)
                    const mesasGrid = document.getElementById('ped-mesas-grid');
                    if (mesasGrid) {
                        mesasGrid.innerHTML = data.mesas.map(renderMesaCardPedidos).join('');
                    }

                    // Lista de pedidos
                    const contenedor = document.getElementById('ped-seccion-lista');
                    if (data.pedidos.length > 0) {
                        contenedor.innerHTML = `
                            <h3 class="ped-section-title">
                                <i class="fa-solid fa-fire text-orange-500"></i>
                                EN CURSO (${data.pedidos.length})
                            </h3>
                            <div class="ped-list">
                                ${data.pedidos.map(renderPedidoItem).join('')}
                            </div>`;
                    } else {
                        contenedor.innerHTML = `
                            <div class="empty-state mt-6">
                                <p class="empty-icon"><i class="fa-solid fa-clipboard-list text-gray-300 text-5xl"></i></p>
                                <p>No hay pedidos activos. Selecciona una mesa para comenzar.</p>
                            </div>`;
                    }
                })
                .catch(err => console.error('Error al actualizar pedidos:', err));
        }

        setInterval(actualizarPedidos, 3000);
    </script>

@endsection