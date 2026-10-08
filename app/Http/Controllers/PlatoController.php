<?php

namespace App\Http\Controllers;

use App\Models\Producto;

class PlatoController extends Controller
{
    public function index()
    {
        $platos = Producto::with('restaurante:id,nombre')
            ->whereHas('restaurante', fn ($q) => $q->activo())
            ->disponible()
            ->paginate(12);

        return view('platos.index', compact('platos'));
    }
}