@extends('layouts.app')

@section('title', 'Editar insumo — MIKHUNAWASI')
@section('page-title', 'Editar insumo')

@section('content')

<div class="page-toolbar">
    <a href="{{ route('insumos.index') }}" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Volver
    </a>
</div>

<div class="form-card">
    <form method="POST" action="{{ route('insumos.update', $insumo->id) }}" id="form-editar-insumo">
        @csrf @method('PUT')

        <div class="form-grid">

            <div class="form-group sm:col-span-2">
                <label class="form-label">Nombre del insumo *</label>
                <input type="text" id="i_nombre" name="nombre"
                    value="{{ old('nombre', $insumo->nombre) }}"
                    class="form-input @error('nombre') input-error @enderror">
                <p class="error-msg hidden" id="err_nombre">
                    @error('nombre'){{ $message }}@enderror
                </p>
            </div>

            <div class="form-group">
                <label class="form-label">Unidad de medida *</label>
                <select id="i_unidad" name="unidad_medida" class="form-input @error('unidad_medida') input-error @enderror">
                    @foreach(['kg','g','L','ml','unidad','caja','bolsa','botella','lata','docena'] as $u)
                        <option value="{{ $u }}" {{ old('unidad_medida', $insumo->unidad_medida) === $u ? 'selected' : '' }}>{{ $u }}</option>
                    @endforeach
                </select>
                <p class="error-msg hidden" id="err_unidad">
                    @error('unidad_medida'){{ $message }}@enderror
                </p>
            </div>

            <div class="form-group">
                <label class="form-label">Categoría <span class="text-gray-400 font-normal">(opcional)</span></label>
                <input type="text" name="categoria"
                    value="{{ old('categoria', $insumo->categoria) }}"
                    class="form-input" placeholder="Ej: Lácteos, Carnes, Verduras...">
            </div>

            <div class="form-group">
                <label class="form-label">Stock actual *</label>
                <input type="number" id="i_stock_actual" name="stock_actual"
                    value="{{ old('stock_actual', $insumo->stock_actual) }}"
                    class="form-input @error('stock_actual') input-error @enderror"
                    step="0.01" min="0"
                    oninput="this.value = this.value.replace(/^0+(?=\d)/, '')">
                <p class="error-msg hidden" id="err_stock_actual">
                    @error('stock_actual'){{ $message }}@enderror
                </p>
            </div>

            <div class="form-group">
                <label class="form-label">Stock mínimo *</label>
                <input type="number" id="i_stock_minimo" name="stock_minimo"
                    value="{{ old('stock_minimo', $insumo->stock_minimo) }}"
                    class="form-input @error('stock_minimo') input-error @enderror"
                    step="0.01" min="0"
                    oninput="this.value = this.value.replace(/^0+(?=\d)/, '')">
                <p class="error-msg hidden" id="err_stock_minimo">
                    @error('stock_minimo'){{ $message }}@enderror
                </p>
            </div>

            <div class="form-group">
                <label class="form-label">Estado</label>
                <div class="toggle-wrapper">
                    <input type="checkbox" name="activo" id="activo" value="1"
                        class="toggle-input" {{ old('activo', $insumo->activo) ? 'checked' : '' }}>
                    <label for="activo" class="toggle-label">Insumo activo</label>
                </div>
            </div>

        </div>

        <div class="form-actions">
            <a href="{{ route('insumos.index') }}" class="btn-cancel">Cancelar</a>
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
        box-shadow: 0 0 0 3px rgba(239,68,68,0.12) !important;
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
        document.querySelectorAll('.form-input').forEach(el => el.classList.remove('input-error'));
    }

    document.querySelectorAll('.form-input').forEach(input => {
        input.addEventListener('input',  () => input.classList.remove('input-error'));
        input.addEventListener('change', () => input.classList.remove('input-error'));
    });

    document.getElementById('form-editar-insumo').addEventListener('submit', function(e) {
        limpiarErrores();
        let valido = true;

        const nombre      = document.getElementById('i_nombre').value.trim();
        const unidad      = document.getElementById('i_unidad').value;
        const stockActual = document.getElementById('i_stock_actual').value;
        const stockMinimo = document.getElementById('i_stock_minimo').value;

        if (!nombre) {
            mostrarError('i_nombre', 'err_nombre', 'El nombre del insumo es obligatorio.');
            valido = false;
        }

        if (!unidad) {
            mostrarError('i_unidad', 'err_unidad', 'Selecciona una unidad de medida.');
            valido = false;
        }

        if (stockActual === '' || parseFloat(stockActual) < 0) {
            mostrarError('i_stock_actual', 'err_stock_actual', 'El stock actual no puede ser negativo.');
            valido = false;
        }

        if (stockMinimo === '' || parseFloat(stockMinimo) < 0) {
            mostrarError('i_stock_minimo', 'err_stock_minimo', 'El stock mínimo no puede ser negativo.');
            valido = false;
        }

        if (!valido) e.preventDefault();
    });
</script>

@endsection