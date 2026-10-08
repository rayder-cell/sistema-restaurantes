@extends('layouts.app')

@push('styles')
    @vite(['resources/assets/css/pages/mesas.css'])
@endpush

@section('title', 'Gestión de Mesas — MIKHUNAWASI')
@section('page-title', 'Mesas')

@section('content')

    {{-- Encabezado --}}
    <div class="mesas-header">
        <div>
            <h2 class="mesas-title">Gestión de Mesas</h2>
            <p class="mesas-sub">Estado en tiempo real del salón principal.</p>
        </div>
        @if (in_array(session('usuario_rol'), ['propietario', 'administrador']))
            <button onclick="abrirModalNueva()" class="mesas-btn-nueva">
                <i class="fa-solid fa-plus"></i> Nueva Mesa
            </button>
        @endif
    </div>

    {{-- Resumen de estados --}}
    @if ($mesas->count() > 0)
        <div class="mesas-resumen">
            <div class="mesas-badge-resumen libre">
                <span class="dot"></span>
                <span id="resumen-libres">{{ $resumen['libres'] }}</span> Libres
            </div>
            <div class="mesas-badge-resumen ocupada">
                <span class="dot"></span>
                <span id="resumen-ocupadas">{{ $resumen['ocupadas'] }}</span> Ocupadas
            </div>
            <div class="mesas-badge-resumen reservada">
                <span class="dot"></span>
                <span id="resumen-reservadas">{{ $resumen['reservadas'] }}</span> Reservadas
            </div>
            <span class="text-sm text-gray-400 ml-auto">
                Total: <span id="resumen-total">{{ $resumen['total'] }}</span> mesas
            </span>
        </div>
    @endif

    {{-- Grid de mesas --}}
    @if ($mesas->count() > 0)
        <div class="mesas-grid" id="mesas-grid">
            @foreach ($mesas as $mesa)
                @php
                    $dueno = $duenoPorMesa[$mesa->id] ?? null;
                    $esMia = $verTodo || $dueno === $usuarioId;
                @endphp
                <div class="mesa-card {{ $mesa->estado }}" id="mesa-card-{{ $mesa->id }}">

                    {{-- Top --}}
                    <div class="mesa-card-top">
                        <div class="mesa-numero">{{ str_pad($mesa->numero, 2, '0', STR_PAD_LEFT) }}</div>
                        <span class="mesa-estado-badge" id="badge-{{ $mesa->id }}">
                            {{ strtoupper($mesa->estado) }}
                        </span>
                    </div>

                    {{-- Info --}}
                    <div class="mesa-card-body">
                        <div class="mesa-info-row">
                            <i class="fa-solid fa-users"></i>
                            <span>Capacidad: {{ $mesa->capacidad }} personas</span>
                        </div>

                        @if ($mesa->estado === 'ocupada')
                            @if ($esMia)
                                <div class="mesa-info-row">
                                    <i class="fa-solid fa-clipboard-list"></i>
                                    <span>Con pedido activo</span>
                                </div>

                                @if (isset($pedidosActivos[$mesa->id]) && $pedidosActivos[$mesa->id]->count() > 0)
                                    {{-- Lista de platos pendientes --}}
                                    <div class="mesa-platos-lista">
                                        @foreach ($pedidosActivos[$mesa->id] as $detalle)
                                            <div class="mesa-plato-item" id="detalle-{{ $detalle->detalle_id }}">
                                                <div class="mesa-plato-info">
                                                    <span class="mesa-plato-qty">{{ $detalle->cantidad }}x</span>
                                                    <span class="mesa-plato-nombre">{{ $detalle->producto_nombre }}</span>
                                                    @if ($detalle->observacion)
                                                        <span class="mesa-plato-obs">· {{ $detalle->observacion }}</span>
                                                    @endif
                                                </div>
                                                @if ($detalle->detalle_estado === 'listo')
                                                    <button
                                                        onclick="entregarPlato({{ $detalle->detalle_id }}, {{ $mesa->id }})"
                                                        class="mesa-plato-btn" title="Marcar como entregado">
                                                        <i class="fa-solid fa-check"></i>
                                                    </button>
                                                @else
                                                    <span class="mesa-plato-preparando" title="Aún en preparación">
                                                        <i class="fa-solid fa-clock text-gray-400"></i>
                                                    </span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="mesa-info-row">
                                        <i class="fa-solid fa-circle-check"></i>
                                        <span>Todo entregado — esperando cobro</span>
                                    </div>
                                @endif

                                <a href="{{ route('pedidos.crear', ['mesa_id' => $mesa->id]) }}"
                                    class="mesa-btn-agregar-pedido">
                                    <i class="fa-solid fa-plus"></i> Agregar Pedido
                                </a>

                                @if ($dueno === $usuarioId)
                                    <button onclick="liberarMesaManual({{ $mesa->id }}, {{ $mesa->numero }})"
                                        class="mesa-btn-liberar">
                                        <i class="fa-solid fa-door-open"></i> Liberar Mesa
                                    </button>
                                @endif
                            @else
                                <div class="mesa-info-row">
                                    <i class="fa-solid fa-user-clock"></i>
                                    <span>Atendida por otro mesero</span>
                                </div>
                            @endif
                        @elseif($mesa->estado === 'reservada')
                            <div class="mesa-info-row">
                                <i class="fa-solid fa-calendar-days"></i>
                                <span>Reserva programada</span>
                            </div>
                        @else
                            <div class="mesa-info-row">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>Disponible</span>
                            </div>
                        @endif
                    </div>

                    {{-- Footer --}}
                    <div class="mesa-card-footer">
                        <button
                            onclick="verQR({{ $mesa->id }}, {{ $mesa->numero }}, '{{ $mesa->qr_token }}')"
                            class="mesa-btn-qr">
                            <i class="fa-solid fa-qrcode"></i> Ver QR
                        </button>
                        @if (in_array(session('usuario_rol'), ['propietario', 'administrador']))
                            <button
                                onclick="abrirModalEstado({{ $mesa->id }}, {{ $mesa->numero }}, '{{ $mesa->estado }}')"
                                class="mesa-btn-estado">
                                <i class="fa-solid fa-rotate"></i> Cambiar estado
                            </button>
                        @endif
                        @if (in_array(session('usuario_rol'), ['propietario', 'administrador']))
                            <div class="mesa-card-footer-row">
                                <button
                                    onclick="abrirModalEditar({{ $mesa->id }}, {{ $mesa->numero }}, {{ $mesa->capacidad }})"
                                    class="btn-edit">
                                    <i class="fa-solid fa-pen"></i> Editar
                                </button>
                                <button onclick="eliminarMesa({{ $mesa->id }}, {{ $mesa->numero }})"
                                    class="btn-delete">
                                    <i class="fa-solid fa-trash"></i> Eliminar
                                </button>
                            </div>
                        @endif
                    </div>

                </div>
            @endforeach
        </div>
    @else
        <div class="mesas-empty">
            <p class="mesas-empty-icon"><i class="fa-solid fa-chair"></i></p>
            <p class="mesas-empty-title">No hay mesas registradas</p>
            <p class="mesas-empty-sub">Agrega las mesas de tu restaurante para comenzar a operar.</p>
            @if (in_array(session('usuario_rol'), ['propietario', 'administrador']))
                <button onclick="abrirModalNueva()" class="mesas-btn-nueva mt-6">
                    <i class="fa-solid fa-plus"></i> Nueva Mesa
                </button>
            @endif
        </div>
    @endif


    {{-- MODAL: Nueva / Editar mesa --}}
    <div id="modal-nueva" class="mesas-modal-overlay hidden">
        <div class="mesas-modal">
            <div class="mesas-modal-header">
                <h3 class="mesas-modal-title" id="modal-nueva-titulo">Nueva Mesa</h3>
                <button onclick="cerrarModalNueva()" class="mesas-modal-close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form method="POST" id="form-mesa" class="mesas-modal-body">
                @csrf
                <div id="form-method-field"></div>
                <div>
                    <label class="mesas-form-label">Número de mesa *</label>
                    <input type="number" name="numero" id="mesa-numero" min="1" placeholder="Ej. 1, 2, 3..."
                        class="mesas-form-input" required
                        onkeydown="return ['Backspace','Delete','ArrowLeft','ArrowRight','Tab'].includes(event.key) || /^[0-9]$/.test(event.key)">
                </div>
                <div>
                    <label class="mesas-form-label">Capacidad (personas) *</label>
                    <input type="number" name="capacidad" id="mesa-capacidad" min="1" max="20"
                        placeholder="Ej. 2, 4, 6..." class="mesas-form-input" required
                        onkeydown="return ['Backspace','Delete','ArrowLeft','ArrowRight','Tab'].includes(event.key) || /^[0-9]$/.test(event.key)">
                </div>
                <div class="mesas-modal-actions">
                    <button type="button" onclick="cerrarModalNueva()" class="mesas-btn-cancelar">Cancelar</button>
                    <button type="submit" class="mesas-btn-guardar">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>


    {{-- MODAL: Cambiar estado --}}
    <div id="modal-estado" class="mesas-modal-overlay hidden">
        <div class="mesas-modal">
            <div class="mesas-modal-header">
                <h3 class="mesas-modal-title" id="modal-estado-titulo">Cambiar estado</h3>
                <button onclick="cerrarModalEstado()" class="mesas-modal-close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="mesas-modal-body">
                <p class="text-sm text-gray-500 mb-4">Selecciona el nuevo estado de la mesa:</p>
                <div class="estado-opciones">
                    <div class="estado-opcion libre" onclick="seleccionarEstado('libre')">
                        <span class="estado-icon"><i class="fa-solid fa-circle-check"></i></span>
                        <span class="estado-nombre">LIBRE</span>
                    </div>
                    <div class="estado-opcion ocupada" onclick="seleccionarEstado('ocupada')">
                        <span class="estado-icon"><i class="fa-solid fa-circle-exclamation"></i></span>
                        <span class="estado-nombre">OCUPADA</span>
                    </div>
                    <div class="estado-opcion reservada" onclick="seleccionarEstado('reservada')">
                        <span class="estado-icon"><i class="fa-solid fa-calendar-days"></i></span>
                        <span class="estado-nombre">RESERVADA</span>
                    </div>
                </div>
                <div class="mesas-modal-actions mt-4">
                    <button type="button" onclick="cerrarModalEstado()" class="mesas-btn-cancelar">Cancelar</button>
                    <button onclick="confirmarEstado()" class="mesas-btn-guardar">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL: Código QR --}}
    <div id="modal-qr" class="mesas-modal-overlay hidden">
        <div class="mesas-modal" style="max-width:380px">
            <div class="mesas-modal-header">
                <h3 class="mesas-modal-title" id="modal-qr-titulo">Código QR</h3>
                <button onclick="cerrarModalQR()" class="mesas-modal-close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="mesas-modal-body" style="text-align:center">
                <img id="qr-modal-img" src="" alt="Código QR de la mesa"
                    style="width:260px;height:260px;margin:0 auto 12px;border-radius:12px;border:1px solid #e5e7eb;display:block">
                <p class="text-xs text-gray-400 break-all mb-4" id="qr-modal-link"></p>
                <div class="mesas-modal-actions">
                    <a id="qr-modal-descargar" href="#" download
                        class="mesas-btn-cancelar" style="text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:6px">
                        <i class="fa-solid fa-download"></i> Descargar
                    </a>
                    <button onclick="imprimirQR()" class="mesas-btn-guardar">
                        <i class="fa-solid fa-print"></i> Imprimir
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Form oculto eliminar --}}
    <form id="form-eliminar-mesa" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    <script>
        let mesaActualId = null;
        let estadoSeleccionado = null;

        // ── Modal nueva / editar ──────────────────────
        function abrirModalNueva() {
            document.getElementById('modal-nueva-titulo').textContent = 'Nueva Mesa';
            document.getElementById('form-mesa').action = '{{ route('mesas.store') }}';
            document.getElementById('form-method-field').innerHTML = '';
            document.getElementById('mesa-numero').value = '';
            document.getElementById('mesa-capacidad').value = '';
            document.getElementById('modal-nueva').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function abrirModalEditar(id, numero, capacidad) {
            document.getElementById('modal-nueva-titulo').textContent = 'Editar Mesa';
            document.getElementById('form-mesa').action = `/mesas/${id}`;
            document.getElementById('form-method-field').innerHTML = '<input type="hidden" name="_method" value="PUT">';
            document.getElementById('mesa-numero').value = numero;
            document.getElementById('mesa-capacidad').value = capacidad;
            document.getElementById('modal-nueva').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function cerrarModalNueva() {
            document.getElementById('modal-nueva').classList.add('hidden');
            document.body.style.overflow = '';
        }

        // ── Modal cambiar estado ──────────────────────
        function abrirModalEstado(id, numero, estadoActual) {
            mesaActualId = id;
            estadoSeleccionado = estadoActual;
            document.getElementById('modal-estado-titulo').textContent =
                `Mesa ${String(numero).padStart(2,'0')} — Cambiar estado`;
            document.querySelectorAll('.estado-opcion').forEach(el => el.classList.remove('selected'));
            const actual = document.querySelector(`.estado-opcion.${estadoActual}`);
            if (actual) actual.classList.add('selected');
            document.getElementById('modal-estado').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function cerrarModalEstado() {
            document.getElementById('modal-estado').classList.add('hidden');
            document.body.style.overflow = '';
            mesaActualId = null;
            estadoSeleccionado = null;
        }

        function seleccionarEstado(estado) {
            estadoSeleccionado = estado;
            document.querySelectorAll('.estado-opcion').forEach(el => el.classList.remove('selected'));
            document.querySelector(`.estado-opcion.${estado}`).classList.add('selected');
        }

        function confirmarEstado() {
            if (!mesaActualId || !estadoSeleccionado) return;
            fetch(`/mesas/${mesaActualId}/estado`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        estado: estadoSeleccionado
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const card = document.getElementById(`mesa-card-${mesaActualId}`);
                        const badge = document.getElementById(`badge-${mesaActualId}`);
                        if (card) card.className = `mesa-card ${estadoSeleccionado}`;
                        if (badge) badge.textContent = estadoSeleccionado.toUpperCase();
                        cerrarModalEstado();
                        Swal.fire({
                            icon: 'success',
                            title: 'Estado actualizado',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'No se pudo cambiar el estado',
                            confirmButtonColor: '#7e22ce'
                        });
                    }
                });
        }

        // ── Modal código QR ────────────────────────────
        function verQR(id, numero, token) {
            const numeroFmt = String(numero).padStart(2, '0');
            const url = `${window.location.origin}/menu/${token}`;
            const imgSrc = `/mesas/${id}/qr-imagen`;

            document.getElementById('modal-qr-titulo').textContent = `Mesa ${numeroFmt} — Código QR`;
            document.getElementById('qr-modal-img').src = imgSrc;
            document.getElementById('qr-modal-link').textContent = url;

            const btnDescargar = document.getElementById('qr-modal-descargar');
            btnDescargar.href = imgSrc;
            btnDescargar.setAttribute('download', `mesa-${numeroFmt}-qr.svg`);

            document.getElementById('modal-qr').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function cerrarModalQR() {
            document.getElementById('modal-qr').classList.add('hidden');
            document.body.style.overflow = '';
        }

        function imprimirQR() {
            const src = document.getElementById('qr-modal-img').src;
            const titulo = document.getElementById('modal-qr-titulo').textContent;
            const ventana = window.open('', '_blank');
            ventana.document.write(`
                <html>
                <head><title>${titulo}</title></head>
                <body style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100vh;margin:0;font-family:sans-serif">
                    <h2>${titulo}</h2>
                    <img src="${src}" style="width:300px" onload="window.print();">
                </body>
                </html>
            `);
            ventana.document.close();
        }

        // ── Eliminar ──────────────────────────────────
        function eliminarMesa(id, numero) {
            Swal.fire({
                title: '¿Eliminar mesa?',
                text: `Se eliminará la Mesa ${String(numero).padStart(2,'0')} permanentemente.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('form-eliminar-mesa');
                    form.action = `/mesas/${id}`;
                    form.submit();
                }
            });
        }

        // ── Entregar plato ────────────────────────────
        function entregarPlato(detalleId, mesaId) {
            fetch(`/pedidos/detalle/${detalleId}/entregar`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const el = document.getElementById(`detalle-${detalleId}`);
                        if (el) el.remove();
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: data.message,
                            confirmButtonColor: '#7e22ce'
                        });
                    }
                });
        }

        // Cerrar modales al click fuera
        document.getElementById('modal-nueva').addEventListener('click', e => {
            if (e.target === e.currentTarget) cerrarModalNueva();
        });
        document.getElementById('modal-estado').addEventListener('click', e => {
            if (e.target === e.currentTarget) cerrarModalEstado();
        });
        document.getElementById('modal-qr').addEventListener('click', e => {
            if (e.target === e.currentTarget) cerrarModalQR();
        });

        function liberarMesaManual(mesaId, numero) {
            Swal.fire({
                title: `¿Liberar Mesa ${String(numero).padStart(2,'0')}?`,
                text: 'La mesa quedará libre para nuevos clientes. El cobro pendiente seguirá disponible en Caja.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#7e22ce',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, liberar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/mesas/${mesaId}/liberar-manual`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Content-Type': 'application/json',
                            }
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                        icon: 'success',
                                        title: data.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    })
                                    .then(() => actualizarMesas());
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: data.message,
                                    confirmButtonColor: '#7e22ce'
                                });
                            }
                        });
                }
            });
        }

        // ── Auto-actualización silenciosa (AJAX, sin recargar página) ────
        const VER_TODO = {{ $verTodo ? 'true' : 'false' }};

        function renderMesaCard(mesa) {
            let bodyHtml = `
        <div class="mesa-info-row">
            <i class="fa-solid fa-users"></i>
            <span>Capacidad: ${mesa.capacidad} personas</span>
        </div>`;

            if (mesa.estado === 'ocupada') {
                if (mesa.esMia) {
                    bodyHtml += `
                <div class="mesa-info-row">
                    <i class="fa-solid fa-clipboard-list"></i>
                    <span>Con pedido activo</span>
                </div>`;

                    if (mesa.detalles.length > 0) {
                        bodyHtml += `<div class="mesa-platos-lista">`;
                        mesa.detalles.forEach(d => {
                            const accionHtml = d.estado === 'listo' ?
                                `<button onclick="entregarPlato(${d.detalle_id}, ${mesa.id})" class="mesa-plato-btn" title="Marcar como entregado">
                                       <i class="fa-solid fa-check"></i>
                                   </button>` :
                                `<span class="mesa-plato-preparando" title="Aún en preparación">
                                       <i class="fa-solid fa-clock text-gray-400"></i>
                                   </span>`;

                            bodyHtml += `
                        <div class="mesa-plato-item" id="detalle-${d.detalle_id}">
                            <div class="mesa-plato-info">
                                <span class="mesa-plato-qty">${d.cantidad}x</span>
                                <span class="mesa-plato-nombre">${d.producto_nombre}</span>
                                ${d.observacion ? `<span class="mesa-plato-obs">· ${d.observacion}</span>` : ''}
                            </div>
                            ${accionHtml}
                        </div>`;
                        });
                        bodyHtml += `</div>`;
                    } else {
                        bodyHtml += `
                    <div class="mesa-info-row">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>Todo entregado — esperando cobro</span>
                    </div>`;
                    }

                    bodyHtml += `
                <a href="/pedidos/nuevo?mesa_id=${mesa.id}" class="mesa-btn-agregar-pedido">
                    <i class="fa-solid fa-plus"></i> Agregar Pedido
                </a>
                <button onclick="liberarMesaManual(${mesa.id}, ${mesa.numero})" class="mesa-btn-liberar">
                    <i class="fa-solid fa-door-open"></i> Liberar Mesa
                </button>`;
                } else {
                    bodyHtml += `
                <div class="mesa-info-row">
                    <i class="fa-solid fa-user-clock"></i>
                    <span>Atendida por otro mesero</span>
                </div>`;
                }
            } else if (mesa.estado === 'reservada') {
                bodyHtml += `
            <div class="mesa-info-row">
                <i class="fa-solid fa-calendar-days"></i>
                <span>Reserva programada</span>
            </div>`;
            } else {
                bodyHtml += `
            <div class="mesa-info-row">
                <i class="fa-solid fa-circle-check"></i>
                <span>Disponible</span>
            </div>`;
            }

            let footerHtml = `
            <button onclick="verQR(${mesa.id}, ${mesa.numero}, '${mesa.qr_token}')" class="mesa-btn-qr">
                <i class="fa-solid fa-qrcode"></i> Ver QR
            </button>`;

            if (VER_TODO) {
                footerHtml += `
            <button onclick="abrirModalEstado(${mesa.id}, ${mesa.numero}, '${mesa.estado}')" class="mesa-btn-estado">
                <i class="fa-solid fa-rotate"></i> Cambiar estado
            </button>
            <div class="mesa-card-footer-row">
                <button onclick="abrirModalEditar(${mesa.id}, ${mesa.numero}, ${mesa.capacidad})" class="btn-edit">
                    <i class="fa-solid fa-pen"></i> Editar
                </button>
                <button onclick="eliminarMesa(${mesa.id}, ${mesa.numero})" class="btn-delete">
                    <i class="fa-solid fa-trash"></i> Eliminar
                </button>
            </div>`;
            }

            return `
        <div class="mesa-card ${mesa.estado}" id="mesa-card-${mesa.id}">
            <div class="mesa-card-top">
                <div class="mesa-numero">${String(mesa.numero).padStart(2, '0')}</div>
                <span class="mesa-estado-badge" id="badge-${mesa.id}">${mesa.estado.toUpperCase()}</span>
            </div>
            <div class="mesa-card-body">${bodyHtml}</div>
            <div class="mesa-card-footer">${footerHtml}</div>
        </div>`;
        }

        function actualizarMesas() {
            fetch('{{ route('mesas.actualizar') }}')
                .then(r => r.json())
                .then(data => {
                    const elLibres = document.getElementById('resumen-libres');
                    const elOcupadas = document.getElementById('resumen-ocupadas');
                    const elReservadas = document.getElementById('resumen-reservadas');
                    const elTotal = document.getElementById('resumen-total');
                    if (elLibres) elLibres.textContent = data.resumen.libres;
                    if (elOcupadas) elOcupadas.textContent = data.resumen.ocupadas;
                    if (elReservadas) elReservadas.textContent = data.resumen.reservadas;
                    if (elTotal) elTotal.textContent = data.resumen.total;

                    const grid = document.getElementById('mesas-grid');
                    if (grid) {
                        grid.innerHTML = data.mesas.map(renderMesaCard).join('');
                    }
                })
                .catch(err => console.error('Error al actualizar mesas:', err));
        }

        setInterval(actualizarMesas, 3000);
    </script>

@endsection