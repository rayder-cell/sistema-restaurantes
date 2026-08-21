<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva — {{ $restaurante->nombre }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center px-4 py-8">

<div class="w-full max-w-md">
    <div class="text-center mb-6">
        <div class="text-4xl mb-2">🍽</div>
        <h1 class="text-xl font-bold text-gray-800">{{ $restaurante->nombre }}</h1>
        <p class="text-gray-500 text-sm">Mesa {{ $mesa->numero }} — Reserva en línea</p>
    </div>

    <div class="bg-white rounded-2xl shadow-lg p-6">
        <h2 class="font-semibold text-gray-700 mb-4">📅 Solicitar reserva</h2>

        @if(session('success'))
        <div class="bg-green-50 border border-green-300 text-green-700 rounded-lg px-4 py-3 mb-4 text-sm">
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="bg-red-50 border border-red-300 text-red-600 rounded-lg px-4 py-3 mb-4 text-sm">
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('reserva.publica.store', $mesa->qr_token) }}">
            @csrf

            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-600 mb-1">Nombre *</label>
                <input type="text" name="cliente_nombre" value="{{ old('cliente_nombre') }}"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-purple-400"
                    required placeholder="Tu nombre completo">
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-600 mb-1">Teléfono *</label>
                <input type="text" name="cliente_telefono" value="{{ old('cliente_telefono') }}"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-purple-400"
                    required placeholder="999 999 999">
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-600 mb-1">Email <span class="text-gray-400">(opcional)</span></label>
                <input type="email" name="cliente_email" value="{{ old('cliente_email') }}"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-purple-400"
                    placeholder="correo@ejemplo.com">
            </div>
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Fecha *</label>
                    <input type="date" name="fecha" value="{{ old('fecha', now()->toDateString()) }}"
                        min="{{ now()->toDateString() }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-purple-400"
                        required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Hora *</label>
                    <input type="time" name="hora" value="{{ old('hora') }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-purple-400"
                        required>
                </div>
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-600 mb-1">N° de personas *</label>
                <input type="number" name="num_personas" value="{{ old('num_personas', 2) }}"
                    min="1" max="{{ $mesa->capacidad }}"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-purple-400"
                    required>
                <p class="text-xs text-gray-400 mt-1">Capacidad máxima: {{ $mesa->capacidad }} personas</p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-600 mb-1">Observaciones <span class="text-gray-400">(opcional)</span></label>
                <textarea name="observacion" rows="2"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-purple-400"
                    placeholder="Alergias, ocasión especial...">{{ old('observacion') }}</textarea>
            </div>

            <button type="submit"
                class="w-full bg-purple-700 hover:bg-purple-800 text-white font-semibold py-3 rounded-lg transition-colors text-sm">
                Solicitar reserva
            </button>
        </form>
    </div>
</div>

</body>
</html>
