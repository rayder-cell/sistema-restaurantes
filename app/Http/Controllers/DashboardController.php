<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Reserva;
use App\Models\Mesa;
use App\Models\Comprobante;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $rol           = session('usuario_rol');
        $restauranteId = session('restaurante_id');
        $hoy           = Carbon::today();

        // ── SUPERADMIN: redirigir a su propio panel ────────────────
        if ($rol === 'superadmin') {
            return redirect()->route('superadmin.dashboard');
        }

        // ── PROPIETARIO ────────────────────────────────────────────
        if ($rol === 'propietario') {
            return $this->dashboardPropietario($restauranteId, $hoy);
        }

        // ── COCINERO: solo pedidos en cocina ───────────────────────
        if ($rol === 'cocinero') {
            return redirect()->route('pedidos.index');
        }

        // ── CAJERO: solo caja ──────────────────────────────────────
        if ($rol === 'cajero') {
            return redirect()->route('caja.index');
        }

        // ── ADMINISTRADOR / MESERO: dashboard general ──────────────
        return $this->dashboardGeneral($restauranteId, $hoy);
    }

    // ── Dashboard para Propietario ─────────────────────────────────
    private function dashboardPropietario($restauranteId, $hoy)
    {
        // Ventas del día
        $ventasHoy = Comprobante::where('restaurante_id', $restauranteId)
            ->whereDate('emitido_at', $hoy)
            ->where('anulado', false)
            ->sum('total');

        // Ventas de la semana
        $ventasSemana = Comprobante::where('restaurante_id', $restauranteId)
            ->whereBetween('emitido_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ])
            ->where('anulado', false)
            ->sum('total');

        // Ventas del mes
        $ventasMes = Comprobante::where('restaurante_id', $restauranteId)
            ->whereMonth('emitido_at', $hoy->month)
            ->whereYear('emitido_at', $hoy->year)
            ->where('anulado', false)
            ->sum('total');

        // Pedidos atendidos hoy
        $pedidosHoy = Pedido::where('restaurante_id', $restauranteId)
            ->whereDate('created_at', $hoy)
            ->count();

        // Reservas de hoy
        $reservasHoy = Reserva::where('restaurante_id', $restauranteId)
            ->whereDate('fecha', $hoy)
            ->count();

        // Reservas del día con detalle
        $reservasDelDia = Reserva::where('restaurante_id', $restauranteId)
            ->whereDate('fecha', $hoy)
            ->orderBy('hora')
            ->limit(10)
            ->get();

        return view('dashboard.propietario', compact(
            'ventasHoy',
            'ventasSemana',
            'ventasMes',
            'pedidosHoy',
            'reservasHoy',
            'reservasDelDia'
        ));
    }

    // ── Dashboard general (administrador / mesero) ─────────────────
    private function dashboardGeneral($restauranteId, $hoy)
    {
        // Pedidos de hoy
        $pedidosHoy = Pedido::where('restaurante_id', $restauranteId)
            ->whereDate('created_at', $hoy)
            ->count();

        // Ventas de hoy
        $ventasHoy = Comprobante::where('restaurante_id', $restauranteId)
            ->whereDate('emitido_at', $hoy)
            ->where('anulado', false)
            ->sum('total');

        // Mesas ocupadas
        $mesasOcupadas = Mesa::where('restaurante_id', $restauranteId)
            ->where('estado', 'ocupada')
            ->count();

        // Reservas de hoy
        $reservasHoy = Reserva::where('restaurante_id', $restauranteId)
            ->whereDate('fecha', $hoy)
            ->count();

        // Pedidos activos (últimos 10)
        $pedidosActivos = Pedido::with('mesa')
            ->where('restaurante_id', $restauranteId)
            ->whereNotIn('estado', ['entregado', 'cancelado'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Reservas del día
        $reservasDelDia = Reserva::where('restaurante_id', $restauranteId)
            ->whereDate('fecha', $hoy)
            ->orderBy('hora')
            ->limit(10)
            ->get();

        return view('dashboard.index', compact(
            'pedidosHoy',
            'ventasHoy',
            'mesasOcupadas',
            'reservasHoy',
            'pedidosActivos',
            'reservasDelDia'
        ));
    }
}
