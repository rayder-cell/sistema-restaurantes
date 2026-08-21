@extends('layouts.app')

@section('title', 'Panel de Cocina — MIKHUNAWASI')
@section('page-title', 'Cocina')

@push('styles')
    @vite('resources/assets/css/pages/cocina.css')
@endpush

@section('content')

    {{-- Header del panel --}}
    <div class="coc-header">
        <h2 class="coc-title">Panel de Cocina</h2>
        <div class="coc-header-right">
            <div class="coc-reloj" id="reloj-actual">{{ now()->format('h:i A') }}</div>
            <div class="coc-auto-badge" id="auto-badge">
                <span class="coc-auto-dot"></span> Auto-actualización
            </div>
        </div>
    </div>

    {{-- Kanban --}}
    <div class="coc-kanban">

        {{-- ══════════════════════════════════
         COLUMNA: PENDIENTE
    ══════════════════════════════════ --}}
        <div class="coc-col">
            <div class="coc-col-header pendiente">
                <div class="flex items-center gap-2">
                    <span class="coc-col-dot pendiente"></span>
                    <span class="coc-col-titulo">Pendiente</span>
                </div>
                <span class="coc-col-count" id="count-pendiente">{{ $pendientes->count() }}</span>
            </div>

            <div class="coc-col-body" id="col-pendiente">
                @forelse($pendientes as $pedido)
                    @include('cocina.partials.card-pendiente', ['pedido' => $pedido])
                @empty
                    <div class="coc-col-empty">Sin pedidos pendientes</div>
                @endforelse
            </div>
        </div>

        {{-- ══════════════════════════════════
         COLUMNA: EN PREPARACIÓN
    ══════════════════════════════════ --}}
        <div class="coc-col">
            <div class="coc-col-header en-preparacion">
                <div class="flex items-center gap-2">
                    <span class="coc-col-dot en-preparacion"></span>
                    <span class="coc-col-titulo">En Preparación</span>
                </div>
                <span class="coc-col-count" id="count-preparacion">{{ $enPreparacion->count() }}</span>
            </div>

            <div class="coc-col-body" id="col-preparacion">
                @forelse($enPreparacion as $pedido)
                    @include('cocina.partials.card-preparacion', ['pedido' => $pedido])
                @empty
                    <div class="coc-col-empty">Sin pedidos en preparación</div>
                @endforelse
            </div>
        </div>

        {{-- ══════════════════════════════════
         COLUMNA: LISTO
    ══════════════════════════════════ --}}
        <div class="coc-col">
            <div class="coc-col-header listo">
                <div class="flex items-center gap-2">
                    <span class="coc-col-dot listo"></span>
                    <span class="coc-col-titulo">Listo</span>
                </div>
                <span class="coc-col-count" id="count-listo">{{ $listos->count() }}</span>
            </div>

            <div class="coc-col-body" id="col-listo">
                @forelse($listos as $pedido)
                    @include('cocina.partials.card-listo', ['pedido' => $pedido])
                @empty
                    <div class="coc-col-empty">Sin pedidos listos</div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Footer con métricas --}}
    <div class="coc-footer">
        <div class="coc-footer-left">
            <span class="coc-footer-dot blue"></span>
            Producción: <strong id="footer-total-platos">{{ $totalPlatos }}</strong> platos
            <span class="coc-footer-sep">•</span>
            <span class="coc-footer-dot red"></span>
            Demora prom: <strong id="footer-tiempo-promedio">{{ number_format($tiempoPromedio ?? 0, 1) }} min</strong>
        </div>
        <div class="coc-footer-right">
            <button onclick="window.print()" class="coc-footer-btn">🖨 Imprimir</button>
        </div>
    </div>

    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;
        let tiemers = {}; // { pedido_id: intervalId }

        // ── Cambiar estado vía AJAX ───────────────────────────────────────
        function cambiarEstado(pedidoId, nuevoEstado) {
            fetch(`/cocina/${pedidoId}/estado`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                    },
                    body: JSON.stringify({
                        estado: nuevoEstado
                    }),
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        actualizarCocina();
                    }
                })
                .catch(err => console.error('Error:', err));
        }

        // ── Reloj en tiempo real ──────────────────────────────────────────
        function actualizarReloj() {
            const ahora = new Date();
            const h = ahora.getHours();
            const m = String(ahora.getMinutes()).padStart(2, '0');
            const ampm = h >= 12 ? 'PM' : 'AM';
            const h12 = h % 12 || 12;
            document.getElementById('reloj-actual').textContent = `${h12}:${m} ${ampm}`;
        }
        setInterval(actualizarReloj, 1000);

        // ── Temporizadores de cada pedido ─────────────────────────────────
        function limpiarTemporizadores() {
            Object.values(tiemers).forEach(id => clearInterval(id));
            tiemers = {};
        }

        function iniciarTemporizadores() {
            document.querySelectorAll('[data-created]').forEach(el => {
                const created = new Date(el.dataset.created);
                const id = el.dataset.pedidoId;

                function tick() {
                    const ahora = new Date();
                    const diffMs = ahora - created;
                    const mins = Math.floor(diffMs / 60000);
                    const segs = Math.floor((diffMs % 60000) / 1000);
                    el.textContent = `${String(mins).padStart(2,'0')}:${String(segs).padStart(2,'0')} min`;

                    const card = el.closest('.coc-card');
                    if (card && mins >= 10) {
                        card.classList.add('urgente');
                        const badge = card.querySelector('.coc-urgente-badge');
                        if (badge) badge.classList.remove('hidden');
                    }
                }
                tick();
                tiemers[id] = setInterval(tick, 1000);
            });
        }
        iniciarTemporizadores();

        // ── Renderizado de tarjetas (réplica de los partials Blade) ───────
        function renderCardPendiente(p) {
            let detallesHtml = '';
            p.detalles.forEach(d => {
                detallesHtml += `
        <div class="coc-detalle-row">
            <span class="coc-detalle-qty">${d.cantidad}x</span>
            <span class="coc-detalle-nombre">${d.nombre}</span>
            ${d.observacion ? `<span class="coc-detalle-obs">${d.observacion}</span>` : ''}
        </div>`;
            });

            const obsHtml = p.observacion ? `
        <div class="coc-card-obs">
            <i class="fa-solid fa-triangle-exclamation"></i> ${p.observacion}
        </div>` : '';

            return `
        <div class="coc-card pendiente" id="pedido-${p.id}">
            <div class="coc-urgente-badge hidden">
                <i class="fa-solid fa-triangle-exclamation"></i> ¡URGENTE!
            </div>
            <div class="coc-card-header">
                <div>
                    <p class="coc-card-mesa">Mesa #${String(p.mesa_numero).padStart(2,'0')}</p>
                </div>
                <div class="text-right">
                    <p class="coc-card-hace-label">Hace</p>
                    <p class="coc-card-timer urgente" data-created="${p.created_at}" data-pedido-id="${p.id}">00:00 min</p>
                </div>
            </div>
            <div class="coc-card-detalles">${detallesHtml}</div>
            ${obsHtml}
            <button onclick="cambiarEstado(${p.id}, 'en_preparacion')" class="coc-btn-accion pendiente">
                <i class="fa-solid fa-play"></i> Comenzar Preparación
            </button>
        </div>`;
        }

        function renderCardPreparacion(p) {
            let detallesHtml = '';
            p.detalles.forEach(d => {
                const icono = d.estado === 'listo' ?
                    `<i class="fa-solid fa-circle-check text-green-500 text-sm"></i>` :
                    `<i class="fa-regular fa-circle text-gray-400 text-sm"></i>`;
                const clase = d.estado === 'listo' ? 'line-through text-gray-400' : '';
                detallesHtml += `
        <div class="coc-detalle-row">
            ${icono}
            <span class="coc-detalle-nombre ${clase}">${d.cantidad}x ${d.nombre}</span>
        </div>`;
            });

            const cocineroHtml = p.cocinero ? `<p class="coc-card-cocinero">COCINERO: ${p.cocinero}</p>` : '';
            const obsHtml = p.observacion ? `
        <div class="coc-card-obs">
            <i class="fa-solid fa-triangle-exclamation"></i> ${p.observacion}
        </div>` : '';

            return `
        <div class="coc-card en-preparacion" id="pedido-${p.id}">
            <div class="coc-card-header">
                <div>
                    <p class="coc-card-mesa">Mesa #${String(p.mesa_numero).padStart(2,'0')}</p>
                    ${cocineroHtml}
                </div>
                <div class="text-right">
                    <p class="coc-card-hace-label">Proceso</p>
                    <p class="coc-card-timer proceso" data-created="${p.updated_at}" data-pedido-id="${p.id}">00:00 min</p>
                </div>
            </div>
            <div class="coc-card-detalles">${detallesHtml}</div>
            ${obsHtml}
            <button onclick="cambiarEstado(${p.id}, 'listo')" class="coc-btn-accion en-preparacion">
                <i class="fa-solid fa-check"></i> Listo para salir
            </button>
        </div>`;
        }

        function renderCardListo(p) {
            let detallesHtml = '';
            p.detalles.forEach(d => {
                detallesHtml += `
        <div class="coc-detalle-row done">
            <span class="text-green-500 text-sm">✓</span>
            <span class="coc-detalle-nombre text-gray-500">${d.cantidad}x ${d.nombre}</span>
        </div>`;
            });

            return `
        <div class="coc-card listo" id="pedido-${p.id}">
            <div class="coc-card-header">
                <div>
                    <p class="coc-card-mesa">Mesa #${String(p.mesa_numero).padStart(2,'0')}</p>
                    <p class="coc-card-esperando">ESPERANDO MESERO</p>
                </div>
                <div class="text-right">
                    <p class="coc-card-hace-label">Finalizado</p>
                    <span class="coc-check-grande">✅</span>
                </div>
            </div>
            <div class="coc-card-detalles">${detallesHtml}</div>
        </div>`;
        }

        // ── Auto-actualización cada 3 segundos (AJAX, sin recargar página) ──
        function actualizarCocina() {
            fetch('{{ route('cocina.actualizar') }}')
                .then(r => r.json())
                .then(data => {
                    limpiarTemporizadores();

                    const colPendiente = document.getElementById('col-pendiente');
                    colPendiente.innerHTML = data.pendientes.length ?
                        data.pendientes.map(renderCardPendiente).join('') :
                        '<div class="coc-col-empty">Sin pedidos pendientes</div>';

                    const colPreparacion = document.getElementById('col-preparacion');
                    colPreparacion.innerHTML = data.enPreparacion.length ?
                        data.enPreparacion.map(renderCardPreparacion).join('') :
                        '<div class="coc-col-empty">Sin pedidos en preparación</div>';

                    const colListo = document.getElementById('col-listo');
                    colListo.innerHTML = data.listos.length ?
                        data.listos.map(renderCardListo).join('') :
                        '<div class="coc-col-empty">Sin pedidos listos</div>';

                    document.getElementById('count-pendiente').textContent = data.pendientes.length;
                    document.getElementById('count-preparacion').textContent = data.enPreparacion.length;
                    document.getElementById('count-listo').textContent = data.listos.length;

                    document.getElementById('footer-total-platos').textContent = data.totalPlatos;
                    document.getElementById('footer-tiempo-promedio').textContent = data.tiempoPromedio.toFixed(1) +
                        ' min';

                    iniciarTemporizadores();
                })
                .catch(err => console.error('Error al actualizar cocina:', err));
        }

        setInterval(actualizarCocina, 3000);
    </script>

@endsection
