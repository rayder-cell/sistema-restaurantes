@extends('layouts.app')

@section('title', 'Nuevo proveedor — MIKHUNAWASI')
@section('page-title', 'Nuevo proveedor')

@section('content')

<div class="page-toolbar">
    <a href="{{ route('proveedores.index') }}" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Volver
    </a>
</div>

<div class="form-card">
    <form method="POST" action="{{ route('proveedores.store') }}" id="form-proveedor">
        @csrf

        <div class="form-grid">

            <div class="form-group sm:col-span-2">
                <label class="form-label">Nombre del proveedor *</label>
                <input type="text" id="p_nombre" name="nombre" value="{{ old('nombre') }}"
                    class="form-input @error('nombre') input-error @enderror"
                    placeholder="Razón social o nombre comercial">
                <p class="error-msg hidden" id="err_nombre">
                    @error('nombre'){{ $message }}@enderror
                </p>
            </div>

            <div class="form-group">
                <label class="form-label">RUC <span class="text-gray-400 font-normal">(opcional)</span></label>
                <input type="text" id="p_ruc" name="ruc" value="{{ old('ruc') }}"
                    class="form-input" placeholder="20123456789" maxlength="11"
                    onkeydown="return bloquearRuc(event, this)"
                    oninput="validarPrefixRuc(this)">
                <p class="error-msg hidden" id="err_ruc"></p>
            </div>

            <div class="form-group">
                <label class="form-label">Teléfono <span class="text-gray-400 font-normal">(opcional)</span></label>
                <input type="text" id="p_telefono" name="telefono" value="{{ old('telefono') }}"
                    class="form-input" placeholder="999 999 999" maxlength="9"
                    onkeydown="return bloquearTelefono(event, this)"
                    oninput="validarPrefixTelefono(this)">
                <p class="error-msg hidden" id="err_telefono"></p>
            </div>

            <div class="form-group">
                <label class="form-label">Email <span class="text-gray-400 font-normal">(opcional)</span></label>
                <input type="text" id="p_email" name="email" value="{{ old('email') }}"
                    class="form-input" placeholder="proveedor@ejemplo.com"
                    autocomplete="email" inputmode="email">
                <p class="error-msg hidden" id="err_email"></p>
            </div>

            <div class="form-group sm:col-span-2">
                <label class="form-label">Dirección <span class="text-gray-400 font-normal">(opcional)</span></label>
                <input type="text" name="direccion" value="{{ old('direccion') }}"
                    class="form-input" placeholder="Dirección del proveedor">
            </div>

        </div>

        <div class="form-actions">
            <a href="{{ route('proveedores.index') }}" class="btn-cancel">Cancelar</a>
            <button type="submit" class="btn-submit">
                <i class="fa-solid fa-floppy-disk"></i> Registrar proveedor
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
        box-shadow: 0 0 0 3px rgba(239,68,68,0.12) !important;
    }
</style>

<script>
    // ── RUC: solo 10 o 20 ────────────────────────────────────────────
    function bloquearRuc(event, input) {
        const teclaPermitida = ['Backspace','Delete','ArrowLeft','ArrowRight','Tab'].includes(event.key);
        const esNumero = /^[0-9]$/.test(event.key);
        if (!teclaPermitida && !esNumero) return false;
        // Primer dígito: solo 1 o 2
        if (esNumero && input.value.length === 0 && !['1','2'].includes(event.key)) return false;
        // Segundo dígito: solo 0
        if (esNumero && input.value.length === 1 && event.key !== '0') return false;
        return true;
    }

    function validarPrefixRuc(input) {
        const val = input.value;
        if (val.length >= 1 && !['1','2'].includes(val[0])) { input.value = ''; return; }
        if (val.length >= 2 && val[1] !== '0') input.value = val[0];
    }

    // ── Teléfono: solo empieza con 9 ─────────────────────────────────
    function bloquearTelefono(event, input) {
        const teclaPermitida = ['Backspace','Delete','ArrowLeft','ArrowRight','Tab'].includes(event.key);
        const esNumero = /^[0-9]$/.test(event.key);
        if (!teclaPermitida && !esNumero) return false;
        // Primer dígito: solo 9
        if (esNumero && input.value.length === 0 && event.key !== '9') return false;
        return true;
    }

    function validarPrefixTelefono(input) {
        if (input.value.length >= 1 && input.value[0] !== '9') input.value = '';
    }

    // ── Validación al enviar ──────────────────────────────────────────
    function mostrarError(inputId, errorId, mensaje) {
        const input = document.getElementById(inputId);
        const error = document.getElementById(errorId);
        if (input) input.classList.add('input-error');
        error.textContent = '⚠︎ ' + mensaje;
        error.classList.remove('hidden');
    }

    function limpiarErrores() {
        document.querySelectorAll('.error-msg').forEach(el => { el.textContent = ''; el.classList.add('hidden'); });
        document.querySelectorAll('.form-input').forEach(el => el.classList.remove('input-error'));
    }

    document.querySelectorAll('.form-input').forEach(input => {
        input.addEventListener('input',  () => input.classList.remove('input-error'));
        input.addEventListener('change', () => input.classList.remove('input-error'));
    });

    document.getElementById('form-proveedor').addEventListener('submit', function(e) {
        limpiarErrores();
        let valido = true;

        const nombre   = document.getElementById('p_nombre').value.trim();
        const ruc      = document.getElementById('p_ruc').value.trim();
        const telefono = document.getElementById('p_telefono').value.trim();
        const email    = document.getElementById('p_email').value.trim();

        if (!nombre) {
            mostrarError('p_nombre', 'err_nombre', 'El nombre del proveedor es obligatorio.');
            valido = false;
        }

        if (ruc && !/^(10|20)\d{9}$/.test(ruc)) {
            mostrarError('p_ruc', 'err_ruc', 'El RUC debe tener 11 dígitos y empezar con 10 o 20.');
            valido = false;
        }

        if (telefono && !/^9\d{8}$/.test(telefono)) {
            mostrarError('p_telefono', 'err_telefono', 'El teléfono debe tener 9 dígitos y empezar con 9.');
            valido = false;
        }

        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            mostrarError('p_email', 'err_email', 'Ingresa un correo electrónico válido.');
            valido = false;
        }

        if (!valido) e.preventDefault();
    });
</script>

@endsection