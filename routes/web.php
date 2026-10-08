<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\MesaController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\CocinaController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\InsumoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\LicenseController;
use App\Http\Controllers\SystemSettingsController;
use App\Http\Controllers\DetallePedidoController;
use App\Http\Controllers\PushController;
use App\Http\Controllers\ConfiguracionPagoController;
use App\Services\NubeFactService;
use App\Http\Controllers\RestauranteController;
use App\Http\Controllers\PlatoController;
use App\Http\Controllers\Auth\ClienteAuthController;
use App\Services\ImpresoraTicketService;

/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
*/

Route::get('/', fn() => redirect()->route('login'));

// ── Login / logout de ADMINISTRADOR (panel interno) ─────────────
Route::get('/login',   [LoginController::class, 'showLogin'])->name('login');
Route::post('/login',  [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ── Reservas públicas por link/token ────────────────────────────
Route::get('/r/{token}', [ReservaController::class, 'reservaPublica'])->name('reserva.publica');
Route::post('/r/{token}', [ReservaController::class, 'reservaPublicaStore'])->name('reserva.publica.store');
Route::get('/reserva/confirmacion', [ReservaController::class, 'confirmacion'])->name('reserva.publica.confirmacion');

// ── Menú por QR de mesa (cliente sin login) ─────────────────────
Route::prefix('menu')->name('cliente.')->group(function () {
    Route::get('/{qrToken}',         [ClienteController::class, 'menu'])->name('menu');
    Route::post('/{qrToken}/pedido', [ClienteController::class, 'pedido'])->name('pedido');
    Route::get('/{qrToken}/gracias', [ClienteController::class, 'confirmacion'])->name('confirmacion');
    Route::get('/{qrToken}/estado',  [ClienteController::class, 'estadoPedido'])->name('estado');
});

// ── Landing / buscador público (una sola ruta) ──────────────────
Route::get('/buscar', [RestauranteController::class, 'buscar'])->name('buscar');

// ── Restaurantes (listado público) ──────────────────────────────
Route::get('/restaurantes', [RestauranteController::class, 'index'])->name('restaurantes.index');
Route::get('/restaurantes/{restaurante}', [RestauranteController::class, 'show'])->name('restaurantes.show');

// ── Platos (listado público) ────────────────────────────────────
Route::get('/platos', [PlatoController::class, 'index'])->name('platos.index');

// ── Login / registro de CLIENTES (distinto del login admin) ─────
Route::prefix('cliente')->name('cliente.')->group(function () {
    Route::get('/login',    [ClienteAuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [ClienteAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout',  [ClienteAuthController::class, 'logout'])->name('logout');
    Route::get('/registro', [ClienteAuthController::class, 'showRegistro'])->name('registro');
    Route::post('/registro', [ClienteAuthController::class, 'registro'])->name('registro.submit');
});



Route::get('/test-impresora', function (ImpresoraTicketService $impresora) {
    if (!$impresora->estaDisponible()) {
        return response()->json(['success' => false, 'message' => 'Impresora no detectada.']);
    }

    $resultado = $impresora->imprimirTicket([
        'restaurante'      => 'EL LIMON',
        'ruc'              => '10745788845',
        'tipo'             => 'boleta',
        'numero_completo'  => 'B002-000071',
        'fecha'            => now()->format('d/m/Y H:i'),
        'items' => [
            ['cantidad' => 1, 'descripcion' => 'Chicharrón', 'total' => 29.98],
        ],
        'subtotal' => 25.42,
        'igv'      => 4.58,
        'total'    => 30.00,
    ]);

    return response()->json($resultado);
});

/*
|--------------------------------------------------------------------------
| Rutas protegidas (requieren sesión de administrador)
|--------------------------------------------------------------------------
*/
Route::middleware('auth.web')->group(function () {

    // ── Dashboard ────────────────────────────────────────────────
    // El mesero, cocinero y cajero no tienen acceso al dashboard general.
    Route::get('/dashboard', function (\Illuminate\Http\Request $request) {
        if (session('usuario_rol') === 'mesero') {
            return redirect()->route('mesas.index');
        }
        if (session('usuario_rol') === 'cocinero') {
            return redirect()->route('cocina.index');
        }
        if (session('usuario_rol') === 'cajero') {
            return redirect()->route('caja.index');
        }
        return app(DashboardController::class)->index($request);
    })->name('dashboard');

    // ── SuperAdmin ───────────────────────────────────────────────
    Route::prefix('superadmin')->name('superadmin.')->group(function () {
        Route::get('/dashboard',                              [SuperAdminController::class, 'dashboard'])->name('dashboard');
        Route::post('/restaurantes',                          [SuperAdminController::class, 'store'])->name('restaurantes.store');
        Route::patch('/restaurantes/{restaurante}/suspender', [SuperAdminController::class, 'suspender'])->name('restaurantes.suspender');
        Route::patch('/restaurantes/{restaurante}/reactivar', [SuperAdminController::class, 'reactivar'])->name('restaurantes.reactivar');
        Route::get('/restaurantes/{restaurante}',             [SuperAdminController::class, 'show'])->name('restaurantes.show');
        Route::get('/restaurantes/{restaurante}/edit',        [SuperAdminController::class, 'edit'])->name('restaurantes.edit');
        Route::put('/restaurantes/{restaurante}',             [SuperAdminController::class, 'update'])->name('restaurantes.update');
        Route::delete('/restaurantes/{restaurante}',          [SuperAdminController::class, 'destroy'])->name('restaurantes.destroy');
        Route::get('/license',                        [LicenseController::class, 'index'])->name('license');
        Route::post('/license/{restaurante}/renovar', [LicenseController::class, 'renovar'])->name('license.renovar');
        Route::get('/settings',  [SystemSettingsController::class, 'index'])->name('settings');
        Route::post('/settings', [SystemSettingsController::class, 'update'])->name('settings.update');
        Route::get('/consultar-ruc/{ruc}', [SuperAdminController::class, 'consultarRuc'])->name('restaurantes.consultar-ruc');
    });

    // ── Usuarios ─────────────────────────────────────────────────
    Route::resource('usuarios', UsuarioController::class);
    Route::patch('usuarios/{usuario}/toggle', [UsuarioController::class, 'toggleActivo'])->name('usuarios.toggle');

    // ── Productos ─────────────────────────────────────────────────
    Route::prefix('productos')->name('productos.')->group(function () {
        Route::get('/',                   [ProductoController::class, 'index'])->name('index');
        Route::post('/',                  [ProductoController::class, 'store'])->name('store');
        Route::put('/{producto}',         [ProductoController::class, 'update'])->name('update');
        Route::delete('/{producto}',      [ProductoController::class, 'destroy'])->name('destroy');
        Route::post('/{producto}/toggle', [ProductoController::class, 'toggleDisponible'])->name('toggle');
    });

    // ── Categorías ────────────────────────────────────────────────
    Route::prefix('categorias')->name('categorias.')->group(function () {
        Route::get('/',               [CategoriaController::class, 'index'])->name('index');
        Route::post('/',              [CategoriaController::class, 'store'])->name('store');
        Route::put('/{categoria}',    [CategoriaController::class, 'update'])->name('update');
        Route::delete('/{categoria}', [CategoriaController::class, 'destroy'])->name('destroy');
    });

    // ── Mesas ─────────────────────────────────────────────────────
    Route::prefix('mesas')->name('mesas.')->controller(MesaController::class)->group(function () {
        Route::get('/',                      'index')->name('index');
        Route::get('/actualizar',            'actualizar')->name('actualizar');
        Route::post('/',                     'store')->name('store')->middleware('rol:propietario,administrador');
        Route::put('/{mesa}',                'update')->name('update')->middleware('rol:propietario,administrador');
        Route::post('/{mesa}/estado',        'cambiarEstado')->name('estado');
        Route::post('/{id}/liberar-manual',  'liberarManual')->name('liberar-manual');
        Route::delete('/{mesa}',             'destroy')->name('destroy')->middleware('rol:propietario,administrador');
        Route::get('/{mesa}/qr-imagen', [MesaController::class, 'qrImagen'])->name('qr');
    });

    // ── Pedidos ───────────────────────────────────────────────────
    // IMPORTANTE: rutas estáticas ANTES de rutas con parámetros
    Route::prefix('pedidos')->name('pedidos.')->group(function () {
        Route::get('/', function (\Illuminate\Http\Request $request) {
            if (session('usuario_rol') === 'cocinero') {
                return redirect()->route('cocina.index');
            }
            return app(PedidoController::class)->index($request);
        })->name('index');
        Route::get('/actualizar',            [PedidoController::class, 'actualizar'])->name('actualizar');
        Route::get('/nuevo',                 [PedidoController::class, 'crear'])->name('crear');
        Route::get('/productos',             [PedidoController::class, 'productosPorCategoria'])->name('productos'); // ← antes de /{pedido}
        Route::post('/',                     [PedidoController::class, 'store'])->name('store');
        Route::get('/{pedido}',              [PedidoController::class, 'show'])->name('show');
        Route::patch('/{pedido}/estado',     [PedidoController::class, 'cambiarEstado'])->name('estado');
        Route::delete('/{pedido}',           [PedidoController::class, 'destroy'])->name('destroy');
    });

    // ── Cocina ────────────────────────────────────────────────────
    Route::prefix('cocina')->name('cocina.')->group(function () {
        Route::get('/',                      [CocinaController::class, 'index'])->name('index');
        Route::patch('/{pedido}/estado',     [CocinaController::class, 'cambiarEstado'])->name('estado');
        Route::get('/actualizar',            [CocinaController::class, 'actualizar'])->name('actualizar');
    });

    // ── Reservas (panel admin) ──────────────────────────────────────
    // El mesero no tiene acceso al listado de reservas.
    Route::resource('reservas', ReservaController::class)->except(['index']);
    Route::get('reservas', function (\Illuminate\Http\Request $request) {
        if (session('usuario_rol') === 'mesero') {
            return redirect()->route('mesas.index');
        }
        return app(ReservaController::class)->index($request);
    })->name('reservas.index');
    Route::patch('reservas/{id}/estado', [ReservaController::class, 'cambiarEstado'])->name('reservas.estado');

    // ── Caja ───────────────────────────────────────────────────────
    Route::prefix('caja')->name('caja.')->group(function () {
        Route::get('/',                    [CajaController::class, 'index'])->name('index');
        Route::get('/cuenta/{clave}',      [CajaController::class, 'detalleCuenta'])->name('cuenta');
        Route::get('/mesa/{mesaId}',       [CajaController::class, 'detalleMesa'])->name('mesa');
        Route::get('/pedido/{id}',         [CajaController::class, 'detallePedido'])->name('pedido');
        Route::post('/emitir',             [CajaController::class, 'emitirComprobante'])->name('emitir');
        Route::post('/abrir',              [CajaController::class, 'abrirCaja'])->name('abrir');
        Route::post('/cerrar',             [CajaController::class, 'cerrarCaja'])->name('cerrar');
        Route::get('/historial',           [CajaController::class, 'historial'])->name('historial');
        Route::patch('/anular/{id}',       [CajaController::class, 'anular'])->name('anular');
        Route::get('/comprobante/{id}/pdf', [CajaController::class, 'comprobantePdf'])->name('comprobante.pdf');
    });

    // ── Inventario ───────────────────────────────────────────────────
    Route::resource('insumos', InsumoController::class)->except(['show']);
    Route::get('insumos/{id}/kardex', [InsumoController::class, 'kardex'])->name('insumos.kardex');
    Route::resource('proveedores', ProveedorController::class)->except(['show']);

    // ── Reportes ───────────────────────────────────────────────────
    Route::get('/reportes/ventas', [ReporteController::class, 'index'])->name('reportes.ventas');

    Route::post('/pedidos/detalle/{detalle}/entregar', [DetallePedidoController::class, 'entregar'])->name('pedidos.detalle.entregar');

    // ── Push Notifications ──────────────────────────────────────────
    Route::prefix('push')->name('push.')->group(function () {
        Route::get('/vapid-key', [PushController::class, 'vapidKey'])->name('vapid-key');
        Route::post('/subscribe', [PushController::class, 'subscribe'])->name('subscribe');
    });

    // ── Configuración de pagos ───────────────────────────────────────
    Route::prefix('configuracion')->name('configuracion.')->middleware('rol:propietario,administrador')->group(function () {
        Route::get('/pagos',    [ConfiguracionPagoController::class, 'index'])->name('pagos');
        Route::post('/pagos',   [ConfiguracionPagoController::class, 'guardar'])->name('pagos.guardar');
        Route::delete('/pagos', [ConfiguracionPagoController::class, 'eliminar'])->name('pagos.eliminar');
    });

    Route::get('/verificar-sunat/{serie}/{numero}', function ($serie, $numero, \App\Services\NubeFactService $nf) {
        return response()->json($nf->consultarEstado(2, $serie, $numero)); // 2 = boleta
    });
});
