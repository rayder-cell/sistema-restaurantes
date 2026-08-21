<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\Mesa;
use App\Models\Producto;
use App\Models\CategoriaMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Cliente;

class PedidoController extends Controller
{
    // ── Lista de pedidos activos ───────────────────────────────────
    public function index()
    {
        $restauranteId = session('restaurante_id');
        $usuarioId     = session('usuario_id');
        $rol           = session('usuario_rol');

        $verTodo = in_array($rol, ['propietario', 'administrador', 'cajero']);

        $pedidosQuery = Pedido::with(['mesa', 'detalles.producto'])
            ->where('restaurante_id', $restauranteId);

        if ($rol === 'cajero') {
            // El cajero ve absolutamente todos los pedidos, sin filtrar por estado
            // (necesita ver los "entregado" para cobrarlos, y también el historial reciente).
        } else {
            $pedidosQuery->whereNotIn('estado', ['entregado', 'cancelado']);
            if (!$verTodo) {
                $pedidosQuery->where('usuario_id', $usuarioId);
            }
        }

        $pedidosRaw = $pedidosQuery->orderBy('created_at', 'desc')->get();

        // Agrupar por mesa: varios pedidos de la misma mesa = una sola tarjeta
        $pedidos = $this->agruparPorMesa($pedidosRaw);

        $mesas = Mesa::where('restaurante_id', $restauranteId)
            ->orderBy('numero')
            ->get();

        $duenoPorMesa = Pedido::where('restaurante_id', $restauranteId)
            ->whereNotIn('estado', ['pagado', 'cancelado'])
            ->whereNotNull('mesa_id')
            ->get(['mesa_id', 'usuario_id'])
            ->keyBy('mesa_id')
            ->map(fn($p) => $p->usuario_id);

        return view('pedidos.index', compact('pedidos', 'mesas', 'duenoPorMesa', 'verTodo', 'usuarioId'));
    }

    // ── Agrupa una colección de pedidos por mesa, combinando sus detalles ──
    // ── Agrupa una colección de pedidos por mesa, combinando sus detalles ──
    private function agruparPorMesa($pedidos)
    {
        $ordenEstado = ['pendiente' => 0, 'en_preparacion' => 1, 'listo' => 2, 'entregado' => 3, 'cancelado' => 4];

        return $pedidos
            ->groupBy(fn($p) => $p->mesa_id ? 'mesa-' . $p->mesa_id : 'suelto-' . $p->id)
            ->sortByDesc(fn($grupo) => $grupo->max('created_at'))
            ->map(function ($grupo) use ($ordenEstado) {
                $primero = $grupo->sortBy('created_at')->first();
                $ultimo  = $grupo->sortByDesc('created_at')->first();

                $estadoCombinado = $grupo
                    ->sortBy(fn($p) => $ordenEstado[$p->estado] ?? 99)
                    ->first()
                    ->estado;

                return (object) [
                    'id'          => $ultimo->id,
                    'mesa_numero' => $primero->mesa?->numero ?? $primero->mesa_liberada_numero,
                    'total'       => $grupo->sum('total'),
                    'estado'      => $estadoCombinado,
                    'created_at'  => $primero->created_at,
                    'detalles'    => $grupo->flatMap->detalles,
                ];
            })
            ->values();
    }

    // ── Vista toma de pedidos (mesero) ─────────────────────────────
    public function crear(Request $request)
    {
        $restauranteId = session('restaurante_id');

        $mesas = Mesa::where('restaurante_id', $restauranteId)->orderBy('numero')->get();

        $categorias = CategoriaMenu::where('restaurante_id', $restauranteId)
            ->where('activa', true)
            ->orderBy('orden')
            ->get();

        $categoriaId = $request->get('categoria_id', $categorias->first()?->id);

        $productos = Producto::where('restaurante_id', $restauranteId)
            ->where('categoria_id', $categoriaId)
            ->where('disponible', true)
            ->get();

        $mesaId     = $request->get('mesa_id');
        $mesaActual = $mesaId ? Mesa::find($mesaId) : null;

        // ── Bloquear acceso si la mesa la atiende otro mesero ──────────
        if ($mesaActual && $mesaActual->estado === 'ocupada') {
            $rol     = session('usuario_rol');
            $verTodo = in_array($rol, ['propietario', 'administrador']);

            if (!$verTodo) {
                $pedidoDeOtro = Pedido::where('restaurante_id', $restauranteId)
                    ->where('mesa_id', $mesaId)
                    ->whereNotIn('estado', ['pagado', 'cancelado'])
                    ->where('usuario_id', '!=', session('usuario_id'))
                    ->exists();

                if ($pedidoDeOtro) {
                    return redirect()->route('pedidos.index')
                        ->with('error', 'Esta mesa está siendo atendida por otro mesero.');
                }
            }
        }

        $pedidoActivo  = null;
        $clienteActivo = null;
        if ($mesaId) {
            $pedidoActivo = Pedido::with('detalles.producto', 'cliente')
                ->where('restaurante_id', $restauranteId)
                ->where('mesa_id', $mesaId)
                ->whereNotIn('estado', ['pagado', 'cancelado'])
                ->latest()
                ->first();

            $clienteActivo = $pedidoActivo?->cliente;
        }

        return view('pedidos.crear', compact(
            'mesas',
            'categorias',
            'productos',
            'categoriaId',
            'mesaActual',
            'pedidoActivo',
            'clienteActivo'
        ));
    }

