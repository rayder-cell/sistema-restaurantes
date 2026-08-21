@extends('layouts.app')

@section('title', 'Nuevo usuario — MIKHUNAWASI')
@section('page-title', 'Nuevo usuario')

@section('content')

    <div class="page-toolbar">
        <a href="{{ route('usuarios.index') }}" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Volver</a>
    </div>

    <div class="form-card">
        <form method="POST" action="{{ route('usuarios.store') }}" id="form-usuario">
            @csrf

            <div class="form-grid">

                <div class="form-group">
                    <label class="form-label">Nombre completo</label>
                    <input type="text" id="u_nombre" name="nombre" value="{{ old('nombre') }}"
                        class="form-input @error('nombre') input-error @enderror" placeholder="Ej: Juan Pérez">
                    <p class="error-msg hidden" id="err_nombre">
                        @error('nombre')
                            {{ $message }}
                        @enderror
                    </p>
                </div>

                <div class="form-group">
                    <label class="form-label">Correo electrónico</label>
                    <input type="text" id="u_email" name="email" value="{{ old('email') }}"
                        class="form-input @error('email') input-error @enderror" placeholder="correo@ejemplo.com"
                        autocomplete="email" inputmode="email">
                    <p class="error-msg hidden" id="err_email">
                        @error('email')
                            {{ $message }}
                        @enderror
                    </p>
                </div>

                <div class="form-group">
                    <label class="form-label">Contraseña</label>
                    <div class="pass-wrap">
                        <input type="password" id="u_password" name="password"
                            class="form-input @error('password') input-error @enderror" autocomplete="new-password"
                            placeholder="Mínimo 8 caracteres">
                        <button type="button" class="pass-toggle"
                            onclick="togglePassword('u_password', 'pass-toggle-icon-1')" tabindex="-1">
                            <i class="fa-solid fa-eye" id="pass-toggle-icon-1"></i>
                        </button>
                    </div>
                    <p class="error-msg hidden" id="err_password">
                        @error('password')
                            {{ $message }}
                        @enderror
                    </p>
                    <div class="pass-hints" id="pass-hints">
                        <span class="pass-hint" data-rule="length"><i class="fa-solid fa-circle-xmark"></i> Mínimo 8
                            caracteres</span>
                        <span class="pass-hint" data-rule="upper"><i class="fa-solid fa-circle-xmark"></i> Una
                            mayúscula</span>
                        <span class="pass-hint" data-rule="lower"><i class="fa-solid fa-circle-xmark"></i> Una
                            minúscula</span>
                        <span class="pass-hint" data-rule="number"><i class="fa-solid fa-circle-xmark"></i> Un número</span>
                        <span class="pass-hint" data-rule="special"><i class="fa-solid fa-circle-xmark"></i> Un carácter
                            especial</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirmar contraseña</label>
                    <div class="pass-wrap">
                        <input type="password" id="u_password_confirmation" name="password_confirmation" class="form-input"
                            autocomplete="new-password" placeholder="Repite la contraseña">
                        <button type="button" class="pass-toggle"
                            onclick="togglePassword('u_password_confirmation', 'pass-toggle-icon-2')" tabindex="-1">
                            <i class="fa-solid fa-eye" id="pass-toggle-icon-2"></i>
                        </button>
                    </div>
                    <p class="error-msg hidden" id="err_password_confirmation"></p>
                </div>

                <div class="form-group">
                    <label class="form-label">Rol</label>
                    <select name="rol_id" id="u_rol" class="form-input @error('rol_id') input-error @enderror">
                        <option value="">Seleccionar rol...</option>
                        @foreach ($roles as $rol)
                            <option value="{{ $rol->id }}" {{ old('rol_id') == $rol->id ? 'selected' : '' }}>
                                {{ ucfirst($rol->nombre) }}
                            </option>
                        @endforeach
                    </select>
                    <p class="error-msg hidden" id="err_rol_id">
                        @error('rol_id')
                            {{ $message }}
                        @enderror
                    </p>
                </div>

                @if ($rolActual === 'superadmin')
                    <div class="form-group">
                        <label class="form-label">Restaurante</label>
                        <select name="restaurante_id" class="form-input">
                            <option value="">Sin restaurante (superadmin)</option>
                            @foreach ($restaurantes as $r)
                                <option value="{{ $r->id }}"
                                    {{ old('restaurante_id') == $r->id ? 'selected' : '' }}>
                                    {{ $r->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

            </div>

            <div class="form-actions">
                <a href="{{ route('usuarios.index') }}" class="btn-cancel">Cancelar</a>
                <button type="submit" class="btn-submit">Crear usuario</button>
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

        /* Campo de contraseña con botón de mostrar/ocultar */
        .pass-wrap {
            position: relative;
        }

        .pass-wrap .form-input {
            padding-right: 2.5rem;
        }

        .pass-toggle {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            cursor: pointer;
            background: none;
            border: none;
        }

        .pass-toggle:hover {
            color: #4b5563;
        }

        /* Checklist de requisitos de contraseña */
        .pass-hints {
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
    </style>

    <script>
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

        document.getElementById('u_password').addEventListener('input', function() {
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

        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            const esOculta = input.type === 'password';
            input.type = esOculta ? 'text' : 'password';
            icon.classList.toggle('fa-eye', !esOculta);
            icon.classList.toggle('fa-eye-slash', esOculta);
        }

        // ── Validación al enviar el formulario ───────────────────────────────────
        function mostrarError(inputId, errorId, mensaje) {
            const input = document.getElementById(inputId);
            const error = document.getElementById(errorId);
            input.classList.add('input-error');
            error.textContent = mensaje;
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

        document.getElementById('form-usuario').addEventListener('submit', function(e) {
            limpiarErrores();
            let valido = true;

            const nombre = document.getElementById('u_nombre').value.trim();
            const email = document.getElementById('u_email').value.trim();
            const password = document.getElementById('u_password').value;
            const passwordConfirm = document.getElementById('u_password_confirmation').value;
            const rol = document.getElementById('u_rol').value;

            if (!nombre) {
                mostrarError('u_nombre', 'err_nombre', '⚠︎ El nombre es obligatorio.');
                valido = false;
            }

            if (!email) {
                mostrarError('u_email', 'err_email', '⚠︎ El correo es obligatorio.');
                valido = false;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                mostrarError('u_email', 'err_email', '⚠︎ Ingresa un correo electrónico válido.');
                valido = false;
            }

            if (!password) {
                mostrarError('u_password', 'err_password', '⚠︎ La contraseña es obligatoria.');
                valido = false;
            } else if (!passwordEsSegura(password)) {
                mostrarError('u_password', 'err_password',
                    '⚠︎ La contraseña no cumple los requisitos de seguridad.');
                valido = false;
            }

            if (!passwordConfirm) {
                mostrarError('u_password_confirmation', 'err_password_confirmation', '⚠︎ Confirma la contraseña.');
                valido = false;
            } else if (password !== passwordConfirm) {
                mostrarError('u_password_confirmation', 'err_password_confirmation',
                    '⚠︎ La contraseña no coincide.');
                valido = false;
            }

            if (!rol) {
                mostrarError('u_rol', 'err_rol_id', '⚠︎ Selecciona un rol.');
                valido = false;
            }

            if (!valido) {
                e.preventDefault();
            }
        });

        // Limpiar error al escribir
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('input', () => {
                input.classList.remove('input-error');
            });
        });
    </script>

@endsection
