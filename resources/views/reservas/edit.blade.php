@extends('layouts.app')

@section('title', 'Editar reserva — MIKHUNAWASI')
@section('page-title', 'Editar reserva')

@section('content')

    <div class="page-toolbar">
        <a href="{{ route('reservas.index') }}" class="btn-back">
            <i class="fa-solid fa-arrow-left"></i> Volver
        </a>
    </div>

    <div class="form-card">
        <form method="POST" action="{{ route('reservas.update', $reserva->id) }}" id="form-editar-reserva">
            @csrf @method('PUT')

            <p class="text-sm font-semibold text-gray-600 mb-4">
                <i class="fa-solid fa-user"></i> Datos del cliente
            </p>
            <div class="form-grid mb-2">

                <div class="form-group">
                    <label class="form-label">Nombre completo *</label>
                    <input type="text" id="r_nombre" name="cliente_nombre"
                        value="{{ old('cliente_nombre', $reserva->cliente_nombre) }}"
                        class="form-input @error('cliente_nombre') input-error @enderror" placeholder="Nombre del cliente"
                        maxlength="100">
                    <p class="error-msg hidden" id="err_nombre">
                        @error('cliente_nombre')
                            {{ $message }}
                        @enderror
                    </p>
                </div>

                <div class="form-group">
                    <label class="form-label">Teléfono *</label>
                    <input type="text" id="r_telefono" name="cliente_telefono"
                        value="{{ old('cliente_telefono', $reserva->cliente_telefono) }}"
                        class="form-input @error('cliente_telefono') input-error @enderror" placeholder="999 999 999"
                        maxlength="9" onkeydown="return bloquearTelefono(event, this)"
                        oninput="validarPrefixTelefono(this)">
                    @error('cliente_telefono')
                        {{ $message }}
                    @enderror
                    </p>
                </div>

                <div class="form-group sm:col-span-2">
                    <label class="form-label">Correo electrónico <span
                            class="text-gray-400 font-normal">(opcional)</span></label>
                    <input type="text" id="r_email" name="cliente_email"
                        value="{{ old('cliente_email', $reserva->cliente_email) }}"
                        class="form-input @error('cliente_email') input-error @enderror" placeholder="correo@ejemplo.com"
                        maxlength="100" autocomplete="email" inputmode="email">
                    <p class="error-msg hidden" id="err_email">
                        @error('cliente_email')
                            {{ $message }}
                        @enderror
                    </p>
                </div>

            </div>

            <p class="text-sm font-semibold text-gray-600 mb-4 mt-4 pt-4 border-t border-gray-100">
                <i class="fa-solid fa-calendar-days"></i> Detalles de la reserva
            </p>
            <div class="form-grid mb-2">

                <div class="form-group">
                    <label class="form-label">Fecha *</label>
                    <input type="date" id="r_fecha" name="fecha" value="{{ old('fecha', $reserva->fecha) }}"
                        class="form-input @error('fecha') input-error @enderror">
                    <p class="error-msg hidden" id="err_fecha">
                        @error('fecha')
                            {{ $message }}
                        @enderror
                    </p>
                </div>

                <div class="form-group">
                    <label class="form-label">Hora *</label>
                    <input type="time" id="r_hora" name="hora"
                        value="{{ old('hora', \Carbon\Carbon::parse($reserva->hora)->format('H:i')) }}"
                        class="form-input @error('hora') input-error @enderror">
                    <p class="error-msg hidden" id="err_hora">
                        @error('hora')
                            {{ $message }}
                        @enderror
                    </p>
                </div>

                <div class="form-group">
                    <label class="form-label">N° de personas *</label>
                    <input type="number" id="r_personas" name="num_personas"
                        value="{{ old('num_personas', $reserva->num_personas) }}"
                        class="form-input @error('num_personas') input-error @enderror" min="1" max="50"
                        oninput="this.value = this.value.replace(/^0+/, '') || 1"
                        onkeydown="return ['Backspace','Delete','ArrowLeft','ArrowRight','Tab'].includes(event.key) || /^[0-9]$/.test(event.key)">
                    <p class="error-msg hidden" id="err_personas">
                        @error('num_personas')
                            {{ $message }}
                        @enderror
                    </p>
                </div>

                <div class="form-group">
                    <label class="form-label">Mesa <span class="text-gray-400 font-normal">(opcional)</span></label>
                    <select id="r_mesa" name="mesa_id" class="form-input">
                        <option value="">Sin mesa asignada</option>
                        @foreach ($mesas as $mesa)
                            <option value="{{ $mesa->id }}"
                                {{ old('mesa_id', $reserva->mesa_id) == $mesa->id ? 'selected' : '' }}>
                                Mesa {{ $mesa->numero }} — Cap. {{ $mesa->capacidad }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Estado *</label>
                    <select id="r_estado" name="estado" class="form-input">
                        <option value="pendiente" {{ old('estado', $reserva->estado) === 'pendiente' ? 'selected' : '' }}>
                            Pendiente</option>
                        <option value="confirmada"
                            {{ old('estado', $reserva->estado) === 'confirmada' ? 'selected' : '' }}>Confirmada</option>
                        <option value="cancelada" {{ old('estado', $reserva->estado) === 'cancelada' ? 'selected' : '' }}>
                            Cancelada</option>
                        <option value="completada"
                            {{ old('estado', $reserva->estado) === 'completada' ? 'selected' : '' }}>Completada</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Adelanto (S/) <span
                            class="text-gray-400 font-normal">(opcional)</span></label>
                    <input type="number" id="r_precio" name="precio_base"
                        value="{{ old('precio_base', $reserva->precio_base) }}"
                        class="form-input @error('precio_base') input-error @enderror" step="0.01" min="0"
                        oninput="this.value = this.value.replace(/^0+(?=\d)/, '')">
                    <p class="error-msg hidden" id="err_precio">
                        @error('precio_base')
                            {{ $message }}
                        @enderror
                    </p>
                </div>

                <div class="form-group sm:col-span-2">
                    <label class="form-label">Observaciones <span
                            class="text-gray-400 font-normal">(opcional)</span></label>
                    <textarea name="observacion" rows="2" maxlength="500" class="form-input"
                        placeholder="Alergias, preferencias, ocasión especial...">{{ old('observacion', $reserva->observacion) }}</textarea>
                </div>

            </div>

            <div class="form-actions">
                <a href="{{ route('reservas.index') }}" class="btn-cancel">Cancelar</a>
                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar cambios
                </button>
            </div>
        </form>
    </div>

    <style>
        .error-msg {
            font-size: 0.75rem;
            color: #DC2626;
            margin-top: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .input-error {
            border-color: #EF4444 !important;
            background-color: #FFF8F8 !important;
            outline: none;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.12) !important;
        }

        .input-error:focus {
            border-color: #EF4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2) !important;
        }
    </style>

    <script>
        function mostrarError(inputId, errorId, mensaje) {
            const input = document.getElementById(inputId);
            const error = document.getElementById(errorId);
            if (input) input.classList.add('input-error');
            error.textContent = '⚠︎ ' + mensaje;
            error.classList.remove('hidden');
        }

        function limpiarErrores() {
            document.querySelectorAll('.error-msg').forEach(el => {
                el.textContent = '';
                el.classList.add('hidden');
            });
            document.querySelectorAll('.form-input').forEach(el => {
                el.classList.remove('input-error');
            });
        }

        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('input', () => input.classList.remove('input-error'));
            input.addEventListener('change', () => input.classList.remove('input-error'));
        });

        document.getElementById('form-editar-reserva').addEventListener('submit', function(e) {
            limpiarErrores();
            let valido = true;

            const nombre = document.getElementById('r_nombre').value.trim();
            const telefono = document.getElementById('r_telefono').value.trim();
            const email = document.getElementById('r_email').value.trim();
            const fecha = document.getElementById('r_fecha').value;
            const hora = document.getElementById('r_hora').value;
            const personas = document.getElementById('r_personas').value;
            const precio = document.getElementById('r_precio').value;

            if (!nombre) {
                mostrarError('r_nombre', 'err_nombre', 'El nombre del cliente es obligatorio.');
                valido = false;
            }

            if (telefono && !/^9\d{8}$/.test(telefono)) {
                mostrarError('r_telefono', 'err_telefono', 'El teléfono debe tener 9 dígitos y empezar con 9.');
                valido = false;
            } else if (!/^[0-9]{9}$/.test(telefono)) {
                mostrarError('r_telefono', 'err_telefono', 'El teléfono debe tener exactamente 9 dígitos.');
                valido = false;
            }

            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                mostrarError('r_email', 'err_email', 'Ingresa un correo electrónico válido.');
                valido = false;
            }

            if (!fecha) {
                mostrarError('r_fecha', 'err_fecha', 'La fecha es obligatoria.');
                valido = false;
            }

            if (!hora) {
                mostrarError('r_hora', 'err_hora', 'La hora es obligatoria.');
                valido = false;
            }

            if (!personas || parseInt(personas) < 1) {
                mostrarError('r_personas', 'err_personas', 'Ingresa al menos 1 persona.');
                valido = false;
            } else if (parseInt(personas) > 50) {
                mostrarError('r_personas', 'err_personas', 'El máximo permitido es 50 personas.');
                valido = false;
            }

            if (precio !== '' && parseFloat(precio) < 0) {
                mostrarError('r_precio', 'err_precio', 'El adelanto no puede ser negativo.');
                valido = false;
            }

            if (!valido) e.preventDefault();
        });

        function bloquearTelefono(event, input) {
            const teclaPermitida = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab'].includes(event.key);
            const esNumero = /^[0-9]$/.test(event.key);
            if (!teclaPermitida && !esNumero) return false;
            if (esNumero && input.value.length === 0 && event.key !== '9') return false;
            return true;
        }

        function validarPrefixTelefono(input) {
            if (input.value.length >= 1 && input.value[0] !== '9') input.value = '';
        }
    </script>

@endsection
