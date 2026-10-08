<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class MesaController extends Controller
{
    public function index()
    {
        $restauranteId = session('restaurante_id');
        $usuarioId     = session('usuario_id');
        $rol           = session('usuario_rol');

        $verTodo = in_array($rol, ['propietario', 'administrador']);

        $mesas = Mesa::where('restaurante_id', $restauranteId)
            ->orderBy('numero')
            ->get();

        // Dueño real de cada mesa ocupada (el mesero que tomó el pedido activo),
        // independientemente de si ya se entregaron todos los platos o no.
        $duenoPorMesa = Pedido::where('restaurante_id', $restauranteId)
            ->whereNotIn('estado', ['pagado', 'cancelado'])
            ->whereNotNull('mesa_id')
            ->get(['mesa_id', 'usuario_id'])
            ->keyBy('mesa_id') // si hay varios pedidos en la misma mesa, toma cualquiera (todos deben ser del mismo mesero)
            ->map(fn($p) => $p->usuario_id);

        // Detalle de platos aún pendientes de entregar, por mesa
        $pedidosActivosQuery = DB::table('pedido as p')
            ->join('detalle_pedido as dp', 'p.id', '=', 'dp.pedido_id')
            ->join('producto as pr', 'dp.producto_id', '=', 'pr.id')
            ->whereIn('p.estado', ['pendiente', 'en_preparacion', 'listo'])
            ->where('p.restaurante_id', $restauranteId)
            ->whereNotNull('p.mesa_id')
            ->select(
                'p.mesa_id',
                'p.id as pedido_id',
                'p.usuario_id as mesero_id',
                'dp.id as detalle_id',
                'pr.nombre as producto_nombre',
                'dp.cantidad',
                'dp.estado as detalle_estado',
                'dp.observacion'
            )
            ->whereIn('dp.estado', ['pendiente', 'en_preparacion', 'listo']);

        if (!$verTodo) {
            $pedidosActivosQuery->where('p.usuario_id', $usuarioId);
        }

        $pedidosActivos = $pedidosActivosQuery->get()->groupBy('mesa_id');

        $resumen = [
            'libres'     => $mesas->where('estado', 'libre')->count(),
            'ocupadas'   => $mesas->where('estado', 'ocupada')->count(),
            'reservadas' => $mesas->where('estado', 'reservada')->count(),
            'total'      => $mesas->count(),
        ];

        return view('mesas.index', compact('mesas', 'resumen', 'pedidosActivos', 'duenoPorMesa', 'verTodo', 'usuarioId'));
    }

    public function store(Request $request)
    {
        $restauranteId = session('restaurante_id');

        $request->validate([
            'numero'    => 'required|integer|min:1',
            'capacidad' => 'required|integer|min:1|max:20',
        ], [
            'numero.required'    => 'El número de mesa es obligatorio.',
            'numero.integer'     => 'El número debe ser entero.',
            'capacidad.required' => 'La capacidad es obligatoria.',
            'capacidad.max'      => 'La capacidad máxima es 20 personas.',
        ]);

        // Verificar que no exista el número en el mismo restaurante
        $existe = Mesa::where('restaurante_id', $restauranteId)
            ->where('numero', $request->numero)
            ->exists();

        if ($existe) {
            return back()->with('error', "Ya existe la Mesa {$request->numero} en tu restaurante.");
        }

        Mesa::create([
            'restaurante_id' => $restauranteId,
            'numero'         => $request->numero,
            'capacidad'      => $request->capacidad,
            'estado'         => 'libre',
        ]);

        return back()->with('success', "Mesa {$request->numero} creada correctamente.");
    }

    public function update(Request $request, Mesa $mesa)
    {
        $this->verificarRestaurante($mesa);

        $request->validate([
            'numero'    => 'required|integer|min:1',
            'capacidad' => 'required|integer|min:1|max:20',
        ]);

        // Verificar duplicado excluyendo la mesa actual
        $existe = Mesa::where('restaurante_id', session('restaurante_id'))
            ->where('numero', $request->numero)
            ->where('id', '!=', $mesa->id)
            ->exists();

        if ($existe) {
            return back()->with('error', "Ya existe la Mesa {$request->numero}.");
        }

        $mesa->update([
            'numero'    => $request->numero,
            'capacidad' => $request->capacidad,
        ]);

        return back()->with('success', "Mesa {$mesa->numero} actualizada.");
    }

    public function cambiarEstado(Request $request, $id)
    {
        try {
            $mesa = Mesa::findOrFail($id);
            $this->verificarRestaurante($mesa);

            $request->validate([
                'estado' => 'required|in:libre,ocupada,reservada',
            ]);

            if ($request->estado === 'libre') {
                $pedidoActivo = Pedido::where('mesa_id', $mesa->id)
                    ->whereNotIn('estado', ['pagado', 'cancelado'])
                    ->exists();

                if ($pedidoActivo) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No puedes liberar una mesa con pedido activo.',
                    ], 422);
                }
            }

            $mesa->update(['estado' => $request->estado]);

            return response()->json([
                'success' => true,
                'estado'  => $mesa->estado,
                'message' => "Mesa {$mesa->numero} → " . ucfirst($request->estado),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Mesa $mesa)
    {
        $this->verificarRestaurante($mesa);

        // No se puede eliminar si tiene pedidos activos
        $pedidoActivo = Pedido::where('mesa_id', $mesa->id)
            ->whereNotIn('estado', ['pagado', 'cancelado'])
            ->exists();

        if ($pedidoActivo) {
            return back()->with('error', "No puedes eliminar la Mesa {$mesa->numero} porque tiene pedidos activos.");
        }

        $numero = $mesa->numero;
        $mesa->delete();

        return back()->with('success', "Mesa {$numero} eliminada.");
    }

    private function verificarRestaurante(Mesa $mesa)
    {
        if ($mesa->restaurante_id !== session('restaurante_id')) {
            abort(403);
        }
    }

    // ── Liberar mesa manualmente (mesero, sin cobrar aún) ───────────
    public function liberarManual($id)
    {
        $mesa = Mesa::findOrFail($id);
        $this->verificarRestaurante($mesa);

        $usuarioId = session('usuario_id');

        $pedidosActivos = Pedido::where('mesa_id', $mesa->id)
            ->whereNotIn('estado', ['pagado', 'cancelado'])
            ->get();

        if ($pedidosActivos->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Esta mesa no tiene pedido activo.',
            ], 422);
        }

        // Solo el mesero que registró el pedido puede liberar manualmente
        $dueno = $pedidosActivos->first()->usuario_id;
        if ($dueno !== $usuarioId) {
            return response()->json([
                'success' => false,
                'message' => 'Solo el mesero que registró el pedido puede liberar esta mesa.',
            ], 403);
        }

        DB::transaction(function () use ($pedidosActivos, $mesa) {
            foreach ($pedidosActivos as $pedido) {
                $pedido->update([
                    'mesa_liberada_numero' => $mesa->numero,
                    'mesa_id'              => null, // se desvincula de la mesa física
                ]);
            }
            $mesa->update(['estado' => 'libre']);
        });

        return response()->json([
            'success' => true,
            'message' => "Mesa {$mesa->numero} liberada. El cobro pendiente sigue disponible en Caja.",
        ]);
    }

    // ── Datos actualizados de mesas (polling AJAX, sin recargar página) ──
    public function actualizar()
    {
        $restauranteId = session('restaurante_id');
        $usuarioId     = session('usuario_id');
        $rol           = session('usuario_rol');

        $verTodo = in_array($rol, ['propietario', 'administrador']);

        $mesas = Mesa::where('restaurante_id', $restauranteId)
            ->orderBy('numero')
            ->get();

        $duenoPorMesa = Pedido::where('restaurante_id', $restauranteId)
            ->whereNotIn('estado', ['pagado', 'cancelado'])
            ->whereNotNull('mesa_id')
            ->get(['mesa_id', 'usuario_id'])
            ->keyBy('mesa_id')
            ->map(fn($p) => $p->usuario_id);

        $pedidosActivosQuery = DB::table('pedido as p')
            ->join('detalle_pedido as dp', 'p.id', '=', 'dp.pedido_id')
            ->join('producto as pr', 'dp.producto_id', '=', 'pr.id')
            ->whereIn('p.estado', ['pendiente', 'en_preparacion', 'listo'])
            ->where('p.restaurante_id', $restauranteId)
            ->whereNotNull('p.mesa_id')
            ->select('p.mesa_id', 'dp.id as detalle_id', 'pr.nombre as producto_nombre', 'dp.cantidad', 'dp.observacion')
            ->whereIn('dp.estado', ['pendiente', 'en_preparacion', 'listo']);

        if (!$verTodo) {
            $pedidosActivosQuery->where('p.usuario_id', $usuarioId);
        }

        $pedidosActivos = $pedidosActivosQuery->get()->groupBy('mesa_id');

        $data = $mesas->map(function ($mesa) use ($duenoPorMesa, $pedidosActivos, $verTodo, $usuarioId) {
            $dueno = $duenoPorMesa[$mesa->id] ?? null;
            $esMia = $verTodo || $dueno === $usuarioId;
            $detalles = $pedidosActivos[$mesa->id] ?? collect();

            return [
                'id'         => $mesa->id,
                'numero'     => $mesa->numero,
                'capacidad'  => $mesa->capacidad,
                'estado'     => $mesa->estado,
                'qr_token'   => $mesa->qr_token,
                'esMia'      => $esMia,
                'detalles'   => $detalles->map(fn($d) => [
                    'detalle_id'      => $d->detalle_id,
                    'producto_nombre' => $d->producto_nombre,
                    'cantidad'        => $d->cantidad,
                    'observacion'     => $d->observacion,
                ])->values(),
            ];
        });

        $resumen = [
            'libres'     => $mesas->where('estado', 'libre')->count(),
            'ocupadas'   => $mesas->where('estado', 'ocupada')->count(),
            'reservadas' => $mesas->where('estado', 'reservada')->count(),
            'total'      => $mesas->count(),
        ];

        return response()->json(['mesas' => $data, 'resumen' => $resumen]);
    }

    // ── Generar imagen PNG del código QR de la mesa (apunta al menú público) ──
    public function qrImagen(Mesa $mesa)
    {
        $this->verificarRestaurante($mesa);

        $url = url("/menu/{$mesa->qr_token}");

        $imagen = QrCode::format('svg')
            ->size(400)
            ->margin(1)
            ->errorCorrection('H')
            ->generate($url);

        return response($imagen)->header('Content-Type', 'image/svg+xml');
    }
}