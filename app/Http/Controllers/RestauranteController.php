<?php

namespace App\Http\Controllers;

use App\Models\Restaurante;
use App\Models\Producto;
use App\Models\Reserva;

class RestauranteController extends Controller
{
    public function buscar()
    {
        $totalRestaurantes = Restaurante::activo()->count();
        $totalReservas = Reserva::count();

        $restaurantes = Restaurante::activo()
            ->select('id', 'nombre', 'direccion', 'logo_url')
            ->get();

        $platos = Producto::with('restaurante:id,nombre')
            ->whereHas('restaurante', fn ($q) => $q->activo())
            ->disponible()
            ->select('id', 'nombre', 'precio', 'imagen_url', 'restaurante_id')
            ->limit(8)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'precio' => $p->precio,
                'imagen_url' => $p->imagen_url,
                'restaurante_nombre' => $p->restaurante->nombre ?? '',
            ]);

        return view('landing.index', compact('restaurantes', 'platos', 'totalRestaurantes', 'totalReservas'));
    }

    public function index()
    {
        $restaurantes = Restaurante::paginate(12);
        return view('restaurantes.index', compact('restaurantes'));
    }

    public function show(Restaurante $restaurante)
    {
        $restaurante->load(['productos' => fn ($q) => $q->disponible(), 'galeria']);
        return view('restaurantes.show', compact('restaurante'));
    }
}