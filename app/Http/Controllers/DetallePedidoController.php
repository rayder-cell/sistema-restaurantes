<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DetallePedidoController extends Controller
{
    public function entregar($id)
    {
        $detalle = DB::table('detalle_pedido')->where('id', $id)->first();

        if (!$detalle) {
            return response()->json(['success' => false, 'message' => 'Detalle no encontrado.'], 404);
        }

        // Solo se puede entregar un plato que cocina ya marcó como "listo"
        if ($detalle->estado !== 'listo') {
            return response()->json([
                'success' => false,
                'message' => 'Este plato todavía está en preparación. Espera a que cocina lo marque como listo.',
            ], 422);
        }

        DB::table('detalle_pedido')->where('id', $id)->update(['estado' => 'entregado']);

        $pedido = DB::table('detalle_pedido')
            ->join('pedido', 'detalle_pedido.pedido_id', '=', 'pedido.id')
            ->where('detalle_pedido.id', $id)
            ->select('pedido.id as pedido_id', 'pedido.mesa_id')
            ->first();

        $pendientes = DB::table('detalle_pedido')
            ->where('pedido_id', $pedido->pedido_id)
            ->whereIn('estado', ['pendiente', 'en_preparacion', 'listo'])
            ->count();

        // Si ya no quedan detalles pendientes, el pedido pasa a "entregado",
        // pero la mesa se mantiene ocupada hasta que se cobre en Caja.
        if ($pendientes === 0) {
            DB::table('pedido')->where('id', $pedido->pedido_id)->update(['estado' => 'entregado']);
        }

        return response()->json(['success' => true]);
    }
}