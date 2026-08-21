@extends('layouts.app')

@section('title', 'Editar usuario — MIKHUNAWASI')
@section('page-title', 'Editar usuario')

@section('content')

    <div class="page-toolbar">
        <a href="{{ route('usuarios.index') }}" class="btn-back">← Volver</a>
    </div>

    <div class="form-card">
        <form method="POST" action="{{ route('usuarios.update', $usuario) }}">
            @csrf @method('PUT')

            <div class="form-grid">

                <div class="form-group">
                    <label class="form-label">Nombre completo</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $usuario->nombre) }}"
                        class="form-input @error('nombre') input-error @enderror">
                    @error('nombre')
                        <p class="error-msg">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Correo electrónico</label>
                    <input type="email" name="email" value="{{ old('email', $usuario->email) }}"
                        class="form-input @error('email') input-error @enderror">
                    @error('email')
                        <p class="error-msg">{{ $message }}</p>
                    @enderror
                </div>

                
                <div class="form-group">
                    <label class="form-label">Nueva contraseña <span class="text-gray-400 font-normal">(dejar en blanco para
                            no cambiar)</span></label>
                    <input type="password" name="password" class="form-input @error('password') input-error @enderror"
                        placeholder="Nueva contraseña">
                    <p class="text-xs text-gray-400 mt-1">Mínimo 8 caracteres, con mayúsculas, minúsculas y números.</p>
                    @error('password')
                        <p class="error-msg">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Confirmar nueva contraseña</label>
                    <input type="password" name="password_confirmation" class="form-input"
                        placeholder="Repite la nueva contraseña">
                </div>

                <div class="form-group">
                    <label class="form-label">Rol</label>
                    <select name="rol_id" class="form-input @error('rol_id') input-error @enderror">
                        @foreach ($roles as $rol)
                            <option value="{{ $rol->id }}"
                                {{ old('rol_id', $usuario->rol_id) == $rol->id ? 'selected' : '' }}>
                                {{ ucfirst($rol->nombre) }}
                            </option>
                        @endforeach
                    </select>
                    @error('rol_id')
                        <p class="error-msg">{{ $message }}</p>
                    @enderror
                </div>

                @if ($rolActual === 'superadmin')
                    <div class="form-group">
                        <label class="form-label">Restaurante</label>
                        <select name="restaurante_id" class="form-input">
                            <option value="">Sin restaurante</option>
                            @foreach ($restaurantes as $r)
                                <option value="{{ $r->id }}"
                                    {{ old('restaurante_id', $usuario->restaurante_id) == $r->id ? 'selected' : '' }}>
                                    {{ $r->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <div class="toggle-wrapper">
                        <input type="checkbox" name="activo" id="activo" value="1" class="toggle-input"
                            {{ old('activo', $usuario->activo) ? 'checked' : '' }}>
                        <label for="activo" class="toggle-label">Usuario activo</label>
                    </div>
                </div>

            </div>

            <div class="form-actions">
                <a href="{{ route('usuarios.index') }}" class="btn-cancel">Cancelar</a>
                <button type="submit" class="btn-submit">Guardar cambios</button>
            </div>

        </form>
    </div>

@endsection
