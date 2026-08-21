<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\DetallePedido;
use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Notifications\PedidoListoNotification;
use Illuminate\Support\Facades\DB;

class CocinaController extends Controller
{
    // Categorías que NO pasan por cocina (ya están listas para servir)
    private const CATEGORIAS_SIN_PREPARACION = ['bebidas'];

    // ── Panel de cocina ────────────────────────────────────────────
    public function index()
    {
        $restauranteId = session('restaurante_id');

        $pendientes    = $this->pedidosConItemsDeCocina($restauranteId, 'pendiente', 'created_at', 'asc');
        $enPreparacion = $this->pedidosConItemsDeCocina($restauranteId, 'en_preparacion', 'updated_at', 'asc');
        $listos        = $this->pedidosConItemsDeCocina($restauranteId, 'listo', 'updated_at', 'asc');

        // Métricas del footer
        $totalPlatos = Pedido::where('restaurante_id', $restauranteId)
            ->whereIn('estado', ['pendiente', 'en_preparacion', 'listo'])
            ->with('detalles.producto.categoria')
            ->get()
            ->sum(fn($p) => $p->detalles->filter(fn($d) => $this->requierePreparacion($d))->count());

        // Tiempo promedio de preparación (pedidos entregados hoy)
        $tiempoPromedio = Pedido::where('restaurante_id', $restauranteId)
            ->where('estado', 'entregado')
            ->whereDate('updated_at', today())
            ->get()
            ->avg(fn($p) => $p->created_at->diffInMinutes($p->updated_at));

        return view('cocina.index', compact(
            'pendientes',
            'enPreparacion',
            'listos',
            'totalPlatos',
            'tiempoPromedio'
        ));
    }

    // ── Helper: ¿este detalle requiere preparación en cocina? ──────
    private function requierePreparacion(DetallePedido $detalle): bool
    {
        $categoriaNombre = strtolower($detalle->producto?->categoria?->nombre ?? '');
        return !in_array($categoriaNombre, self::CATEGORIAS_SIN_PREPARACION);
    }

    // ── Helper: solo pedidos con al menos un ítem que requiere cocina ──
    private function pedidosConItemsDeCocina($restauranteId, $estado, $orderBy, $direccion)
    {
        return Pedido::with(['mesa', 'detalles.producto.categoria', 'usuario'])
            ->where('restaurante_id', $restauranteId)
            ->where('estado', $estado)
            ->orderBy($orderBy, $direccion)
            ->get()
            ->map(function ($pedido) {
                $pedido->setRelation(
                    'detalles',
                    $pedido->detalles->filter(fn($d) => $this->requierePreparacion($d))
                );
                return $pedido;
            })
            ->filter(fn($pedido) => $pedido->detalles->isNotEmpty()) // oculta pedidos solo-bebida
            ->values();
    }

    // ── Cambiar estado desde cocina (AJAX) ─────────────────────────

    public function cambiarEstado(Request $request, Pedido $pedido)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,en_preparacion,listo,entregado,cancelado',
        ]);

        $estadoAnterior = $pedido->estado;
        $pedido->update(['estado' => $request->estado]);

        // Propagar el nuevo estado a cada detalle individual (excepto los ya entregados),
        // ya que Mesas depende del estado por-plato, no solo del estado general del pedido.
        if (in_array($request->estado, ['pendiente', 'en_preparacion', 'listo'])) {
            DB::table('detalle_pedido')->whereIn('pedido_id', DB::table('pedido')->where('mesa_id', function ($q) {
                $q->select('id')->from('mesa')->where('numero', 1);
            })->where('estado', 'listo')->pluck('id'))->where('estado', '!=', 'entregado')->update(['estado' => 'listo']);
        }

        // Notificar al mesero solo cuando el pedido pasa a "listo" por primera vez
        if ($request->estado === 'listo' && $estadoAnterior !== 'listo' && $pedido->usuario_id) {
            $mesero = Usuario::find($pedido->usuario_id);
            if ($mesero) {
                $mesero->notify(new PedidoListoNotification($pedido->id, $pedido->mesa?->numero));
            }
        }

        return response()->json([
            'success' => true,
            'estado'  => $request->estado,
            'pedido'  => $pedido->load(['mesa', 'detalles.producto']),
        ]);
    }

    // ── Obtener pedidos actualizados (polling AJAX, sin recargar página) ──
    public function actualizar()
    {
        $restauranteId = session('restaurante_id');

        $pendientes    = $this->pedidosConItemsDeCocina($restauranteId, 'pendiente', 'created_at', 'asc');
        $enPreparacion = $this->pedidosConItemsDeCocina($restauranteId, 'en_preparacion', 'updated_at', 'asc');
        $listos        = $this->pedidosConItemsDeCocina($restauranteId, 'listo', 'updated_at', 'asc');

        $totalPlatos = Pedido::where('restaurante_id', $restauranteId)
            ->whereIn('estado', ['pendiente', 'en_preparacion', 'listo'])
            ->with('detalles.producto.categoria')
            ->get()
            ->sum(fn($p) => $p->detalles->filter(fn($d) => $this->requierePreparacion($d))->count());

        $tiempoPromedio = Pedido::where('restaurante_id', $restauranteId)
            ->where('estado', 'entregado')
            ->whereDate('updated_at', today())
            ->get()
            ->avg(fn($p) => $p->created_at->diffInMinutes($p->updated_at));

        return response()->json([
            'pendientes'     => $pendientes->map(fn($p) => $this->serializarPendiente($p))->values(),
            'enPreparacion'  => $enPreparacion->map(fn($p) => $this->serializarPreparacion($p))->values(),
            'listos'         => $listos->map(fn($p) => $this->serializarListo($p))->values(),
            'totalPlatos'    => $totalPlatos,
            'tiempoPromedio' => round($tiempoPromedio ?? 0, 1),
        ]);
    }

    // ── Serializadores para el JSON de cada columna ──────────────────
    private function serializarPendiente($pedido)
    {
        return [
            'id'          => $pedido->id,
            'mesa_numero' => $pedido->mesa?->numero,
            'created_at'  => $pedido->created_at->toIso8601String(),
            'observacion' => $pedido->observacion,
            'detalles'    => $pedido->detalles->map(fn($d) => [
                'cantidad'    => $d->cantidad,
                'nombre'      => $d->producto?->nombre,
                'observacion' => $d->observacion,
            ])->values(),
        ];
    }

    private function serializarPreparacion($pedido)
    {
        $cocinero = null;
        if ($pedido->usuario) {
            $partes = explode(' ', $pedido->usuario->nombre);
            $cocinero = strtoupper($partes[0]) . ' ' . strtoupper(substr($partes[1] ?? '', 0, 1)) . '.';
        }

        return [
            'id'          => $pedido->id,
            'mesa_numero' => $pedido->mesa?->numero,
            'cocinero'    => $cocinero,
            'updated_at'  => $pedido->updated_at->toIso8601String(),
            'observacion' => $pedido->observacion,
            'detalles'    => $pedido->detalles->map(fn($d) => [
                'cantidad' => $d->cantidad,
                'nombre'   => $d->producto?->nombre,
                'estado'   => $d->estado,
            ])->values(),
        ];
    }

    private function serializarListo($pedido)
    {
        return [
            'id'          => $pedido->id,
            'mesa_numero' => $pedido->mesa?->numero,
            'detalles'    => $pedido->detalles->map(fn($d) => [
                'cantidad' => $d->cantidad,
                'nombre'   => $d->producto?->nombre,
            ])->values(),
        ];
    }
}
