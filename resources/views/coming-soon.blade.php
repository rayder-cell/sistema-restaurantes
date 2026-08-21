@extends('layouts.app')

@section('title', 'Próximamente')
@section('page-title', 'Módulo en desarrollo')

@section('content')
<div class="flex flex-col items-center justify-center h-96 text-center">
    <div class="text-6xl mb-4">🚧</div>
    <h2 class="text-2xl font-bold text-gray-700 mb-2">Módulo en desarrollo</h2>
    <p class="text-gray-500">Este módulo estará disponible próximamente.</p>
    <a href="{{ route('dashboard') }}" class="mt-6 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm">
        Volver al Dashboard
    </a>
</div>
@endsection