    // ── ¿La mesa ya tiene cliente activo? (AJAX al cambiar de mesa) ────
    public function clienteActivoPorMesa($mesaId)
    {
        $restauranteId = session('restaurante_id');

        $pedidoActivo = Pedido::with('cliente')
            ->where('restaurante_id', $restauranteId)
            ->where('mesa_id', $mesaId)
            ->whereNotIn('estado', ['pagado', 'cancelado'])
            ->latest()
            ->first();

        return response()->json([
            'tiene_cliente' => (bool) $pedidoActivo?->cliente_id,
            'cliente' => $pedidoActivo?->cliente ? [
                'nombre'    => $pedidoActivo->cliente->nombre,
                'apellidos' => $pedidoActivo->cliente->apellidos,
            ] : null,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'mesa_id' => 'required|exists:mesa,id',
            'items'   => 'required|string',
        ]);

        $restauranteId = session('restaurante_id');
        $usuarioId     = session('usuario_id');
        $items         = json_decode($request->items, true);

        if (empty($items)) {
            return back()->with('error', 'El pedido no tiene productos.');
        }

        // ── Bloquear si la mesa la atiende otro mesero ──────────────────
        $rol     = session('usuario_rol');
        $verTodo = in_array($rol, ['propietario', 'administrador']);

        if (!$verTodo) {
            $pedidoDeOtro = Pedido::where('restaurante_id', $restauranteId)
                ->where('mesa_id', $request->mesa_id)
                ->whereNotIn('estado', ['pagado', 'cancelado'])
                ->where('usuario_id', '!=', $usuarioId)
                ->exists();

            if ($pedidoDeOtro) {
                return redirect()->route('pedidos.index')
                    ->with('error', 'Esta mesa está siendo atendida por otro mesero.');
            }
        }

        // ── ¿La mesa ya tiene un cliente activo (pedido sin pagar)? ────
        $pedidoActivoMesa = Pedido::where('restaurante_id', $restauranteId)
            ->where('mesa_id', $request->mesa_id)
            ->whereNotIn('estado', ['pagado', 'cancelado'])
            ->latest()
            ->first();

        $clienteId = $pedidoActivoMesa->cliente_id ?? null;

        if (!$clienteId) {
            $request->validate([
                'cliente_nombre'    => 'required|string|max:100',
                'cliente_apellidos' => 'required|string|max:100',
            ]);

            $cliente = Cliente::create([
                'restaurante_id' => $restauranteId,
                'nombre'         => $request->cliente_nombre,
                'apellidos'      => $request->cliente_apellidos,
            ]);
            $clienteId = $cliente->id;
        }

        DB::transaction(function () use ($request, $restauranteId, $usuarioId, $items, $clienteId) {

            $pedido = Pedido::create([
                'restaurante_id' => $restauranteId,
                'mesa_id'        => $request->mesa_id,
                'cliente_id'     => $clienteId,
                'usuario_id'     => $usuarioId,
                'origen'         => 'mesero',
                'estado'         => 'pendiente',
                'observacion'    => $request->observacion,
                'total'          => 0,
            ]);

            foreach ($items as $item) {
                $producto = Producto::find($item['producto_id']);
                if (!$producto) continue;

                DetallePedido::create([
                    'pedido_id'       => $pedido->id,
                    'producto_id'     => $producto->id,
                    'cantidad'        => $item['cantidad'],
                    'precio_unitario' => $producto->precio,
                    'observacion'     => $item['observacion'] ?? null,
                    'estado'          => 'pendiente',
                ]);
            }

            Mesa::where('id', $request->mesa_id)->update(['estado' => 'ocupada']);
        });

        return redirect()
            ->route('pedidos.index')
            ->with('success', 'Pedido confirmado y enviado a cocina.');
    }

