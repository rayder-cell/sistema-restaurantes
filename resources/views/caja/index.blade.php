@extends('layouts.app')

@push('styles')
    @vite(['resources/assets/css/pages/crud.css'])
@endpush

@section('title', 'Caja y Pagos — MIKHUNAWASI')
@section('page-title', 'Caja y Pagos')

@section('content')

    {{-- Stats del día --}}
    <div class="caja-stats-grid">
        <div class="caja-stat-card purple">
            <p class="caja-stat-label">VENTAS HOY</p>
            <p class="caja-stat-value" id="ventas-hoy-stat">S/ {{ number_format($ventasHoy->total_ventas ?? 0, 2) }}</p>
        </div>
        <div class="caja-stat-card green">
            <p class="caja-stat-label">COMPROBANTES HOY</p>
            <p class="caja-stat-value" id="comprobantes-hoy-stat">{{ $ventasHoy->total_comprobantes ?? 0 }}</p>
        </div>
        <div class="caja-stat-card {{ $apertura ? 'green' : 'red' }}">
            <p class="caja-stat-label">ESTADO CAJA</p>
            <p class="caja-stat-value text-base">
                @if ($apertura)
                    <i class="fa-solid fa-circle" style="color:#16a34a;font-size:0.7em;"></i> Abierta
                @else
                    <i class="fa-solid fa-circle" style="color:#dc2626;font-size:0.7em;"></i> Cerrada
                @endif
            </p>
        </div>
        <div class="caja-stat-card blue">
            <p class="caja-stat-label">MESAS POR COBRAR</p>
            <p class="caja-stat-value" id="mesas-pendientes-stat">{{ $mesasACobrar->count() }}</p>
        </div>
    </div>

    {{-- Apertura/Cierre de caja --}}
    @if (!$apertura)
        <div class="caja-apertura-banner">
            <div>
                <p class="font-semibold text-gray-700"><i class="fa-solid fa-triangle-exclamation"
                        style="color:#d97706;"></i> Caja cerrada</p>
                <p class="text-sm text-gray-500">Abre la caja para comenzar a registrar pagos.</p>
            </div>
            @if ($caja)
                <button onclick="abrirModalCaja()" class="btn-create">Abrir caja</button>
            @else
                <p class="text-sm text-red-500">No hay caja configurada para este restaurante.</p>
            @endif
        </div>
    @else
        <div class="caja-apertura-banner green">
            <div>
                <p class="font-semibold text-gray-700"><i class="fa-solid fa-circle-check" style="color:#16a34a;"></i> Caja
                    abierta desde
                    {{ \Carbon\Carbon::parse($apertura->apertura_at)->format('H:i') }}</p>
                <p class="text-sm text-gray-500">Monto inicial: S/ {{ number_format($apertura->monto_inicial, 2) }}</p>
            </div>
            <button onclick="abrirModalCierre()" class="btn-delete px-4 py-2 rounded-lg">Cerrar caja</button>
        </div>
    @endif

    {{-- Layout principal --}}
    <div class="caja-layout">

        {{-- Panel izquierdo: cuentas por cobrar --}}
        <div class="caja-panel-left">
            <div class="caja-panel-header">
                <h3 class="caja-panel-title">Mesas Activas</h3>
                <span class="caja-badge-pending" id="badge-pendientes"
                    style="{{ $mesasACobrar->count() === 0 ? 'display:none' : '' }}">
                    {{ $mesasACobrar->count() }} PENDIENTES
                </span>
            </div>

            <div id="lista-mesas-cobrar">
                @forelse($mesasACobrar as $mesa)
                    <div class="caja-mesa-item" id="mesa-item-{{ $mesa->clave }}"
                        onclick="seleccionarCuenta('{{ $mesa->clave }}', this)">
                        <div class="flex items-center justify-between">
                            <p class="caja-mesa-nombre">
                                Mesa {{ $mesa->mesa_numero ?? 'S/N' }}
                                @if ($mesa->liberada)
                                    <span class="text-xs text-orange-500">(liberada)</span>
                                @endif
                            </p>
                            <p class="caja-mesa-total">S/ {{ number_format($mesa->total, 2) }}</p>
                        </div>
                        @if ($mesa->cliente_nombre)
                            <p class="caja-mesa-cliente text-xs text-gray-500">{{ $mesa->cliente_nombre }}
                                {{ $mesa->cliente_apellidos }}</p>
                        @endif
                        <p class="caja-mesa-tiempo">{{ \Carbon\Carbon::parse($mesa->created_at)->diffForHumans() }}</p>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-400 text-sm">
                        <p class="text-2xl mb-2"><i class="fa-solid fa-circle-check"></i></p>
                        <p>No hay pedidos pendientes de cobro</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Panel central: detalle de la cuenta --}}
        <div class="caja-panel-center" id="panel-detalle">
            <div class="caja-empty-state">
                <p class="text-3xl mb-2"><i class="fa-solid fa-receipt"></i></p>
                <p class="text-gray-400">Selecciona una mesa para ver el detalle</p>
            </div>
        </div>

        {{-- Panel derecho: método de pago --}}
        <div class="caja-panel-right" id="panel-pago">
            <div class="caja-panel-header">
                <h3 class="caja-panel-title"><i class="fa-solid fa-credit-card"></i> Método de Pago</h3>
            </div>

            <form id="form-pago">
                @csrf
                <input type="hidden" name="pedido_ids" id="pago-pedido-ids">
                <input type="hidden" name="culqi_token" id="culqi-token">

                {{-- Método --}}
                <div class="caja-metodos-grid">
                    <label class="caja-metodo-btn">
                        <input type="radio" name="metodo" value="efectivo" checked class="hidden">
                        <div class="caja-metodo-inner active" data-metodo="efectivo">
                            <span class="text-xl"><i class="fa-solid fa-money-bill-wave"></i></span>
                            <span>Efectivo</span>
                        </div>
                    </label>
                    <label class="caja-metodo-btn">
                        <input type="radio" name="metodo" value="tarjeta" class="hidden">
                        <div class="caja-metodo-inner" data-metodo="tarjeta">
                            <span class="text-xl"><i class="fa-solid fa-credit-card"></i></span>
                            <span>Tarjeta</span>
                        </div>
                    </label>
                    <label class="caja-metodo-btn">
                        <input type="radio" name="metodo" value="yape" class="hidden">
                        <div class="caja-metodo-inner" data-metodo="yape">
                            <span class="text-xl"><i class="fa-solid fa-mobile-screen"></i></span>
                            <span>Yape/Plin</span>
                        </div>
                    </label>
                </div>

                {{-- Monto recibido (solo aplica a efectivo) --}}
                <div class="caja-form-group" id="grupo-monto-recibido">
                    <label class="caja-form-label">MONTO RECIBIDO (S/)</label>
                    <input type="number" name="monto_recibido" id="monto-recibido" class="caja-monto-input"
                        step="0.01" min="0" value="0.00" oninput="calcularVuelto()">
                </div>

                {{-- Vuelto (solo aplica a efectivo) --}}
                <div class="caja-vuelto-box" id="grupo-vuelto">
                    <p class="caja-form-label">CAMBIO (VUELTO)</p>
                    <p class="caja-vuelto-valor" id="vuelto-display">S/ 0.00</p>
                </div>

                {{-- Datos para tarjeta --}}
                <div class="caja-form-group" id="aviso-tarjeta" style="display:none">
                    <label class="caja-form-label">EMAIL DEL CLIENTE</label>
                    <input type="email" name="cliente_email" id="pago-cliente-email" class="form-input"
                        placeholder="cliente@correo.com">
                    <p class="text-sm text-gray-500 mt-1"><i class="fa-solid fa-circle-info"></i> Se abrirá la ventana
                        segura de Culqi para procesar el cobro con tarjeta.</p>
                </div>

                {{-- Tipo comprobante --}}
                <div class="caja-tipo-grid">
                    @foreach ($series as $s)
                        <label class="caja-tipo-btn">
                            <input type="radio" name="tipo" value="{{ $s->tipo }}" class="hidden"
                                {{ $loop->first ? 'checked' : '' }}>
                            <div class="caja-tipo-inner {{ $loop->first ? 'active' : '' }}">
                                {{ ucfirst($s->tipo) }}
                                <span class="text-xs text-gray-400">{{ $s->serie }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>

                {{-- Cliente (para factura) --}}
                <div class="caja-form-group" id="datos-cliente" style="display:none">
                    <input type="text" name="cliente_nombre" placeholder="Razón social" class="form-input mb-2">
                    <input type="text" name="cliente_ruc" placeholder="RUC (11 dígitos)" class="form-input"
                        maxlength="11">
                </div>

                <button type="submit" id="btn-emitir" class="caja-btn-emitir" disabled>
                    <i class="fa-solid fa-print"></i> Emitir Comprobante
                </button>
            </form>
        </div>

    </div>

    {{-- Modal abrir caja --}}
    @if ($caja)
        <div id="modal-abrir-caja" class="sa-modal-overlay hidden">
            <div class="sa-modal" style="max-width:400px">
                <div class="sa-modal-header">
                    <div class="sa-modal-header-left"><span><i class="fa-solid fa-sack-dollar"></i></span>
                        <h3>Abrir Caja — {{ $caja->nombre }}</h3>
                    </div>
                    <button onclick="cerrarModalCaja()" class="sa-modal-close"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <form method="POST" action="{{ route('caja.abrir') }}" class="sa-modal-form">
                    @csrf
                    <input type="hidden" name="caja_id" value="{{ $caja->id }}">
                    <div class="sa-form-group">
                        <label class="sa-form-label">Monto inicial en caja (S/)</label>
                        <input type="number" name="monto_inicial" class="sa-form-input" step="0.01" min="0"
                            value="0" required>
                    </div>
                    <div class="sa-modal-actions">
                        <button type="button" onclick="cerrarModalCaja()" class="sa-btn-cancelar">Cancelar</button>
                        <button type="submit" class="sa-btn-confirmar">Abrir caja</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal cerrar caja --}}
    @if ($apertura)
        <div id="modal-cierre-caja" class="sa-modal-overlay hidden">
            <div class="sa-modal" style="max-width:400px">
                <div class="sa-modal-header">
                    <div class="sa-modal-header-left"><span><i class="fa-solid fa-lock"></i></span>
                        <h3>Cerrar Caja</h3>
                    </div>
                    <button onclick="cerrarModalCierre()" class="sa-modal-close"><i
                            class="fa-solid fa-xmark"></i></button>
                </div>
                <form method="POST" action="{{ route('caja.cerrar') }}" class="sa-modal-form">
                    @csrf
                    <input type="hidden" name="apertura_id" value="{{ $apertura->id }}">
                    <div class="sa-form-group">
                        <label class="sa-form-label">Monto contado en caja (S/)</label>
                        <input type="number" name="monto_cierre" class="sa-form-input" step="0.01" min="0"
                            required>
                    </div>
                    <div class="sa-form-group">
                        <label class="sa-form-label">Justificación (opcional)</label>
                        <textarea name="justificacion" class="sa-form-input" rows="2" placeholder="En caso de diferencia..."></textarea>
                    </div>
                    <div class="sa-modal-actions">
                        <button type="button" onclick="cerrarModalCierre()" class="sa-btn-cancelar">Cancelar</button>
                        <button type="submit" class="sa-btn-confirmar">Cerrar caja</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Culqi Checkout.js --}}
    <script src="https://checkout.culqi.com/js/v4"></script>

    <script>
        const CULQI_PUBLIC_KEY = "{{ config('culqi.public_key') }}";
        Culqi.publicKey = CULQI_PUBLIC_KEY;

        let totalMesaActual = 0;
        let metodoSeleccionado = 'efectivo';

        function seleccionarCuenta(clave, el) {
            fetch(`/caja/cuenta/${clave}`)
                .then(r => r.json())
                .then(data => {
                    totalMesaActual = parseFloat(data.total);

                    const nombreCliente = data.cliente_nombre ?
                        `${data.cliente_nombre} ${data.cliente_apellidos ?? ''}`.trim() :
                        'Sin cliente asignado';

                    const etiquetaMesa = data.liberada ?
                        `Mesa ${data.mesa_numero ?? 'S/N'} (liberada)` :
                        `Mesa ${data.mesa_numero ?? 'S/N'}`;

                    let html = `
                <div class="caja-panel-header">
                    <h3 class="caja-panel-title">Resumen: ${etiquetaMesa}</h3>
                    <span class="text-xs text-gray-400">${nombreCliente}</span>
                </div>
                <div class="caja-detalles-list">`;

                    data.detalles.forEach(d => {
                        const subtotalLinea = parseFloat(d.precio_unitario) * parseFloat(d.cantidad);
                        html += `
                <div class="caja-detalle-row">
                    <div>
                        <p class="caja-detalle-nombre">${d.producto_nombre}</p>
                        <p class="caja-detalle-cant">x${d.cantidad}</p>
                    </div>
                    <p class="caja-detalle-precio">S/ ${subtotalLinea.toFixed(2)}</p>
                </div>`;
                    });

                    const subtotal = (totalMesaActual / 1.18).toFixed(2);
                    const igv = (totalMesaActual - subtotal).toFixed(2);

                    html += `</div>
                <div class="caja-total-box">
                    <div class="caja-total-row"><span>Subtotal</span><span>S/ ${subtotal}</span></div>
                    <div class="caja-total-row"><span>IGV (18%)</span><span>S/ ${igv}</span></div>
                    <div class="caja-total-final"><span>TOTAL A COBRAR</span><span>S/ ${totalMesaActual.toFixed(2)}</span></div>
                </div>`;

                    document.getElementById('panel-detalle').innerHTML = html;
                    document.getElementById('pago-pedido-ids').value = JSON.stringify(data.pedido_ids);
                    document.getElementById('monto-recibido').value = totalMesaActual.toFixed(2);
                    document.getElementById('btn-emitir').disabled = false;
                    calcularVuelto();

                    document.querySelectorAll('.caja-mesa-item').forEach(item => item.classList.remove('active'));
                    if (el) el.classList.add('active');
                });
        }

        function calcularVuelto() {
            const recibido = parseFloat(document.getElementById('monto-recibido').value) || 0;
            const vuelto = Math.max(0, recibido - totalMesaActual);
            document.getElementById('vuelto-display').textContent = 'S/ ' + vuelto.toFixed(2);
        }

        // Métodos de pago
        document.querySelectorAll('[data-metodo]').forEach(el => {
            el.addEventListener('click', () => {
                document.querySelectorAll('[data-metodo]').forEach(e => e.classList.remove('active'));
                el.classList.add('active');
                document.getElementById('datos-cliente').style.display = 'none';

                metodoSeleccionado = el.getAttribute('data-metodo');

                const esTarjeta = metodoSeleccionado === 'tarjeta';
                document.getElementById('grupo-monto-recibido').style.display = esTarjeta ? 'none' : '';
                document.getElementById('grupo-vuelto').style.display = esTarjeta ? 'none' : '';
                document.getElementById('aviso-tarjeta').style.display = esTarjeta ? '' : 'none';
                document.getElementById('pago-cliente-email').required = esTarjeta;

                if (esTarjeta) {
                    document.getElementById('monto-recibido').value = totalMesaActual.toFixed(2);
                }
            });
        });

        // Tipos de comprobante
        document.querySelectorAll('input[name="tipo"]').forEach(radio => {
            radio.addEventListener('change', () => {
                document.querySelectorAll('.caja-tipo-inner').forEach(e => e.classList.remove('active'));
                radio.nextElementSibling.classList.add('active');
                document.getElementById('datos-cliente').style.display =
                    radio.value === 'factura' ? 'block' : 'none';
            });
        });

        function abrirModalCaja() {
            document.getElementById('modal-abrir-caja').classList.remove('hidden');
        }

        function cerrarModalCaja() {
            document.getElementById('modal-abrir-caja').classList.add('hidden');
        }

        function abrirModalCierre() {
            document.getElementById('modal-cierre-caja').classList.remove('hidden');
        }

        function cerrarModalCierre() {
            document.getElementById('modal-cierre-caja').classList.add('hidden');
        }

        // ── Callback de Culqi: se dispara cuando el widget genera el token ──
        Culqi.token = function() {
            if (Culqi.token) {
                const token = Culqi.token.id;
                document.getElementById('culqi-token').value = token;
                enviarFormularioPago();
            } else if (Culqi.error) {
                Swal.fire({
                    icon: 'error',
                    title: 'No se pudo procesar la tarjeta',
                    text: Culqi.error.user_message || 'Verifica los datos e intenta de nuevo.',
                    confirmButtonColor: '#7e22ce'
                });
                const btn = document.getElementById('btn-emitir');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-print"></i> Emitir Comprobante';
            }
        };

        // ── Envío del formulario, ahora separado para reusarlo tras el token de Culqi ──
        document.getElementById('form-pago').addEventListener('submit', function(e) {
            e.preventDefault();

            const btn = document.getElementById('btn-emitir');

            if (metodoSeleccionado === 'tarjeta' && !document.getElementById('culqi-token').value) {
                const email = document.getElementById('pago-cliente-email').value.trim();
                if (!email) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Falta el email del cliente',
                        text: 'Culqi requiere un correo para procesar el pago con tarjeta.',
                        confirmButtonColor: '#7e22ce'
                    });
                    return;
                }

                // Aún no hay token: abrir el widget de Culqi en vez de enviar directo
                btn.disabled = true;
                btn.textContent = 'Abriendo pasarela...';

                Culqi.settings({
                    title: 'MIKHUNAWASI',
                    currency: 'PEN',
                    amount: Math.round(totalMesaActual * 100),
                });
                Culqi.options({
                    lang: 'auto',
                    installments: false,
                    paymentMethods: {
                        tarjeta: true,
                        yape: false,
                        bancaMovil: false,
                        billeteraMovil: false,
                        agente: false,
                        cuotealo: false,
                    },
                });
                Culqi.open();

                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-print"></i> Emitir Comprobante';
                return;
            }

            enviarFormularioPago();
        });

        function enviarFormularioPago() {
            const btn = document.getElementById('btn-emitir');
            btn.disabled = true;
            btn.textContent = 'Emitiendo...';

            const formData = new FormData(document.getElementById('form-pago'));

            fetch('{{ route('caja.emitir') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData,
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const itemActivo = document.querySelector('.caja-mesa-item.active');
                        if (itemActivo) itemActivo.remove();

                        const restantes = document.querySelectorAll('.caja-mesa-item').length;
                        const badge = document.getElementById('badge-pendientes');
                        const statMesas = document.getElementById('mesas-pendientes-stat');
                        statMesas.textContent = restantes;

                        if (restantes > 0) {
                            badge.style.display = '';
                            badge.textContent = restantes + ' PENDIENTES';
                        } else {
                            badge.style.display = 'none';
                            document.getElementById('lista-mesas-cobrar').innerHTML = `
                                <div class="text-center py-8 text-gray-400 text-sm">
                                    <p class="text-2xl mb-2"><i class="fa-solid fa-circle-check"></i></p>
                                    <p>No hay pedidos pendientes de cobro</p>
                                </div>`;
                        }

                        document.getElementById('panel-detalle').innerHTML = `
                            <div class="caja-empty-state">
                                <p class="text-3xl mb-2"><i class="fa-solid fa-receipt"></i></p>
                                <p class="text-gray-400">Selecciona una mesa para ver el detalle</p>
                            </div>`;
                        document.getElementById('form-pago').reset();
                        document.getElementById('culqi-token').value = '';
                        btn.disabled = true;

                        Swal.fire({
                            icon: 'success',
                            title: data.message,
                            timer: 2500,
                            showConfirmButton: false
                        });

                        window.open(`/caja/comprobante/${data.comprobante_id}/pdf`, '_blank');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: data.message,
                            confirmButtonColor: '#7e22ce'
                        });
                        btn.disabled = false;
                        document.getElementById('culqi-token').value = '';
                    }
                    btn.innerHTML = '<i class="fa-solid fa-print"></i> Emitir Comprobante';
                })
                .catch(err => {
                    console.error('Error al emitir comprobante:', err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al emitir el comprobante',
                        confirmButtonColor: '#7e22ce'
                    });
                    btn.disabled = false;
                    document.getElementById('culqi-token').value = '';
                    btn.innerHTML = '<i class="fa-solid fa-print"></i> Emitir Comprobante';
                });
        }
    </script>

@endsection