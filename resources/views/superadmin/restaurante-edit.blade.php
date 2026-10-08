@extends('layouts.superadmin')

@section('title', 'Editar — ' . $restaurante->nombre)

@section('content')

    <div class="sa-page-header">
        <div>
            <p class="sa-page-sub">EDITAR RESTAURANTE</p>
            <h2 class="sa-page-title">{{ $restaurante->nombre }}</h2>
        </div>
        <a href="{{ route('superadmin.dashboard') }}" class="sa-btn-cancelar">← Volver</a>
    </div>

    <div class="sa-card p-8 max-w-2xl">
        <form method="POST" action="{{ route('superadmin.restaurantes.update', $restaurante) }}" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="sa-form-row-2">
                <div class="sa-form-group">
                    <label class="sa-form-label">Nombre del Restaurante *</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $restaurante->nombre) }}"
                        class="sa-form-input @error('nombre') border-red-400 @enderror" required>
                    @error('nombre')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="sa-form-group">
                    <label class="sa-form-label">RUC Comercial *</label>
                    <input type="text" name="ruc" value="{{ old('ruc', $restaurante->ruc) }}"
                        class="sa-form-input @error('ruc') border-red-400 @enderror" maxlength="11" required>
                    @error('ruc')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="sa-form-group mb-4">
                <label class="sa-form-label">Dirección Fiscal *</label>
                <input type="text" name="direccion" value="{{ old('direccion', $restaurante->direccion) }}"
                    class="sa-form-input @error('direccion') border-red-400 @enderror" required>
                @error('direccion')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="sa-form-group mb-4">
                <label class="sa-form-label">
                    Ubicación en Google Maps
                    <span class="text-gray-400 font-normal">(link de inserción/embed)</span>
                </label>
                <textarea name="mapa_embed_url" rows="3" class="sa-form-input @error('mapa_embed_url') border-red-400 @enderror"
                    placeholder="https://www.google.com/maps/embed?pb=...">{{ old('mapa_embed_url', $restaurante->mapa_embed_url) }}</textarea>
                <p class="text-gray-400 text-xs mt-1">
                    En Google Maps: busca el local → Compartir → Insertar un mapa → copia la URL que está dentro de <code>src="..."</code> y pégala aquí.
                </p>
                @error('mapa_embed_url')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="sa-form-group mb-6">
                <label class="sa-form-label">Logo del Restaurante</label>
                @if ($restaurante->logo_url)
                    <div class="flex items-center gap-3 mb-2">
                        <img src="{{ $restaurante->logo_url }}" alt="logo"
                            class="w-12 h-12 rounded-lg object-cover border border-gray-200">
                        <span class="text-xs text-gray-400">Logo actual</span>
                    </div>
                @endif
                <input type="file" name="logo" accept="image/*" class="sa-form-input">
            </div>

            @php
                $boleta = $restaurante->seriesComprobante->where('tipo', 'boleta')->first();
                $factura = $restaurante->seriesComprobante->where('tipo', 'factura')->first();
            @endphp
            <div class="sa-form-row-2 mb-6">
                <div class="sa-form-group">
                    <label class="sa-form-label">Boleta Serie</label>
                    <input type="text" name="boleta_serie" value="{{ old('boleta_serie', $boleta?->serie) }}"
                        class="sa-form-input" maxlength="4">
                </div>
                <div class="sa-form-group">
                    <label class="sa-form-label">Factura Serie</label>
                    <input type="text" name="factura_serie" value="{{ old('factura_serie', $factura?->serie) }}"
                        class="sa-form-input" maxlength="4">
                </div>
            </div>

            @php
                $propietario = $restaurante->usuarios->first(fn($u) => $u->rol?->nombre === 'propietario');
            @endphp
            <div class="border-t border-gray-200 pt-6 mb-6">
                <p class="sa-form-section-title mb-4">👤 Datos del propietario</p>
                @if ($propietario)
                    <div class="sa-form-row-2 mb-4">
                        <div class="sa-form-group">
                            <label class="sa-form-label">Nombre completo</label>
                            <input type="text" name="propietario_nombre"
                                value="{{ old('propietario_nombre', $propietario->nombre) }}" class="sa-form-input">
                        </div>
                        <div class="sa-form-group">
                            <label class="sa-form-label">Correo electrónico</label>
                            <input type="email" name="propietario_email"
                                value="{{ old('propietario_email', $propietario->email) }}" class="sa-form-input">
                        </div>
                    </div>
                    <div class="sa-form-group">
                        <label class="sa-form-label">
                            Nueva contraseña
                            <span class="text-gray-400 font-normal">(dejar en blanco para no cambiar)</span>
                        </label>
                        <input type="password" name="propietario_password" class="sa-form-input"
                            placeholder="Mínimo 6 caracteres">
                    </div>
                @else
                    <p class="text-gray-400 text-sm">No hay propietario registrado para este restaurante.</p>
                @endif
            </div>

            <div class="flex gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('superadmin.dashboard') }}" class="sa-btn-cancelar">Cancelar</a>
                <button type="submit" class="sa-btn-confirmar">Guardar cambios</button>
            </div>

        </form>
    </div>

@endsection