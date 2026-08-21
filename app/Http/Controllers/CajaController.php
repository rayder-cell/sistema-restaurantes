<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\CulqiService;

class CajaController extends Controller
{
    // ── Panel principal de caja ────────────────────────────────────
    public function index()
    {
        $restauranteId = session('restaurante_id');

        $caja = DB::table('caja')
            ->where('restaurante_id', $restauranteId)
            ->where('activa', true)
            ->first();

        $apertura = null;
        if ($caja) {
            $apertura = DB::table('apertura_caja')
                ->where('caja_id', $caja->id)
                ->where('estado', 'abierta')
                ->orderBy('apertura_at', 'desc')
                ->first();
        }

        $pedidosPendientes = DB::table('pedido as p')
            ->leftJoin('mesa as m', 'p.mesa_id', '=', 'm.id')
            ->leftJoin('cliente as c', 'p.cliente_id', '=', 'c.id')
            ->where('p.restaurante_id', $restauranteId)
            ->whereNotIn('p.estado', ['pagado', 'cancelado'])
            ->select(
                'p.id',
                'p.mesa_id',
                'p.mesa_liberada_numero',
                'p.total',
                'p.created_at',
                'm.numero as mesa_numero',
                'c.nombre as cliente_nombre',
                'c.apellidos as cliente_apellidos'
            )
            ->orderBy('p.created_at')
            ->get();

        $mesasACobrar = $pedidosPendientes
            ->groupBy(fn($p) => $p->mesa_id ? 'mesa-' . $p->mesa_id : 'huerfano-' . $p->id)
            ->map(function ($pedidos) {
                $primero    = $pedidos->first();
                $esHuerfano = is_null($primero->mesa_id);
                return (object) [
                    'clave'             => $esHuerfano ? 'huerfano-' . $primero->id : 'mesa-' . $primero->mesa_id,
                    'mesa_numero'       => $esHuerfano ? $primero->mesa_liberada_numero : $primero->mesa_numero,
                    'liberada'          => $esHuerfano,
                    'cliente_nombre'    => $primero->cliente_nombre,
                    'cliente_apellidos' => $primero->cliente_apellidos,
                    'total'             => $pedidos->sum('total'),
                    'pedido_ids'        => $pedidos->pluck('id')->toArray(),
                    'created_at'        => $pedidos->min('created_at'),
                ];
            })
            ->sortBy('created_at')
            ->values();

        $series = DB::table('serie_comprobante')
            ->where('restaurante_id', $restauranteId)
            ->where('activa', true)
            ->get();

        $hoy = now()->toDateString();
        $ventasHoy = DB::table('comprobante')
            ->where('restaurante_id', $restauranteId)
            ->whereDate('emitido_at', $hoy)
            ->where('anulado', false)
            ->selectRaw('count(*) as total_comprobantes, sum(total) as total_ventas')
            ->first();

        return view('caja.index', compact('caja', 'apertura', 'mesasACobrar', 'series', 'ventasHoy'));
    }

    // ── Ver detalle combinado de una cuenta (mesa activa o pedido liberado) ──
    public function detalleCuenta($clave)
    {
        $restauranteId = session('restaurante_id');

        if (str_starts_with($clave, 'mesa-')) {
            $mesaId = (int) str_replace('mesa-', '', $clave);

            $pedidos = DB::table('pedido as p')
                ->leftJoin('mesa as m', 'p.mesa_id', '=', 'm.id')
                ->leftJoin('cliente as c', 'p.cliente_id', '=', 'c.id')
                ->where('p.mesa_id', $mesaId)
                ->where('p.restaurante_id', $restauranteId)
                ->whereNotIn('p.estado', ['pagado', 'cancelado'])
                ->select('p.*', 'm.numero as mesa_numero', 'c.nombre as cliente_nombre', 'c.apellidos as cliente_apellidos')
                ->get();
        } elseif (str_starts_with($clave, 'huerfano-')) {
            $pedidoId = (int) str_replace('huerfano-', '', $clave);

            $pedidos = DB::table('pedido as p')
                ->leftJoin('cliente as c', 'p.cliente_id', '=', 'c.id')
                ->where('p.id', $pedidoId)
                ->where('p.restaurante_id', $restauranteId)
                ->whereNotIn('p.estado', ['pagado', 'cancelado'])
                ->select('p.*', 'p.mesa_liberada_numero as mesa_numero', 'c.nombre as cliente_nombre', 'c.apellidos as cliente_apellidos')
                ->get();
        } else {
            abort(404);
        }

        if ($pedidos->isEmpty()) abort(404);

        $pedidoIds = $pedidos->pluck('id');

        $detalles = DB::table('detalle_pedido as dp')
            ->join('producto as pr', 'dp.producto_id', '=', 'pr.id')
            ->whereIn('dp.pedido_id', $pedidoIds)
            ->select('dp.*', 'pr.nombre as producto_nombre')
            ->get();

        $primero = $pedidos->first();

        return response()->json([
            'mesa_numero'       => $primero->mesa_numero,
            'liberada'          => str_starts_with($clave, 'huerfano-'),
            'cliente_nombre'    => $primero->cliente_nombre,
            'cliente_apellidos' => $primero->cliente_apellidos,
            'total'             => $pedidos->sum('total'),
            'pedido_ids'        => $pedidoIds,
            'detalles'          => $detalles,
        ]);
    }

