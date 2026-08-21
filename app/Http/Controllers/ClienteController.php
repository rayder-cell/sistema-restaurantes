<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use App\Models\Restaurante;
use App\Models\Producto;
use App\Models\CategoriaMenu;
use App\Models\Pedido;
use App\Models\DetallePedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{
    // ── Menú digital público (via QR token) ───────────────────────
    public function menu(string $qrToken)
    {
        // Buscar mesa por QR token
        $mesa = Mesa::where('qr_token', $qrToken)->firstOrFail();
        $restaurante = $mesa->restaurante;

        // Verificar que el restaurante esté activo
        if ($restaurante->estado !== 'activo') {
            abort(404, 'Restaurante no disponible.');
        }

        // Categorías con productos disponibles
        $categorias = CategoriaMenu::where('restaurante_id', $restaurante->id)
            ->where('activa', true)
            ->orderBy('orden')
            ->get();

        // Todos los productos disponibles agrupados por categoría
        $productos = Producto::where('restaurante_id', $restaurante->id)
            ->where('disponible', true)
            ->get()
            ->groupBy('categoria_id');

        return view('cliente.menu', compact(
            'mesa',
            'restaurante',
            'categorias',
            'productos'
        ));
    }

    // ── Registrar pedido del cliente ───────────────────────────────
    public function pedido(Request $request, string $qrToken)
    {
        $mesa = Mesa::where('qr_token', $qrToken)->firstOrFail();
        $restaurante = $mesa->restaurante;

        $request->validate([
            'items' => 'required|string',
        ]);

        $items = json_decode($request->items, true);

        if (empty($items)) {
            return back()->with('error', 'El pedido está vacío.');
        }

        DB::transaction(function () use ($request, $mesa, $restaurante, $items) {

            $pedido = Pedido::create([
                'restaurante_id' => $restaurante->id,
                'mesa_id'        => $mesa->id,
                'usuario_id'     => null, // cliente externo
                'origen'         => 'cliente',
                'estado'         => 'pendiente',
                'observacion'    => $request->observacion,
                'total'          => 0,
            ]);

            foreach ($items as $item) {
                $producto = Producto::find($item['producto_id']);
                if (!$producto || !$producto->disponible) continue;

                DetallePedido::create([
                    'pedido_id'       => $pedido->id,
                    'producto_id'     => $producto->id,
                    'cantidad'        => $item['cantidad'],
                    'precio_unitario' => $producto->precio,
                    'observacion'     => $item['observacion'] ?? null,
                    'estado'          => 'pendiente',
                ]);
            }

            // Actualizar estado de mesa
            $mesa->update(['estado' => 'ocupada']);
        });

        return redirect()
            ->route('cliente.confirmacion', $qrToken)
            ->with('success', '¡Tu pedido fue enviado a cocina!');
    }

    // ── Confirmación del pedido ────────────────────────────────────
    public function confirmacion(string $qrToken)
    {
        $mesa = Mesa::where('qr_token', $qrToken)->firstOrFail();
        $restaurante = $mesa->restaurante;

        // Último pedido de esta mesa
        $pedido = Pedido::with('detalles.producto')
            ->where('mesa_id', $mesa->id)
            ->where('origen', 'cliente')
            ->whereNotIn('estado', ['entregado', 'cancelado'])
            ->latest()
            ->first();

        return view('cliente.confirmacion', compact('mesa', 'restaurante', 'pedido'));
    }

    // ── Estado del pedido en tiempo real (AJAX polling) ───────────
    public function estadoPedido(string $qrToken)
    {
        $mesa = Mesa::where('qr_token', $qrToken)->firstOrFail();

        $pedido = Pedido::with('detalles.producto')
            ->where('mesa_id', $mesa->id)
            ->whereNotIn('estado', ['entregado', 'cancelado'])
            ->latest()
            ->first();

        if (!$pedido) {
            return response()->json(['estado' => null]);
        }

        return response()->json([
            'estado'    => $pedido->estado,
            'pedido_id' => $pedido->id,
            'total'     => $pedido->total,
        ]);
    }
}
