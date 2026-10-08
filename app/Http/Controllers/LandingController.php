<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class LandingController extends Controller
{
    public function index()
    {
        $restaurantes = DB::table('restaurante')
            ->where('estado', 'activo')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'direccion', 'logo_url']);

        $platos = DB::table('producto as p')
            ->join('restaurante as r', 'p.restaurante_id', '=', 'r.id')
            ->where('p.disponible', true)
            ->where('r.estado', 'activo')
            ->orderBy('p.created_at', 'desc')
            ->limit(8)
            ->select('p.nombre', 'p.precio', 'p.imagen_url', 'r.nombre as restaurante_nombre')
            ->get();

        return view('landing.index', compact('restaurantes', 'platos'));
    }
}