    // ── Cambiar estado del pedido ──────────────────────────────────
    public function cambiarEstado(Request $request, Pedido $pedido)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,en_preparacion,listo,entregado,cancelado',
        ]);

        $pedido->update(['estado' => $request->estado]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'estado' => $request->estado]);
        }

        return back()->with('success', 'Estado del pedido actualizado.');
    }

    // ── Obtener productos por categoría (AJAX) ─────────────────────
    public function productosPorCategoria(Request $request)
    {
        $restauranteId = session('restaurante_id');
        $categoriaId   = $request->get('categoria_id');

        $productos = Producto::where('restaurante_id', $restauranteId)
            ->where('categoria_id', $categoriaId)
            ->get(['id', 'nombre', 'descripcion', 'precio', 'imagen_url', 'disponible']);

        return response()->json($productos);
    }

    // ── Ver detalle de un pedido ───────────────────────────────────
    public function show(Pedido $pedido)
    {
        if ($pedido->restaurante_id !== session('restaurante_id')) {
            abort(403);
        }

        $pedido->load(['mesa', 'detalles.producto', 'usuario']);

        return view('pedidos.show', compact('pedido'));
    }

    // ── Cancelar pedido ────────────────────────────────────────────
    public function destroy(Pedido $pedido)
    {
        if ($pedido->restaurante_id !== session('restaurante_id')) {
            abort(403);
        }

        $pedido->update(['estado' => 'cancelado']);

        // Liberar mesa si no hay otros pedidos activos
        $otroPedido = Pedido::where('mesa_id', $pedido->mesa_id)
            ->where('id', '!=', $pedido->id)
            ->whereNotIn('estado', ['entregado', 'cancelado'])
            ->exists();

        if (!$otroPedido) {
            Mesa::where('id', $pedido->mesa_id)->update(['estado' => 'libre']);
        }

        return redirect()
            ->route('pedidos.index')
            ->with('success', 'Pedido cancelado.');
    }

    // ── Datos actualizados de pedidos (polling AJAX, sin recargar página) ──
    public function actualizar()
    {
        $restauranteId = session('restaurante_id');
        $usuarioId     = session('usuario_id');
        $rol           = session('usuario_rol');

        $verTodo = in_array($rol, ['propietario', 'administrador', 'cajero']);

        $pedidosQuery = Pedido::with(['mesa', 'detalles.producto'])
            ->where('restaurante_id', $restauranteId);

        if ($rol !== 'cajero') {
            $pedidosQuery->whereNotIn('estado', ['entregado', 'cancelado']);
            if (!$verTodo) {
                $pedidosQuery->where('usuario_id', $usuarioId);
            }
        }

        $pedidosRaw = $pedidosQuery->orderBy('created_at', 'desc')->get();

        $mesas = Mesa::where('restaurante_id', $restauranteId)
            ->orderBy('numero')
            ->get();

        $duenoPorMesa = Pedido::where('restaurante_id', $restauranteId)
            ->whereNotIn('estado', ['pagado', 'cancelado'])
            ->whereNotNull('mesa_id')
            ->get(['mesa_id', 'usuario_id'])
            ->keyBy('mesa_id')
            ->map(fn($p) => $p->usuario_id);

        $mesasData = $mesas->map(function ($mesa) use ($duenoPorMesa, $verTodo, $usuarioId) {
            $dueno = $duenoPorMesa[$mesa->id] ?? null;
            $esAjena = $mesa->estado === 'ocupada' && !$verTodo && $dueno !== null && $dueno !== $usuarioId;

            return [
                'id'        => $mesa->id,
                'numero'    => $mesa->numero,
                'capacidad' => $mesa->capacidad,
                'estado'    => $mesa->estado,
                'esAjena'   => $esAjena,
            ];
        });

        // Agrupar por mesa: la misma lógica que en index()
        $ordenEstado = ['pendiente' => 0, 'en_preparacion' => 1, 'listo' => 2, 'entregado' => 3, 'cancelado' => 4];

        $pedidosData = $pedidosRaw
            ->groupBy(fn($p) => $p->mesa_id ? 'mesa-' . $p->mesa_id : 'suelto-' . $p->id)
            ->sortByDesc(fn($grupo) => $grupo->max('created_at'))
            ->map(function ($grupo) use ($ordenEstado) {
                $primero = $grupo->sortBy('created_at')->first();
                $ultimo  = $grupo->sortByDesc('created_at')->first();

                $estadoCombinado = $grupo
                    ->sortBy(fn($p) => $ordenEstado[$p->estado] ?? 99)
                    ->first()
                    ->estado;

                $detallesUnidos = $grupo->flatMap->detalles;

                return [
                    'id'              => $ultimo->id,
                    'mesa_numero'     => $primero->mesa?->numero,
                    'total'           => $grupo->sum('total'),
                    'estado'          => $estadoCombinado,
                    'created_at_diff' => $primero->created_at->diffForHumans(),
                    'detalles_count'  => $detallesUnidos->count(),
                    'mesa_numero'     => $primero->mesa?->numero ?? $primero->mesa_liberada_numero,
                    'detalles'        => $detallesUnidos->take(3)->map(fn($d) => [
                        'cantidad' => $d->cantidad,
                        'nombre'   => $d->producto?->nombre,
                    ])->values(),
                ];
            })
            ->values();

        return response()->json([
            'pedidos'  => $pedidosData,
            'mesas'    => $mesasData,
            'esCajero' => $rol === 'cajero',
        ]);
    }
}
