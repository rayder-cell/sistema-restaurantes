<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'rol'              => \App\Http\Middleware\VerificarRol::class,
            'verificar.tenant' => \App\Http\Middleware\VerificarTenant::class,
            'auth.web'         => \App\Http\Middleware\AutenticadoWeb::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Solo aplicar JSON en rutas API reales
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'No autenticado.'], 401);
            }
        });

        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'No autorizado.'], 403);
            }
        });

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Error de validación.',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });
    })->create();