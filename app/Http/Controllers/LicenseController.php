<?php

namespace App\Http\Controllers;

use App\Models\Restaurante;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    public function index()
    {
        $restaurantes = Restaurante::with('seriesComprobante')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($r) {
                $r->licencia_inicio = $r->created_at;
                $r->licencia_fin    = $r->created_at->copy()->addYear();
                $r->dias_restantes  = (int) now()->diffInDays($r->licencia_fin, false);
                $r->licencia_estado = $r->dias_restantes > 30
                    ? 'vigente'
                    : ($r->dias_restantes > 0 ? 'por_vencer' : 'vencida');
                return $r;
            });

        $vigentes  = $restaurantes->where('licencia_estado', 'vigente')->count();
        $porVencer = $restaurantes->where('licencia_estado', 'por_vencer')->count();
        $vencidas  = $restaurantes->where('licencia_estado', 'vencida')->count();

        return view('superadmin.license', compact(
            'restaurantes', 'vigentes', 'porVencer', 'vencidas'
        ));
    }

    public function renovar(Restaurante $restaurante)
    {
        return redirect()
            ->route('superadmin.license')
            ->with('success', "Licencia de \"{$restaurante->nombre}\" renovada por 1 año.");
    }
}
