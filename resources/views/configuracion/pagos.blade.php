@extends('layouts.app')

@section('title', 'Configuración de Pagos — MIKHUNAWASI')
@section('page-title', 'Configuración de Pagos')

@section('content')

<div class="ped-header">
    <div>
        <h2 class="ped-title">Configuración de Pagos</h2>
        <p class="ped-sub">Conecta tu cuenta de Culqi para aceptar pagos con tarjeta en Caja.</p>
    </div>
</div>

<div style="max-width: 600px;">

    @if($tieneCulqiConfigurado)
        <div class="caja-apertura-banner green mb-4">
            <div>
                <p class="font-semibold text-gray-700">✅ Culqi configurado</p>
                <p class="text-sm text-gray-500">Tu restaurante ya puede aceptar pagos con tarjeta.</p>
            </div>
            <form method="POST" action="{{ route('configuracion.pagos.eliminar') }}"
                  onsubmit="return confirm('¿Desactivar el cobro con tarjeta para este restaurante?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-delete px-4 py-2 rounded-lg">Desconectar</button>
            </form>
        </div>
    @else
        <div class="caja-apertura-banner mb-4">
            <div>
                <p class="font-semibold text-gray-700">⚠️ Culqi no configurado</p>
                <p class="text-sm text-gray-500">El cobro con tarjeta en Caja no estará disponible hasta que conectes tu cuenta.</p>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('configuracion.pagos.guardar') }}" class="mesas-modal-body" style="padding:0;">
        @csrf

        <div class="mb-4">
            <label class="mesas-form-label">Llave pública de Culqi</label>
            <input type="text" name="culqi_public_key" class="mesas-form-input"
                   placeholder="pk_test_... o pk_live_..."
                   value="{{ old('culqi_public_key', $restaurante->culqi_public_key) }}" required>
            @error('culqi_public_key')
                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label class="mesas-form-label">Llave secreta de Culqi</label>
            <input type="password" name="culqi_secret_key" class="mesas-form-input"
                   placeholder="sk_test_... o sk_live_..." required>
            <p class="text-sm text-gray-500 mt-1">
                Por seguridad, no mostramos la llave guardada. Escribe una nueva para reemplazarla.
            </p>
            @error('culqi_secret_key')
                <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6" style="background:#f9f0f8; border-radius:8px; padding:12px 16px;">
            <p class="text-sm text-gray-600">
                <i class="fa-solid fa-circle-info"></i>
                ¿No tienes cuenta de Culqi? Regístrate gratis en
                <a href="https://culqi.com" target="_blank" style="color:#7e22ce; font-weight:600;">culqi.com</a>
                y obtén tus llaves en CulqiPanel → Desarrollo → API Keys.
            </p>
        </div>

        <button type="submit" class="mesas-btn-guardar">
            <i class="fa-solid fa-floppy-disk"></i> Guardar llaves
        </button>
    </form>

</div>

@endsection