    // ── Emitir comprobante cubriendo varios pedidos de la mesa ──────
    public function emitirComprobante(Request $request)
    {
        $restauranteId = session('restaurante_id');
        $usuarioId     = session('usuario_id');

        $request->validate([
            'pedido_ids'     => 'required|string',
            'tipo'           => 'required|in:boleta,factura',
            'metodo'         => 'required|in:efectivo,tarjeta,yape,plin',
            'monto_recibido' => 'required|numeric|min:0',
            'cliente_nombre' => 'nullable|string|max:150',
            'cliente_ruc'    => 'nullable|string|max:11',
            'cliente_email'  => 'nullable|email',
            'culqi_token'    => 'required_if:metodo,tarjeta|nullable|string',
        ]);

        $pedidoIds = json_decode($request->pedido_ids, true);
        if (empty($pedidoIds)) {
            return response()->json(['success' => false, 'message' => 'No hay pedidos seleccionados.'], 422);
        }

        $pedidos = DB::table('pedido')
            ->whereIn('id', $pedidoIds)
            ->where('restaurante_id', $restauranteId)
            ->whereNotIn('estado', ['pagado', 'cancelado'])
            ->get();

        if ($pedidos->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Estos pedidos ya no están pendientes de cobro.'], 404);
        }

        $mesaId = $pedidos->first()->mesa_id;

        $serie = DB::table('serie_comprobante')
            ->where('restaurante_id', $restauranteId)
            ->where('tipo', $request->tipo)
            ->where('activa', true)
            ->first();

        if (!$serie) {
            return response()->json(['success' => false, 'message' => "No hay serie activa de {$request->tipo}."], 422);
        }

        $total    = $pedidos->sum('total');
        $subtotal = round($total / 1.18, 2);
        $igv      = round($total - $subtotal, 2);

        // ── Si el método es tarjeta, cobramos vía Culqi ANTES de tocar la BD ──
        $culqiChargeId = null;

        if ($request->metodo === 'tarjeta') {
            $culqiService = app(CulqiService::class);

            $resultado = $culqiService->crearCargo(
                token: $request->culqi_token,
                monto: $total,
                email: $request->cliente_email ?: 'cliente@mikhunawasi.pe',
                descripcion: 'Pedido(s) ' . implode(',', $pedidoIds)
            );

            if (!$resultado['success']) {
                return response()->json(['success' => false, 'message' => $resultado['message']], 422);
            }

            $culqiChargeId = $resultado['charge_id'];
        }

        // Para tarjeta no hay vuelto: se cobra el monto exacto
        $montoCobrado = $request->metodo === 'efectivo' ? $request->monto_recibido : $total;
        $vuelto       = $request->metodo === 'efectivo' ? max(0, $request->monto_recibido - $total) : 0;

        $caja = DB::table('caja')
            ->where('restaurante_id', $restauranteId)
            ->where('activa', true)
            ->first();

        $comprobanteIdRef = null;

        DB::transaction(function () use (
            $request, $restauranteId, $usuarioId, $pedidos, $pedidoIds, $mesaId,
            $serie, $subtotal, $igv, $total, $montoCobrado, $vuelto, $caja, $culqiChargeId, &$comprobanteIdRef
        ) {
            $comprobanteId = DB::table('comprobante')->insertGetId([
                'restaurante_id'     => $restauranteId,
                'pedido_id'          => $pedidos->first()->id,
                'serie_id'           => $serie->id,
                'tipo'               => $request->tipo,
                'numero_correlativo' => $serie->correlativo_actual,
                'cliente_nombre'     => $request->cliente_nombre ?? 'Cliente',
                'cliente_ruc'        => $request->cliente_ruc,
                'subtotal'           => $subtotal,
                'igv'                => $igv,
                'total'              => $total,
                'anulado'            => false,
                'emitido_at'         => now(),
            ]);

            $comprobanteIdRef = $comprobanteId;

            foreach ($pedidoIds as $pid) {
                DB::table('comprobante_pedido')->insert([
                    'comprobante_id' => $comprobanteId,
                    'pedido_id'      => $pid,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }

            DB::table('metodo_pago')->insert([
                'comprobante_id' => $comprobanteId,
                'caja_id'        => $caja?->id,
                'cajero_id'      => $usuarioId,
                'metodo'         => $request->metodo,
                'monto'          => $montoCobrado,
                'vuelto'         => $vuelto,
                'referencia'     => $culqiChargeId ?? ($request->referencia ?? null),
                'pagado_at'      => now(),
            ]);

            DB::table('serie_comprobante')->where('id', $serie->id)->increment('correlativo_actual');

            DB::table('pedido')->whereIn('id', $pedidoIds)->update(['estado' => 'pagado', 'updated_at' => now()]);

            if ($mesaId) {
                DB::table('mesa')->where('id', $mesaId)->update(['estado' => 'libre']);
            }
        });

        return response()->json([
            'success'        => true,
            'comprobante_id' => $comprobanteIdRef,
            'vuelto'         => number_format($vuelto, 2),
            'message'        => ucfirst($request->tipo) . " emitida correctamente. Vuelto: S/ " . number_format($vuelto, 2),
        ]);
    }

    // ── Abrir caja ─────────────────────────────────────────────────
    public function abrirCaja(Request $request)
    {
        $restauranteId = session('restaurante_id');
        $usuarioId     = session('usuario_id');

        $request->validate([
            'caja_id'       => 'required|integer',
            'monto_inicial' => 'required|numeric|min:0',
        ]);

        $aperturaActiva = DB::table('apertura_caja')
            ->where('caja_id', $request->caja_id)
            ->where('estado', 'abierta')
            ->exists();

        if ($aperturaActiva) {
            return back()->with('error', 'Ya existe una caja abierta.');
        }

        DB::table('apertura_caja')->insert([
            'caja_id'       => $request->caja_id,
            'cajero_id'     => $usuarioId,
            'monto_inicial' => $request->monto_inicial,
            'monto_cierre'  => 0,
            'estado'        => 'abierta',
            'apertura_at'   => now(),
        ]);

        return redirect()->route('caja.index')->with('success', 'Caja abierta correctamente.');
    }

    // ── Cerrar caja ────────────────────────────────────────────────
    public function cerrarCaja(Request $request)
    {
        $request->validate([
            'apertura_id'   => 'required|integer',
            'monto_cierre'  => 'required|numeric|min:0',
            'justificacion' => 'nullable|string|max:500',
        ]);

        $apertura = DB::table('apertura_caja')
            ->where('id', $request->apertura_id)
            ->where('estado', 'abierta')
            ->first();

        if (!$apertura) abort(404);

        $ventasEfectivo = DB::table('metodo_pago')
            ->where('caja_id', $apertura->caja_id)
            ->where('metodo', 'efectivo')
            ->where('pagado_at', '>=', $apertura->apertura_at)
            ->sum('monto');

        $esperado   = $apertura->monto_inicial + $ventasEfectivo;
        $diferencia = $request->monto_cierre - $esperado;

        DB::table('apertura_caja')
            ->where('id', $request->apertura_id)
            ->update([
                'monto_cierre'  => $request->monto_cierre,
                'justificacion' => $request->justificacion,
                'estado'        => 'cerrada',
                'cierre_at'     => now(),
            ]);

        return redirect()->route('caja.index')
            ->with('success', 'Caja cerrada. Diferencia: S/ ' . number_format($diferencia, 2));
    }

    // ── Historial de comprobantes ──────────────────────────────────
    public function historial(Request $request)
    {
        $restauranteId = session('restaurante_id');

        $query = DB::table('comprobante as c')
            ->join('serie_comprobante as s', 'c.serie_id', '=', 's.id')
            ->leftJoin('pedido as p', 'c.pedido_id', '=', 'p.id')
            ->leftJoin('mesa as m', 'p.mesa_id', '=', 'm.id')
            ->where('c.restaurante_id', $restauranteId)
            ->select('c.*', 's.serie', 'm.numero as mesa_numero');

        if ($request->filled('fecha')) {
            $query->whereDate('c.emitido_at', $request->fecha);
        }
        if ($request->filled('tipo')) {
            $query->where('c.tipo', $request->tipo);
        }

        $comprobantes = $query->orderBy('c.emitido_at', 'desc')->paginate(20);

        $totalDia = DB::table('comprobante')
            ->where('restaurante_id', $restauranteId)
            ->whereDate('emitido_at', $request->fecha ?? now()->toDateString())
            ->where('anulado', false)
            ->sum('total');

        return view('caja.historial', compact('comprobantes', 'totalDia'));
    }

    // ── Anular comprobante ─────────────────────────────────────────
    public function anular($id)
    {
        $restauranteId = session('restaurante_id');

        $comprobante = DB::table('comprobante')
            ->where('id', $id)
            ->where('restaurante_id', $restauranteId)
            ->first();

        if (!$comprobante) abort(404);

        if ($comprobante->anulado) {
            return back()->with('error', 'Este comprobante ya está anulado.');
        }

        DB::transaction(function () use ($comprobante) {
            DB::table('comprobante')
                ->where('id', $comprobante->id)
                ->update(['anulado' => true]);

            $pedidoIds = DB::table('comprobante_pedido')
                ->where('comprobante_id', $comprobante->id)
                ->pluck('pedido_id');

            if ($pedidoIds->isEmpty()) {
                $pedidoIds = collect([$comprobante->pedido_id]);
            }

            DB::table('pedido')
                ->whereIn('id', $pedidoIds)
                ->where('estado', 'pagado')
                ->update(['estado' => 'entregado', 'updated_at' => now()]);

            $mesaId = DB::table('pedido')
                ->whereIn('id', $pedidoIds)
                ->whereNotNull('mesa_id')
                ->value('mesa_id');

            if ($mesaId) {
                DB::table('mesa')->where('id', $mesaId)->update(['estado' => 'ocupada']);
            }
        });

        return back()->with('success', 'Comprobante anulado. El pedido volvió a estar pendiente de cobro.');
    }

    // ── PDF del comprobante — ahora incluye todos los pedidos vinculados ──
    public function comprobantePdf($id)
    {
        $restauranteId = session('restaurante_id');

        $comprobante = DB::table('comprobante')
            ->where('id', $id)
            ->where('restaurante_id', $restauranteId)
            ->first();

        if (!$comprobante) abort(404);

        $serie = DB::table('serie_comprobante')->where('id', $comprobante->serie_id)->first();
        $restaurante = DB::table('restaurante')->where('id', $restauranteId)->first();

        $pedidoIds = DB::table('comprobante_pedido')
            ->where('comprobante_id', $comprobante->id)
            ->pluck('pedido_id');

        if ($pedidoIds->isEmpty()) {
            $pedidoIds = collect([$comprobante->pedido_id]);
        }

        $mesa = DB::table('pedido as p')
            ->leftJoin('mesa as m', 'p.mesa_id', '=', 'm.id')
            ->whereIn('p.id', $pedidoIds)
            ->selectRaw('COALESCE(m.numero, CAST(p.mesa_liberada_numero AS VARCHAR)) as mesa_numero')
            ->first();

        $detalles = DB::table('detalle_pedido as dp')
            ->join('producto as pr', 'dp.producto_id', '=', 'pr.id')
            ->whereIn('dp.pedido_id', $pedidoIds)
            ->select('dp.*', 'pr.nombre as producto_nombre')
            ->get();

        $metodoPago = DB::table('metodo_pago')->where('comprobante_id', $comprobante->id)->first();

        $pdf = Pdf::loadView('caja.comprobante-pdf', compact(
            'comprobante',
            'serie',
            'restaurante',
            'mesa',
            'detalles',
            'metodoPago'
        ))->setPaper([0, 0, 226.77, 600], 'portrait');

        $nombreArchivo = "{$comprobante->tipo}-{$serie->serie}-" .
            str_pad($comprobante->numero_correlativo, 6, '0', STR_PAD_LEFT) . ".pdf";

        return $pdf->stream($nombreArchivo);
    }
}