@extends('layouts.superadmin')

@section('title', 'Panel SaaS Global — MIKHUNAWASI')

@section('content')

    {{-- Encabezado --}}
    <div class="sa-page-header">
        <div>
            <p class="sa-page-sub">MIKHUNAWASI</p>
            <h2 class="sa-page-title">Control de Suscriptores</h2>
        </div>
        <div class="sa-page-date">Hoy: {{ now()->timezone('America/Lima')->format('d/m/Y') }}</div>
    </div>

    {{-- Métricas --}}
    <div class="sa-metrics-grid">

        <div class="sa-metric-card">
            <div>
                <p class="sa-metric-label">TOTAL RESTAURANTES</p>
                <p class="sa-metric-value">{{ $totalRestaurantes }}</p>
                <p class="sa-metric-sub {{ $nuevosEsteMes > 0 ? 'green' : '' }}">
                    +{{ $nuevosEsteMes }} este mes
                </p>
            </div>
            <div class="sa-metric-icon purple">
                <i class="fa-solid fa-store"></i>
            </div>
        </div>

        <div class="sa-metric-card">
            <div>
                <p class="sa-metric-label">ACTIVOS EN SUNAT</p>
                <p class="sa-metric-value green">{{ $activos }}</p>
                <p class="sa-metric-sub">{{ $porcentajeActivos }}% del total</p>
            </div>
            <div class="sa-metric-icon teal">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
        </div>

        <div class="sa-metric-card">
            <div>
                <p class="sa-metric-label">ESTATUS SUSPENDIDOS</p>
                <p class="sa-metric-value {{ $suspendidos > 0 ? 'red' : '' }}">{{ $suspendidos }}</p>
                @if ($suspendidos > 0)
                    <p class="sa-metric-sub red">Requiere Acción</p>
                @else
                    <p class="sa-metric-sub">Sin problemas</p>
                @endif
            </div>
            <div class="sa-metric-icon red">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
        </div>

    </div>

    {{-- Tabla --}}
    <div class="sa-card">

        {{-- Toolbar --}}
        <div class="sa-card-toolbar">
            <div class="sa-filter-group">
                <div class="sa-filter-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="sa-filter-ico">
                        <line x1="4" y1="6" x2="20" y2="6" />
                        <line x1="8" y1="12" x2="16" y2="12" />
                        <line x1="11" y1="18" x2="13" y2="18" />
                    </svg>
                    <input type="text" id="filtro-tabla" placeholder="Filtrar por RUC o Nombre..."
                        class="sa-filter-input" onkeyup="filtrarTabla()">
                </div>

                {{-- Filtro de estado --}}
                <div class="sa-estado-wrap" id="estado-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="sa-estado-dot-ico"
                        id="estado-dot-ico">
                        <circle cx="12" cy="12" r="5" />
                    </svg>
                    <select id="filtro-estado" onchange="filtrarTabla()" class="sa-filter-select">
                        <option value="">Todos los estados</option>
                        <option value="activo">Activo</option>
                        <option value="suspendido">Suspendido</option>
                    </select>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        class="sa-estado-chevron">
                        <polyline points="6 9 12 15 18 9" />
                    </svg>
                </div>
            </div>

            <button onclick="abrirModal()" class="sa-btn-registrar">
                <i class="fa-solid fa-store"></i> Registrar Restaurante
            </button>
        </div>

        {{-- Tabla --}}
        <table class="sa-table" id="tabla-restaurantes">
            <thead>
                <tr>
                    <th>RESTAURANTE / RUC</th>
                    <th>COMPROBANTES SERIES</th>
                    <th>ESTADO</th>
                    <th>ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                @forelse($restaurantes as $r)
                    <tr data-nombre="{{ strtolower($r->nombre) }}" data-ruc="{{ $r->ruc }}"
                        data-estado="{{ $r->estado }}">
                        <td>
                            <div class="sa-rest-cell">
                                <div class="sa-rest-avatar">
                                    @if ($r->logo_url)
                                        <img src="{{ $r->logo_url }}" alt="logo">
                                    @else
                                        <i class="fa-solid fa-utensils"></i>
                                    @endif
                                </div>
                                <div>
                                    <p class="sa-rest-nombre">{{ $r->nombre }}</p>
                                    <p class="sa-rest-ruc">RUC: {{ $r->ruc }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $boleta = $r->seriesComprobante->where('tipo', 'boleta')->first();
                                $factura = $r->seriesComprobante->where('tipo', 'factura')->first();
                            @endphp
                            @if ($boleta || $factura)
                                <div class="sa-series-cell">
                                    @if ($boleta)
                                        <div class="sa-series-row">
                                            <span class="sa-series-tipo">Boleta:</span>
                                            <span class="sa-series-tag blue">
                                                {{ $boleta->serie }} ({{ $boleta->correlativo_actual }})
                                            </span>
                                        </div>
                                    @endif
                                    @if ($factura)
                                        <div class="sa-series-row">
                                            <span class="sa-series-tipo">Factura:</span>
                                            <span class="sa-series-tag purple">
                                                {{ $factura->serie }} ({{ $factura->correlativo_actual }})
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <span class="sa-series-expirada">Licencia Expirada</span>
                            @endif
                        </td>
                        <td>
                            @if ($r->estado === 'activo')
                                <span class="sa-estado-activo">● Activo</span>
                            @elseif($r->estado === 'suspendido')
                                <span class="sa-estado-suspendido">● Suspendido</span>
                            @else
                                <span class="sa-estado-inactivo">● Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <div class="sa-acciones-cell">
                                {{-- Ver --}}
                                <a href="{{ route('superadmin.restaurantes.show', $r->id) }}"
                                    class="sa-btn-accion sa-btn-ver" title="Ver detalle">
                                    <i class="fa-solid fa-eye"></i>
                                </a>

                                {{-- Editar --}}
                                <a href="{{ route('superadmin.restaurantes.edit', $r->id) }}"
                                    class="sa-btn-accion sa-btn-editar" title="Editar">
                                    <i class="fa-solid fa-pen"></i>
                                </a>

                                {{-- Suspender / Reactivar con SweetAlert2 --}}
                                @if ($r->estado === 'activo')
                                    <button type="button"
                                        onclick="confirmarSuspender({{ $r->id }}, '{{ addslashes($r->nombre) }}')"
                                        class="sa-btn-suspender">
                                        SUSPENDER
                                    </button>
                                @else
                                    <button type="button"
                                        onclick="confirmarReactivar({{ $r->id }}, '{{ addslashes($r->nombre) }}')"
                                        class="sa-btn-reactivar">
                                        REACTIVAR
                                    </button>
                                @endif

                                {{-- Eliminar con SweetAlert2 --}}
                                <button type="button"
                                    onclick="confirmarEliminar({{ $r->id }}, '{{ addslashes($r->nombre) }}')"
                                    class="sa-btn-accion sa-btn-eliminar" title="Eliminar">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="sa-table-empty">
                            <p><i class="fa-solid fa-store text-2xl text-gray-300"></i></p>
                            <p>No hay restaurantes registrados</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Footer --}}
        <div class="sa-card-footer">
            <span>Mostrando {{ $restaurantes->count() }} de {{ $totalRestaurantes }} suscriptores totales</span>
            <div class="sa-pagination">
                @if ($restaurantes->onFirstPage())
                    <span class="sa-page-btn disabled">‹</span>
                @else
                    <a href="{{ $restaurantes->previousPageUrl() }}" class="sa-page-btn">‹</a>
                @endif
                <span class="sa-page-btn active">{{ $restaurantes->currentPage() }}</span>
                @if ($restaurantes->hasMorePages())
                    <a href="{{ $restaurantes->nextPageUrl() }}" class="sa-page-btn">›</a>
                @else
                    <span class="sa-page-btn disabled">›</span>
                @endif
            </div>
        </div>

    </div>

    {{-- MODAL: Registrar Nuevo Tenant --}}
    <div id="modal-registrar" class="sa-modal-overlay hidden">
        <div class="sa-modal">
            <div class="sa-modal-header">
                <div class="sa-modal-header-left">
                    <span><i class="fa-solid fa-store"></i></span>
                    <h3>Registrar Nuevo Restaurante</h3>
                </div>
                <button onclick="cerrarModal()" class="sa-modal-close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('superadmin.restaurantes.store') }}" class="sa-modal-form"
                enctype="multipart/form-data" id="form-registrar">
                @csrf
                <input type="hidden" name="_modal" value="registrar">

                <div class="sa-form-row-2">
                    <div class="sa-form-group">
                        <label class="sa-form-label">Nombre del Restaurante *</label>
                        <input type="text" id="r_nombre" name="nombre" placeholder="E.g., Rústica Sabor"
                            class="sa-form-input" value="{{ old('nombre') }}" autocomplete="off">
                        <p class="sa-field-error hidden" id="err_nombre">
                            @error('nombre')
                                {{ $message }}
                            @enderror
                        </p>
                    </div>
                    <div class="sa-form-group">
                        <label class="sa-form-label">RUC Comercial *</label>
                        <input type="text" id="r_ruc" name="ruc" placeholder="E.g., 20123456789"
                            class="sa-form-input" value="{{ old('ruc') }}" maxlength="11" inputmode="numeric"
                            autocomplete="off">
                        <span id="ruc-status-icon" class="ruc-status-icon"></span>
                        <p class="sa-field-error hidden" id="err_ruc">
                            @error('ruc')
                                {{ $message }}
                            @enderror
                        </p>
                    </div>
                </div>

                <div class="sa-form-group">
                    <label class="sa-form-label">Dirección Fiscal *</label>
                    <input type="text" id="r_direccion" name="direccion"
                        placeholder="E.g., Av. Javier Prado Este 1500, San Isidro, Lima" class="sa-form-input"
                        value="{{ old('direccion') }}" autocomplete="off">
                    <p class="sa-field-error hidden" id="err_direccion">
                        @error('direccion')
                            {{ $message }}
                        @enderror
                    </p>
                </div>

                <div class="sa-form-row-3">
                    <div class="sa-form-group">
                        <label class="sa-form-label">Logo del Restaurante</label>
                        <input type="file" name="logo" accept="image/*" class="sa-form-input">
                    </div>
                    <div class="sa-form-group">
                        <label class="sa-form-label">Boleta Serie</label>
                        <input type="text" id="r_boleta" name="boleta_serie"
                            value="{{ old('boleta_serie', 'B001') }}" class="sa-form-input" maxlength="4">
                        <p class="sa-field-error hidden" id="err_boleta">
                            @error('boleta_serie')
                                {{ $message }}
                            @enderror
                        </p>
                    </div>
                    <div class="sa-form-group">
                        <label class="sa-form-label">Factura Serie</label>
                        <input type="text" id="r_factura" name="factura_serie"
                            value="{{ old('factura_serie', 'F001') }}" class="sa-form-input" maxlength="4">
                        <p class="sa-field-error hidden" id="err_factura">
                            @error('factura_serie')
                                {{ $message }}
                            @enderror
                        </p>
                    </div>
                </div>

                <div class="sa-form-section">
                    <p class="sa-form-section-title"><i class="fa-solid fa-user"></i> Datos del propietario</p>
                    <div class="sa-form-row-2">
                        <div class="sa-form-group">
                            <label class="sa-form-label">Nombre completo *</label>
                            <input type="text" id="r_prop_nombre" name="propietario_nombre" class="sa-form-input"
                                value="{{ old('propietario_nombre') }}" placeholder="Ej: Carlos Pérez"
                                autocomplete="off">
                            <p class="sa-field-error hidden" id="err_prop_nombre">
                                @error('propietario_nombre')
                                    {{ $message }}
                                @enderror
                            </p>
                        </div>
                        <div class="sa-form-group">
                            <label class="sa-form-label">Correo electrónico *</label>
                            <input type="email" id="r_prop_email" name="propietario_email" class="sa-form-input"
                                value="{{ old('propietario_email') }}" placeholder="propietario@correo.com"
                                autocomplete="off">
                            <p class="sa-field-error hidden" id="err_prop_email">
                                @error('propietario_email')
                                    {{ $message }}
                                @enderror
                            </p>
                        </div>
                    </div>
                    <div class="sa-form-group">
                        <label class="sa-form-label">Contraseña *</label>
                        <div class="sa-pass-wrap">
                            <input type="password" id="r_prop_pass" name="propietario_password" class="sa-form-input"
                                autocomplete="new-password" placeholder="Mínimo 8 caracteres">
                            <button type="button" class="sa-pass-toggle" onclick="togglePassword()" tabindex="-1">
                                <i class="fa-solid fa-eye" id="pass-toggle-icon"></i>
                            </button>
                        </div>
                        <p class="sa-field-error hidden" id="err_prop_pass">
                            @error('propietario_password')
                                {{ $message }}
                            @enderror
                        </p>
                        <div class="sa-pass-hints" id="pass-hints">
                            <span class="pass-hint" data-rule="length"><i class="fa-solid fa-circle-xmark"></i> Mínimo 8
                                caracteres</span>
                            <span class="pass-hint" data-rule="upper"><i class="fa-solid fa-circle-xmark"></i> Una
                                mayúscula</span>
                            <span class="pass-hint" data-rule="lower"><i class="fa-solid fa-circle-xmark"></i> Una
                                minúscula</span>
                            <span class="pass-hint" data-rule="number"><i class="fa-solid fa-circle-xmark"></i> Un
                                número</span>
                            <span class="pass-hint" data-rule="special"><i class="fa-solid fa-circle-xmark"></i> Un
                                carácter especial</span>
                        </div>
                    </div>
                </div>

                <div class="sa-modal-actions">
                    <button type="button" onclick="cerrarModal()" class="sa-btn-cancelar">Cancelar</button>
                    <button type="button" onclick="guardarRestaurante()" class="sa-btn-confirmar">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Forms ocultos para acciones POST (evita forms inline en la tabla) --}}
    <form id="form-suspender" method="POST" action="" class="hidden">
        @csrf @method('PATCH')
    </form>
    <form id="form-reactivar" method="POST" action="" class="hidden">
        @csrf @method('PATCH')
    </form>
    <form id="form-eliminar" method="POST" action="" class="hidden">
        @csrf @method('DELETE')
    </form>

    {{-- Estilos de validación del modal --}}
    <style>
        .sa-field-error {
            font-size: 0.75rem;
            color: #DC2626;
            margin-top: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }



        .sa-input-error {
            border-color: #EF4444 !important;
            background-color: #FFF8F8 !important;
            outline: none;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.12) !important;
        }

        .sa-input-error:focus {
            border-color: #EF4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2) !important;
        }

        .sa-metric-icon i {
            font-size: 1.5rem;
        }

        /* Campo de contraseña con botón de mostrar/ocultar */
        .sa-pass-wrap {
            position: relative;
        }

        .sa-pass-wrap .sa-form-input {
            padding-right: 2.5rem;
        }

        .sa-pass-toggle {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            cursor: pointer;
            background: none;
            border: none;
        }

        .sa-pass-toggle:hover {
            color: #4b5563;
        }

        /* Checklist de requisitos de contraseña */
        .sa-pass-hints {
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem 0.75rem;
            margin-top: 0.5rem;
        }

        .pass-hint {
            font-size: 0.75rem;
            color: #9ca3af;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            transition: color 0.15s;
        }

        .pass-hint i {
            font-size: 0.65rem;
        }

        .pass-hint.valid {
            color: #16a34a;
            font-weight: 600;
        }

        /* Resumen de datos en el SweetAlert de confirmación */
        .sa-resumen {
            text-align: left;
            background: #F9FAFB;
            border: 1px solid #F0EDF5;
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            margin-top: 0.5rem;
        }

        .sa-resumen-row {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.35rem 0;
            font-size: 0.8rem;
        }

        .sa-resumen-label {
            color: #6B7280;
            flex-shrink: 0;
        }

        .sa-resumen-val {
            color: #111827;
            font-weight: 600;
            text-align: right;
            word-break: break-word;
        }

        .sa-resumen-divider {
            border-top: 1px dashed #E5E7EB;
            margin: 0.35rem 0;
        }

        .sa-resumen-aviso {
            color: #DC2626;
            font-weight: 600;
            font-size: 0.8rem;
            margin-top: 0.75rem;
            text-align: center;
        }
    </style>

    {{-- Scripts inline (el layout superadmin no usa @stack) --}}
    <script>
        // ── Configuración base compartida ────────────────────────────────────────
        const swalBase = {
            customClass: {
                confirmButton: 'sa-swal-confirm',
                cancelButton: 'sa-swal-cancel',
            },
            buttonsStyling: false, // usamos nuestras clases CSS
            reverseButtons: true,
        };

        // ── Suspender ────────────────────────────────────────────────────────────
        function confirmarSuspender(id, nombre) {
            Swal.fire({
                ...swalBase,
                title: '¿Suspender restaurante?',
                html: `El restaurante <strong>${nombre}</strong> quedará inactivo.<br>Sus usuarios no podrán acceder al sistema.`,
                icon: 'warning',
                iconColor: '#EF4444',
                confirmButtonText: 'Sí, suspender',
                cancelButtonText: 'Cancelar',
                focusCancel: true,
            }).then(result => {
                if (!result.isConfirmed) return;
                document.getElementById('form-suspender').action =
                    `{{ url('superadmin/restaurantes') }}/${id}/suspender`;
                document.getElementById('form-suspender').submit();
            });
        }

        // ── Reactivar ────────────────────────────────────────────────────────────
        function confirmarReactivar(id, nombre) {
            Swal.fire({
                ...swalBase,
                title: '¿Reactivar restaurante?',
                html: `El restaurante <strong>${nombre}</strong> volverá a estar operativo.`,
                icon: 'question',
                iconColor: '#7C3AED',
                confirmButtonText: 'Sí, reactivar',
                cancelButtonText: 'Cancelar',
            }).then(result => {
                if (!result.isConfirmed) return;
                document.getElementById('form-reactivar').action =
                    `{{ url('superadmin/restaurantes') }}/${id}/reactivar`;
                document.getElementById('form-reactivar').submit();
            });
        }

        // ── Eliminar ──────────────────────────────────────────────────────────
        function confirmarEliminar(id, nombre) {
            Swal.fire({
                ...swalBase,
                title: '¿Eliminar restaurante?',
                html: `Esta acción es <strong>permanente</strong>.<br>Se eliminarán todos los datos de <strong>${nombre}</strong>.`,
                icon: 'error',
                iconColor: '#EF4444',
                confirmButtonText: 'Eliminar',
                cancelButtonText: 'Cancelar',
                focusCancel: true,
            }).then(result => {
                if (!result.isConfirmed) return;
                document.getElementById('form-eliminar').action =
                    `{{ url('superadmin/restaurantes') }}/${id}`;
                document.getElementById('form-eliminar').submit();
            });
        }

        // Si el navegador restaura la página desde el bfcache (botón atrás/adelante),
        // el DOM vuelve exactamente como quedó antes (modal abierto, triángulos, autofill).
        // Forzamos un reset total para que siempre arranque limpio.
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                cerrarModal();
                document.getElementById('form-registrar').reset();
            }
        });

        // Modal registrar
        function abrirModal() {
            limpiarErrores();
            document.getElementById('form-registrar').reset();
            // Restaurar valores por defecto de series
            document.getElementById('r_boleta').value = 'B001';
            document.getElementById('r_factura').value = 'F001';
            document.getElementById('modal-registrar').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function cerrarModal() {
            document.getElementById('modal-registrar').classList.add('hidden');
            document.body.style.overflow = '';
            limpiarErrores();
        }

        document.getElementById('modal-registrar').addEventListener('click', function(e) {
            if (e.target === this) cerrarModal();
        });

        // ── Filtro en tiempo real del RUC (solo números, inicio 10 o 20) ─────────
        document.getElementById('r_ruc').addEventListener('input', function() {
            let v = this.value.replace(/\D/g, '').slice(0, 11);
            if (v.length >= 1 && v[0] !== '1' && v[0] !== '2') {
                v = v.slice(1);
            }
            if (v.length >= 2 && !['10', '20'].includes(v.slice(0, 2))) {
                v = v.slice(0, 1);
            }
            this.value = v;
        });

        // ── Contraseña segura ────────────────────────────────────────────────────
        function reglasPassword(pass) {
            return {
                length: pass.length >= 8,
                upper: /[A-Z]/.test(pass),
                lower: /[a-z]/.test(pass),
                number: /[0-9]/.test(pass),
                special: /[^A-Za-z0-9]/.test(pass),
            };
        }

        function passwordEsSegura(pass) {
            return Object.values(reglasPassword(pass)).every(Boolean);
        }

        document.getElementById('r_prop_pass').addEventListener('input', function() {
            const reglas = reglasPassword(this.value);
            Object.keys(reglas).forEach(key => {
                const hint = document.querySelector(`.pass-hint[data-rule="${key}"]`);
                if (!hint) return;
                const icon = hint.querySelector('i');
                if (reglas[key]) {
                    hint.classList.add('valid');
                    icon.classList.replace('fa-circle-xmark', 'fa-circle-check');
                } else {
                    hint.classList.remove('valid');
                    icon.classList.replace('fa-circle-check', 'fa-circle-xmark');
                }
            });
        });

        function togglePassword() {
            const input = document.getElementById('r_prop_pass');
            const icon = document.getElementById('pass-toggle-icon');
            const esOculta = input.type === 'password';
            input.type = esOculta ? 'text' : 'password';
            icon.classList.toggle('fa-eye', !esOculta);
            icon.classList.toggle('fa-eye-slash', esOculta);
        }

        // ── Validación del formulario ─────────────────────────────────────────────
        function limpiarErrores() {
            document.querySelectorAll('.sa-field-error').forEach(el => {
                el.textContent = '';
                el.classList.add('hidden');
            });
            document.querySelectorAll('#form-registrar .sa-form-input').forEach(el => {
                el.classList.remove('sa-input-error');
            });
            document.querySelectorAll('.pass-hint').forEach(hint => {
                hint.classList.remove('valid');
                const icon = hint.querySelector('i');
                icon.classList.replace('fa-circle-check', 'fa-circle-xmark');
            });
        }

        function mostrarError(inputId, errorId, mensaje) {
            const input = document.getElementById(inputId);
            const error = document.getElementById(errorId);
            input.classList.add('sa-input-error');
            error.textContent = mensaje;
            error.classList.remove('hidden');
        }

        function validarFormulario() {
            limpiarErrores();
            let valido = true;

            const nombre = document.getElementById('r_nombre').value.trim();
            const ruc = document.getElementById('r_ruc').value.trim();
            const direccion = document.getElementById('r_direccion').value.trim();
            const boleta = document.getElementById('r_boleta').value.trim();
            const factura = document.getElementById('r_factura').value.trim();
            const propNom = document.getElementById('r_prop_nombre').value.trim();
            const propEmail = document.getElementById('r_prop_email').value.trim();
            const propPass = document.getElementById('r_prop_pass').value;

            if (!nombre) {
                mostrarError('r_nombre', 'err_nombre', '⚠ El nombre del restaurante es obligatorio.');
                valido = false;
            }

            if (!ruc) {
                mostrarError('r_ruc', 'err_ruc', '⚠ El RUC es obligatorio.');
                valido = false;
            } else if (!/^(10|20)\d{9}$/.test(ruc)) {
                mostrarError('r_ruc', 'err_ruc', '⚠ El RUC debe tener 11 dígitos y empezar con 10 o 20.');
                valido = false;
            }

            if (!direccion) {
                mostrarError('r_direccion', 'err_direccion', '⚠ La dirección fiscal es obligatoria.');
                valido = false;
            }

            if (!boleta) {
                mostrarError('r_boleta', 'err_boleta', '⚠ Ingresa la serie de boleta.');
                valido = false;
            } else if (!/^B\d{3}$/i.test(boleta)) {
                mostrarError('r_boleta', 'err_boleta', '⚠ Formato válido: B001');
                valido = false;
            }

            if (!factura) {
                mostrarError('r_factura', 'err_factura', '⚠ Ingresa la serie de factura.');
                valido = false;
            } else if (!/^F\d{3}$/i.test(factura)) {
                mostrarError('r_factura', 'err_factura', '⚠ Formato válido: F001');
                valido = false;
            }

            if (!propNom) {
                mostrarError('r_prop_nombre', 'err_prop_nombre', '⚠ El nombre del propietario es obligatorio.');
                valido = false;
            }

            if (!propEmail) {
                mostrarError('r_prop_email', 'err_prop_email', '⚠ El correo electrónico es obligatorio.');
                valido = false;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(propEmail)) {
                mostrarError('r_prop_email', 'err_prop_email', '⚠ Ingresa un correo electrónico válido.');
                valido = false;
            }

            if (!propPass) {
                mostrarError('r_prop_pass', 'err_prop_pass', '⚠ La contraseña es obligatoria.');
                valido = false;
            } else if (!passwordEsSegura(propPass)) {
                mostrarError('r_prop_pass', 'err_prop_pass', '⚠ La contraseña no cumple los requisitos de seguridad.');
                valido = false;
            }

            return valido;
        }

        function guardarRestaurante() {
            if (!validarFormulario()) return;

            const nombre = document.getElementById('r_nombre').value.trim();
            const ruc = document.getElementById('r_ruc').value.trim();
            const direccion = document.getElementById('r_direccion').value.trim();
            const boleta = document.getElementById('r_boleta').value.trim().toUpperCase();
            const factura = document.getElementById('r_factura').value.trim().toUpperCase();
            const propNombre = document.getElementById('r_prop_nombre').value.trim();
            const propEmail = document.getElementById('r_prop_email').value.trim();

            Swal.fire({
                ...swalBase,
                title: '¿Registrar restaurante?',
                html: `
                    <div class="sa-resumen">
                        <div class="sa-resumen-row">
                            <span class="sa-resumen-label">Restaurante</span>
                            <span class="sa-resumen-val">${nombre}</span>
                        </div>
                        <div class="sa-resumen-row">
                            <span class="sa-resumen-label">RUC</span>
                            <span class="sa-resumen-val">${ruc}</span>
                        </div>
                        <div class="sa-resumen-row">
                            <span class="sa-resumen-label">Dirección</span>
                            <span class="sa-resumen-val">${direccion}</span>
                        </div>
                        <div class="sa-resumen-row">
                            <span class="sa-resumen-label">Boleta / Factura</span>
                            <span class="sa-resumen-val">${boleta} / ${factura}</span>
                        </div>
                        <div class="sa-resumen-divider"></div>
                        <div class="sa-resumen-row">
                            <span class="sa-resumen-label">Propietario</span>
                            <span class="sa-resumen-val">${propNombre}</span>
                        </div>
                        <div class="sa-resumen-row">
                            <span class="sa-resumen-label">Correo</span>
                            <span class="sa-resumen-val">${propEmail}</span>
                        </div>
                    </div>
                    <p class="sa-resumen-aviso">⚠ Verifica tus datos antes de continuar</p>
                `,
                icon: 'question',
                iconColor: '#7C3AED',
                showCancelButton: true,
                confirmButtonText: 'Sí, registrar',
                cancelButtonText: 'Cancelar',
            }).then(result => {
                if (result.isConfirmed) {
                    document.getElementById('form-registrar').submit();
                }
            });
        }

        // Limpiar error de un campo al empezar a escribir
        document.querySelectorAll('#form-registrar .sa-form-input').forEach(input => {
            input.addEventListener('input', () => {
                input.classList.remove('sa-input-error');
                const errId = 'err_' + input.id.replace('r_', '').replace('prop_', 'prop_');
                const err = document.getElementById(errId);
                if (err) {
                    err.textContent = '';
                    err.classList.add('hidden');
                }
            });
        });

        // ── Filtro de tabla ───────────────────────────────────────────────────────
        function filtrarTabla() {
            const q = document.getElementById('filtro-tabla').value.toLowerCase();
            const estado = document.getElementById('filtro-estado').value.toLowerCase();

            document.querySelectorAll('#tabla-restaurantes tbody tr[data-nombre]').forEach(tr => {
                const matchNombre = tr.dataset.nombre.includes(q) || tr.dataset.ruc.includes(q);
                const matchEstado = estado === '' || tr.dataset.estado === estado;
                tr.style.display = (matchNombre && matchEstado) ? '' : 'none';
            });
        }

        // ── Filtro de estado: pinta el ícono/borde según selección ───────────────
        document.getElementById('filtro-estado').addEventListener('change', function() {
            const wrap = document.getElementById('estado-wrap');

            wrap.classList.remove('activo', 'suspendido');
            if (this.value === 'activo') {
                wrap.classList.add('activo');
            } else if (this.value === 'suspendido') {
                wrap.classList.add('suspendido');
            }
        });

        let rucTimeout;
        document.getElementById('r_ruc').addEventListener('input', function() {
            let v = this.value.replace(/\D/g, '').slice(0, 11);
            if (v.length >= 1 && v[0] !== '1' && v[0] !== '2') v = v.slice(1);
            if (v.length >= 2 && !['10', '20'].includes(v.slice(0, 2))) v = v.slice(0, 1);
            this.value = v;

            const icon = document.getElementById('ruc-status-icon');
            icon.className = 'ruc-status-icon';

            clearTimeout(rucTimeout);
            if (v.length === 11) {
                rucTimeout = setTimeout(() => consultarRuc(v), 400);
            }
        });
        async function consultarRuc(ruc) {
            const icon = document.getElementById('ruc-status-icon');
            icon.className = 'ruc-status-icon cargando';

            try {
                const res = await fetch(`{{ url('superadmin/consultar-ruc') }}/${ruc}`);
                if (!res.ok) throw new Error();
                const data = await res.json();

                if (data.razon_social) {
                    document.getElementById('r_nombre').value = data.razon_social;
                }
                if (data.direccion) {
                    document.getElementById('r_direccion').value = data.direccion;
                }
                icon.className = 'ruc-status-icon ok';
            } catch (e) {
                icon.className = 'ruc-status-icon error';
            }
        }
    </script>

@endsection
