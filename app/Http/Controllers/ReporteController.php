<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        $restauranteId = session('restaurante_id');
        $desde = $request->get('desde', now()->startOfMonth()->toDateString());
        $hasta = $request->get('hasta', now()->toDateString());

        // ── Ventas por día ─────────────────────────────────────────
        $ventasPorDia = DB::table('comprobante')
            ->where('restaurante_id', $restauranteId)
            ->where('anulado', false)
            ->whereBetween(DB::raw('DATE(emitido_at)'), [$desde, $hasta])
            ->selectRaw("DATE(emitido_at) as fecha, count(*) as total_comprobantes, sum(total) as total_ventas")
            ->groupBy(DB::raw('DATE(emitido_at)'))
            ->orderBy('fecha')
            ->get();

        // ── Resumen del período ────────────────────────────────────
        $resumen = DB::table('comprobante')
            ->where('restaurante_id', $restauranteId)
            ->where('anulado', false)
            ->whereBetween(DB::raw('DATE(emitido_at)'), [$desde, $hasta])
            ->selectRaw("
                count(*) as total_comprobantes,
                sum(total) as total_ventas,
                sum(igv) as total_igv,
                sum(subtotal) as total_subtotal,
                avg(total) as ticket_promedio
            ")->first();

        // ── Ventas por método de pago ──────────────────────────────
        $ventasPorMetodo = DB::table('metodo_pago as mp')
            ->join('comprobante as c', 'mp.comprobante_id', '=', 'c.id')
            ->where('c.restaurante_id', $restauranteId)
            ->where('c.anulado', false)
            ->whereBetween(DB::raw('DATE(c.emitido_at)'), [$desde, $hasta])
            ->selectRaw("mp.metodo, count(*) as cantidad, sum(mp.monto) as total")
            ->groupBy('mp.metodo')
            ->orderByDesc('total')
            ->get();

        // ── Productos más vendidos ─────────────────────────────────
        $productosMasVendidos = DB::table('detalle_pedido as dp')
            ->join('pedido as p', 'dp.pedido_id', '=', 'p.id')
            ->join('producto as pr', 'dp.producto_id', '=', 'pr.id')
            ->where('p.restaurante_id', $restauranteId)
            ->whereIn('p.estado', ['entregado', 'pagado', 'listo'])
            ->whereBetween(DB::raw('DATE(p.created_at)'), [$desde, $hasta])
            ->selectRaw("pr.nombre, sum(dp.cantidad) as total_vendido, sum(dp.subtotal) as total_ingresos")
            ->groupBy('pr.id', 'pr.nombre')
            ->orderByDesc('total_vendido')
            ->limit(10)
            ->get();

        // ── Pedidos por estado ─────────────────────────────────────
        $pedidosPorEstado = DB::table('pedido')
            ->where('restaurante_id', $restauranteId)
            ->whereBetween(DB::raw('DATE(created_at)'), [$desde, $hasta])
            ->selectRaw("estado, count(*) as cantidad")
            ->groupBy('estado')
            ->get();

        // ── Ventas por categoría ───────────────────────────────────
        $ventasPorCategoria = DB::table('detalle_pedido as dp')
            ->join('pedido as p', 'dp.pedido_id', '=', 'p.id')
            ->join('producto as pr', 'dp.producto_id', '=', 'pr.id')
            ->join('categoria_menu as c', 'pr.categoria_id', '=', 'c.id')
            ->where('p.restaurante_id', $restauranteId)
            ->whereIn('p.estado', ['entregado', 'pagado', 'listo'])
            ->whereBetween(DB::raw('DATE(p.created_at)'), [$desde, $hasta])
            ->selectRaw("c.nombre as categoria, sum(dp.cantidad) as total_items, sum(dp.subtotal) as total_ventas")
            ->groupBy('c.id', 'c.nombre')
            ->orderByDesc('total_ventas')
            ->get();

        // ── Indicadores del día ────────────────────────────────────
        $hoy = now()->toDateString();
        $indicadoresHoy = DB::table('pedido')
            ->where('restaurante_id', $restauranteId)
            ->whereDate('created_at', $hoy)
            ->selectRaw("
                count(*) as total_pedidos,
                sum(case when estado = 'pagado' then 1 else 0 end) as pedidos_pagados,
                sum(case when estado = 'pendiente' then 1 else 0 end) as pedidos_pendientes,
                avg(total) as ticket_promedio
            ")->first();

        return view('reportes.index', compact(
            'ventasPorDia', 'resumen', 'ventasPorMetodo',
            'productosMasVendidos', 'pedidosPorEstado',
            'ventasPorCategoria', 'indicadoresHoy',
            'desde', 'hasta'
        ));
    }
}
