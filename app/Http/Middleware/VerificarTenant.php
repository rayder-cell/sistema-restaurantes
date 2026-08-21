<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        // Leer desde sesión (sistema web)
        $rolUsuario      = session('usuario_rol');
        $restauranteId   = session('restaurante_id');

        if (!$rolUsuario) {
            return redirect()->route('login');
        }

        if ($rolUsuario === 'superadmin') {
            return $next($request);
        }

        if (!$restauranteId) {
            abort(403, 'Tu usuario no está asignado a ningún restaurante.');
        }

        // Inyectar restaurante_id en el request para los controladores
        $request->merge(['restaurante_id' => $restauranteId]);

        return $next($request);
    }
}