<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarRol
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Leer rol desde sesión (sistema web), no desde token
        $rolUsuario = session('usuario_rol');

        if (!$rolUsuario) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión.');
        }

        if (!in_array($rolUsuario, $roles)) {
            abort(403, 'No tienes permisos para realizar esta acción.');
        }

        return $next($request);
    }
}