<?php

namespace App\Http\Controllers;

use App\Models\Restaurante;
use App\Models\Usuario;
use App\Models\Pedido;
use Illuminate\Http\Request;

class SystemSettingsController extends Controller
{
    private function getSettings(): array
    {
        return [
            'plataforma_nombre' => config('app.name', 'MIKHUNAWASI'),
            'version'           => '2.4.0',
            'igv'               => 18,
            'moneda'            => 'PEN',
            'moneda_simbolo'    => 'S/',
            'zona_horaria'      => config('app.timezone', 'America/Lima'),
            'idioma'            => config('app.locale', 'es'),
            'mantenimiento'     => app()->isDownForMaintenance(),
        ];
    }

    public function index()
    {
        $settings = $this->getSettings();
        $metrics  = [
            'total_restaurantes' => Restaurante::count(),
            'total_usuarios'     => Usuario::count(),
            'total_pedidos'      => Pedido::count(),
            'uptime'             => '100%',
        ];

        return view('superadmin.settings', compact('settings', 'metrics'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'igv'            => 'required|numeric|min:0|max:100',
            'moneda_simbolo' => 'required|string|max:5',
            'zona_horaria'   => 'required|string',
        ]);

        return redirect()
            ->route('superadmin.settings')
            ->with('success', 'Configuración del sistema actualizada correctamente.');
    }
}
