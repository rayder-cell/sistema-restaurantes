<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\CulqiService;
use App\Services\NubeFactService;
use App\Services\ImpresoraTicketService;

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
                'p.cliente_id',
                'p.mesa_liberada_numero',
                'p.total',
                'p.created_at',
                'm.numero as mesa_numero',
                'c.nombre as cliente_nombre',
                'c.apellidos as cliente_apellidos'
            )
            ->orderBy('p.created_at')
            ->get();

        // Agrupar: pedidos con mesa activa se agrupan por mesa.
        // Pedidos liberados (mesa_id null) se agrupan por cliente, ya que todos
        // los pedidos de esa visita comparten el mismo cliente_id.
        $mesasACobrar = $pedidosPendientes
            ->groupBy(function ($p) {
                if ($p->mesa_id) return 'mesa-' . $p->mesa_id;
                if ($p->cliente_id) return 'cliente-' . $p->cliente_id;
                return 'huerfano-' . $p->id;
            })
            ->map(function ($pedidos, $clave) {
                $primero    = $pedidos->first();
                $esHuerfano = is_null($primero->mesa_id);

                $mesaNumero = $esHuerfano
                    ? $pedidos->pluck('mesa_liberada_numero')->filter()->first()
                    : $primero->mesa_numero;

                return (object) [
                    'clave'             => $clave,
                    'mesa_numero'       => $mesaNumero,
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

        $restaurante = DB::table('restaurante')->where('id', $restauranteId)->first();
        $culqiPublicKey = $restaurante->culqi_public_key ?? null;
        return view('caja.index', compact('caja', 'apertura', 'mesasACobrar', 'series', 'ventasHoy', 'culqiPublicKey'));
    }

    // ── Ver detalle combinado de una cuenta (mesa activa, cliente liberado, o huérfano) ──
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
        } elseif (str_starts_with($clave, 'cliente-')) {
            $clienteId = (int) str_replace('cliente-', '', $clave);

            $pedidos = DB::table('pedido as p')
                ->leftJoin('cliente as c', 'p.cliente_id', '=', 'c.id')
                ->where('p.cliente_id', $clienteId)
                ->where('p.restaurante_id', $restauranteId)
                ->whereNull('p.mesa_id')
                ->whereNotIn('p.estado', ['pagado', 'cancelado'])
                ->select('p.*', 'p.mesa_liberada_numero as mesa_numero', 'c.nombre as cliente_nombre', 'c.apellidos as cliente_apellidos')
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
        $mesaNumero = $pedidos->pluck('mesa_numero')->filter()->first();

        return response()->json([
            'mesa_numero'       => $mesaNumero,
            'liberada'          => is_null($primero->mesa_id),
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
            'cliente_nombre' => ['nullable', 'required_if:tipo,factura', 'string', 'max:150'],
            'cliente_ruc'    => ['nullable', 'required_if:tipo,factura', 'digits:11'],
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

        // ── Validar y armar los datos del cliente ANTES de cobrar con Culqi ──
        // (así nunca cobramos si el RUC de factura viene mal y luego falla SUNAT)
        if ($request->tipo === 'factura') {
            $ruc = trim((string) $request->cliente_ruc);

            if (!preg_match('/^\d{11}$/', $ruc)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El RUC debe tener exactamente 11 dígitos numéricos.',
                ], 422);
            }

            $clienteRuc    = $ruc;
            $clienteNombre = $request->cliente_nombre ?: 'Cliente';

            $datosCliente = [
                'cliente_tipo_documento'   => 6, // RUC
                'cliente_numero_documento' => $clienteRuc,
                'cliente_denominacion'     => $clienteNombre,
            ];
        } else {
            $clienteRuc    = null;
            $clienteNombre = $request->cliente_nombre ?: 'Cliente Varios';

            $datosCliente = [
                'cliente_tipo_documento'   => 1, // DNI
                'cliente_numero_documento' => '00000000',
                'cliente_denominacion'     => $clienteNombre,
            ];
        }

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

        // ── Armar los items para NubeFacT a partir del detalle real del pedido ──
        $detallesPedido = DB::table('detalle_pedido as dp')
            ->join('producto as pr', 'dp.producto_id', '=', 'pr.id')
            ->whereIn('dp.pedido_id', $pedidoIds)
            ->select('dp.*', 'pr.nombre as producto_nombre')
            ->get();

        $itemsNubeFact = $detallesPedido->map(function ($d) {
            return [
                'descripcion'     => $d->producto_nombre,
                'cantidad'        => (float) $d->cantidad,
                'valor_unitario'  => round($d->precio_unitario / 1.18, 2),
                'precio_unitario' => (float) $d->precio_unitario,
            ];
        })->toArray();

        // ── Determinar serie/número oficiales SUNAT (BBB1 boleta, FFF1 factura) ──
        $sunatSerie = $request->tipo === 'factura' ? 'FFF1' : 'BBB1';

        $ultimoNumeroSunat = DB::table('comprobante')
            ->where('restaurante_id', $restauranteId)
            ->where('sunat_serie', $sunatSerie)
            ->max('sunat_numero');

        // NOTA: iniciamos en 3 porque los números 1 y 2 ya se usaron
        // en pruebas directas contra NubeFacT (fuera de este flujo real).
        $siguienteNumeroSunat = max($ultimoNumeroSunat ?? 0, 2) + 1;

        // ── Enviar a NubeFacT ──
        $nubeFactService = app(NubeFactService::class);
        $tipoComprobanteSunat = $request->tipo === 'factura' ? 1 : 2;

        $resultadoSunat = $nubeFactService->emitirBoleta(array_merge($datosCliente, [
            'serie'  => $sunatSerie,
            'numero' => $siguienteNumeroSunat,
            'items'  => $itemsNubeFact,
        ]), $tipoComprobanteSunat);

        if (!$resultadoSunat['success']) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo emitir el comprobante ante SUNAT: ' . $resultadoSunat['message'],
            ], 422);
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
            $request,
            $restauranteId,
            $usuarioId,
            $pedidos,
            $pedidoIds,
            $mesaId,
            $serie,
            $subtotal,
            $igv,
            $total,
            $montoCobrado,
            $vuelto,
            $caja,
            $culqiChargeId,
            $sunatSerie,
            $siguienteNumeroSunat,
            $resultadoSunat,
            $clienteNombre,
            $clienteRuc,
            &$comprobanteIdRef
        ) {
            $comprobanteId = DB::table('comprobante')->insertGetId([
                'restaurante_id'     => $restauranteId,
                'pedido_id'          => $pedidos->first()->id,
                'serie_id'           => $serie->id,
                'tipo'               => $request->tipo,
                'numero_correlativo' => $serie->correlativo_actual,
                'cliente_nombre'     => $clienteNombre,
                'cliente_ruc'        => $clienteRuc,
                'subtotal'           => $subtotal,
                'igv'                => $igv,
                'total'              => $total,
                'anulado'            => false,
                'emitido_at'         => now(),
                'sunat_serie'        => $sunatSerie,
                'sunat_numero'       => $siguienteNumeroSunat,
                'sunat_enlace_pdf'   => $resultadoSunat['data']['enlace_del_pdf'] ?? null,
                'sunat_enlace_xml'   => $resultadoSunat['data']['enlace_del_xml'] ?? null,
                'sunat_estado'       => 'enviado',
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

        // ── Imprimir el ticket físico (NO bloquea la venta si falla) ──
        $mensajeImpresion = '';

        try {
            $impresoraService = app(ImpresoraTicketService::class);
            $restaurante = DB::table('restaurante')->where('id', $restauranteId)->first();

            $mesaNumero = $mesaId ? DB::table('mesa')->where('id', $mesaId)->value('numero') : null;

            $itemsImpresion = $detallesPedido->map(function ($d) {
                return [
                    'cantidad'        => (float) $d->cantidad,
                    'descripcion'     => $d->producto_nombre,
                    'precio_unitario' => (float) $d->precio_unitario,
                    'total'           => (float) $d->precio_unitario * (float) $d->cantidad,
                ];
            })->toArray();

            $resultadoImpresion = $impresoraService->imprimirTicketDoble([
                'restaurante'       => $restaurante->nombre ?? 'Restaurante',
                'direccion'         => $restaurante->direccion ?? '',
                'ruc'               => $restaurante->ruc ?? '',
                'tipo'              => $request->tipo,
                'numero_completo'   => $serie->serie . '-' . str_pad($serie->correlativo_actual, 6, '0', STR_PAD_LEFT),
                'fecha'             => now()->format('d/m/Y H:i A'),
                'mesa'              => $mesaNumero,
                'items'             => $itemsImpresion,
                'subtotal'          => $subtotal,
                'igv'               => $igv,
                'total'             => $total,
                'cliente_nombre'    => $clienteNombre,
                'cliente_documento' => $clienteRuc,
                'metodo_pago'       => $request->metodo,
                'monto_pagado'      => $montoCobrado,
                'vuelto'            => $vuelto,
            ]);

            if (!$resultadoImpresion['success']) {
                $mensajeImpresion = ' (Aviso: no se pudo imprimir el ticket físico — ' . $resultadoImpresion['message'] . ')';
            }
        } catch (\Throwable $e) {
            $mensajeImpresion = ' (Aviso: no se pudo imprimir el ticket físico)';
        }

        return response()->json([
            'success'          => true,
            'comprobante_id'   => $comprobanteIdRef,
            'vuelto'           => number_format($vuelto, 2),
            'enlace_pdf_sunat' => $resultadoSunat['data']['enlace_del_pdf'] ?? null,
            'message'          => ucfirst($request->tipo) . " emitida y enviada a SUNAT correctamente. Vuelto: S/ " . number_format($vuelto, 2) . $mensajeImpresion,
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
