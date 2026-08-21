<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

/*
|--------------------------------------------------------------------------
| Rutas públicas (sin autenticación)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Rutas protegidas (requieren token Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'verificar.tenant'])->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::get('/perfil',             [AuthController::class, 'perfil']);
        Route::put('/perfil',             [AuthController::class, 'actualizarPerfil']);
        Route::post('/logout',            [AuthController::class, 'logout']);

        // Solo superadmin y propietario pueden registrar usuarios
        Route::post('/register', [AuthController::class, 'register'])
            ->middleware('rol:superadmin,propietario');
    });

    /*
    |----------------------------------------------------------------------
    | Aquí irán las demás rutas del sistema organizadas por módulo
    |----------------------------------------------------------------------
    | Ejemplo:
    | Route::middleware('rol:superadmin')->group(function () {
    |     // Rutas solo para superadmin
    | });
    |
    | Route::middleware('rol:superadmin,propietario,administrador')->group(function () {
    |     // Rutas para admins
    | });
    */

});
