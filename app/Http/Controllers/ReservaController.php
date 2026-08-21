<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservaController extends Controller
{
    public function index(Request $request)
    {
        $restauranteId = session('restaurante_id');

        $query = DB::table('reserva as r')
            ->leftJoin('mesa as m', 'r.mesa_id', '=', 'm.id')
            ->where('r.restaurante_id', $restauranteId)
            ->select('r.*', 'm.numero as mesa_numero');

        if ($request->filled('fecha')) {
            $query->whereDate('r.fecha', $request->fecha);
        }
        if ($request->filled('estado')) {
            $query->where('r.estado', $request->estado);
        }
        if ($request->filled('buscar')) {
            $query->where(function ($q) use ($request) {
                $q->where('r.cliente_nombre', 'ilike', '%' . $request->buscar . '%')
                    ->orWhere('r.cliente_telefono', 'ilike', '%' . $request->buscar . '%');
            });
        }

        $reservas = $query->orderBy('r.fecha', 'desc')->orderBy('r.hora', 'desc')->paginate(15);

        $hoy = now()->toDateString();
        $statsHoy = DB::table('reserva')
            ->where('restaurante_id', $restauranteId)
            ->whereDate('fecha', $hoy)
            ->selectRaw("
                count(*) as total,
                sum(case when estado = 'confirmada' then 1 else 0 end) as confirmadas,
                sum(case when estado = 'pendiente' then 1 else 0 end) as pendientes,
                sum(case when estado = 'cancelada' then 1 else 0 end) as canceladas
            ")->first();

        $mesas = DB::table('mesa')
            ->where('restaurante_id', $restauranteId)
            ->orderBy('numero')
            ->get();

        return view('reservas.index', compact('reservas', 'statsHoy', 'mesas'));
    }

    public function create()
    {
        $restauranteId = session('restaurante_id');
        $mesas = DB::table('mesa')
            ->where('restaurante_id', $restauranteId)
            ->orderBy('numero')
            ->get();
        return view('reservas.create', compact('mesas'));
    }

    public function store(Request $request)
    {
        $restauranteId = session('restaurante_id');

        $request->validate([
            'cliente_nombre'   => 'required|string|min:3|max:100',
            'cliente_telefono' => 'required|string|min:7|max:20|regex:/^[0-9]+$/',
            'cliente_email'    => 'nullable|email:rfc|max:100',
            'num_personas'     => 'required|integer|min:1|max:50',
            'fecha'            => 'required|date|after_or_equal:today',
            'hora'             => 'required',
            'mesa_id'          => 'nullable|integer|exists:mesa,id',
            'observacion'      => 'nullable|string|max:500',
            'precio_base'      => 'nullable|numeric|min:0|max:9999',
        ], [
            'cliente_nombre.min'     => 'El nombre debe tener al menos 3 caracteres.',
            'cliente_telefono.min'   => 'El teléfono debe tener al menos 7 dígitos.',
            'cliente_telefono.regex' => 'El teléfono solo debe contener números.',
            'cliente_email.email'    => 'Ingresa un correo electrónico válido.',
            'fecha.after_or_equal'   => 'La fecha debe ser hoy o una fecha futura.',
            'mesa_id.exists'         => 'La mesa seleccionada no existe.',
        ]);

        if ($request->filled('mesa_id')) {
            $conflicto = DB::table('reserva')
                ->where('restaurante_id', $restauranteId)
                ->where('mesa_id', $request->mesa_id)
                ->whereDate('fecha', $request->fecha)
                ->where('hora', $request->hora)
                ->whereNotIn('estado', ['cancelada'])
                ->exists();

            if ($conflicto) {
                return back()->withErrors(['mesa_id' => 'Esta mesa ya tiene una reserva en esa fecha y hora.'])->withInput();
            }
        }

        DB::table('reserva')->insert([
            'restaurante_id'   => $restauranteId,
            'mesa_id'          => $request->mesa_id ?: null,
            'cliente_nombre'   => $request->cliente_nombre,
            'cliente_telefono' => $request->cliente_telefono,
            'cliente_email'    => $request->cliente_email,
            'num_personas'     => $request->num_personas,
            'fecha'            => $request->fecha,
            'hora'             => $request->hora,
            'precio_base'      => $request->precio_base ?? 0,
            'estado'           => 'pendiente',
            'observacion'      => $request->observacion,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return redirect()->route('reservas.index')
            ->with('success', 'Reserva registrada correctamente.');
    }

    public function show($id)
    {
        $restauranteId = session('restaurante_id');

        $reserva = DB::table('reserva as r')
            ->leftJoin('mesa as m', 'r.mesa_id', '=', 'm.id')
            ->where('r.id', $id)
            ->where('r.restaurante_id', $restauranteId)
            ->select('r.*', 'm.numero as mesa_numero', 'm.capacidad as mesa_capacidad')
            ->first();

        if (!$reserva) abort(404);

        return view('reservas.show', compact('reserva'));
    }

    public function edit($id)
    {
        $restauranteId = session('restaurante_id');

        $reserva = DB::table('reserva')
            ->where('id', $id)
            ->where('restaurante_id', $restauranteId)
            ->first();

        if (!$reserva) abort(404);

        $mesas = DB::table('mesa')
            ->where('restaurante_id', $restauranteId)
            ->orderBy('numero')
            ->get();

        return view('reservas.edit', compact('reserva', 'mesas'));
    }

    public function update(Request $request, $id)
    {
        $restauranteId = session('restaurante_id');

        $request->validate([
            'cliente_nombre'   => 'required|string|min:3|max:100',
            'cliente_telefono' => 'required|string|min:7|max:20|regex:/^[0-9]+$/',
            'cliente_email'    => 'nullable|email:rfc|max:100',
            'num_personas'     => 'required|integer|min:1|max:50',
            'fecha'            => 'required|date',
            'hora'             => 'required',
            'mesa_id'          => 'nullable|integer|exists:mesa,id',
            'estado'           => 'required|in:pendiente,confirmada,cancelada,completada',
            'observacion'      => 'nullable|string|max:500',
            'precio_base'      => 'nullable|numeric|min:0|max:9999',
        ], [
            'cliente_nombre.min'     => 'El nombre debe tener al menos 3 caracteres.',
            'cliente_telefono.min'   => 'El teléfono debe tener al menos 7 dígitos.',
            'cliente_telefono.regex' => 'El teléfono solo debe contener números.',
            'cliente_email.email'    => 'Ingresa un correo electrónico válido.',
            'mesa_id.exists'         => 'La mesa seleccionada no existe.',
        ]);

        // Verificar conflicto de mesa al editar (excluyendo la propia reserva)
        if ($request->filled('mesa_id')) {
            $conflicto = DB::table('reserva')
                ->where('restaurante_id', $restauranteId)
                ->where('mesa_id', $request->mesa_id)
                ->whereDate('fecha', $request->fecha)
                ->where('hora', $request->hora)
                ->whereNotIn('estado', ['cancelada'])
                ->where('id', '!=', $id)
                ->exists();

            if ($conflicto) {
                return back()->withErrors(['mesa_id' => 'Esta mesa ya tiene otra reserva en esa fecha y hora.'])->withInput();
            }
        }

        DB::table('reserva')
            ->where('id', $id)
            ->where('restaurante_id', $restauranteId)
            ->update([
                'mesa_id'          => $request->mesa_id ?: null,
                'cliente_nombre'   => $request->cliente_nombre,
                'cliente_telefono' => $request->cliente_telefono,
                'cliente_email'    => $request->cliente_email,
                'num_personas'     => $request->num_personas,
                'fecha'            => $request->fecha,
                'hora'             => $request->hora,
                'estado'           => $request->estado,
                'precio_base'      => $request->precio_base ?? 0,
                'observacion'      => $request->observacion,
                'confirmado_por'   => $request->estado === 'confirmada' ? session('usuario_id') : null,
                'updated_at'       => now(),
            ]);

        return redirect()->route('reservas.index')
            ->with('success', 'Reserva actualizada correctamente.');
    }

    public function destroy($id)
    {
        $restauranteId = session('restaurante_id');

        DB::table('reserva')
            ->where('id', $id)
            ->where('restaurante_id', $restauranteId)
            ->delete();

        return redirect()->route('reservas.index')
            ->with('success', 'Reserva eliminada correctamente.');
    }

    public function cambiarEstado(Request $request, $id)
    {
        $restauranteId = session('restaurante_id');

        $request->validate(['estado' => 'required|in:pendiente,confirmada,cancelada,completada']);

        DB::table('reserva')
            ->where('id', $id)
            ->where('restaurante_id', $restauranteId)
            ->update([
                'estado'         => $request->estado,
                'confirmado_por' => in_array($request->estado, ['confirmada', 'completada'])
                    ? session('usuario_id') : null,
                'updated_at'     => now(),
            ]);

        $estados = [
            'confirmada' => 'confirmada',
            'cancelada'  => 'cancelada',
            'completada' => 'completada',
            'pendiente'  => 'marcada como pendiente',
        ];

        return back()->with('success', "Reserva {$estados[$request->estado]} correctamente.");
    }

    public function reservaPublica($token)
    {
        $mesa = DB::table('mesa')->where('qr_token', $token)->first();
        if (!$mesa) abort(404);
        $restaurante = DB::table('restaurante')->where('id', $mesa->restaurante_id)->first();
        return view('reservas.publica', compact('mesa', 'restaurante'));
    }

    public function reservaPublicaStore(Request $request, $token)
    {
        $mesa = DB::table('mesa')->where('qr_token', $token)->first();
        if (!$mesa) abort(404);

        $request->validate([
            'cliente_nombre'   => 'required|string|min:3|max:100',
            'cliente_telefono' => 'required|string|min:7|max:20|regex:/^[0-9]+$/',
            'cliente_email'    => 'nullable|email:rfc|max:100',
            'num_personas'     => 'required|integer|min:1|max:' . max(1, $mesa->capacidad),
            'fecha'            => 'required|date|after_or_equal:today',
            'hora'             => 'required',
            'observacion'      => 'nullable|string|max:500',
        ], [
            'cliente_nombre.min'     => 'El nombre debe tener al menos 3 caracteres.',
            'cliente_telefono.min'   => 'El teléfono debe tener al menos 7 dígitos.',
            'cliente_telefono.regex' => 'El teléfono solo debe contener números.',
            'cliente_email.email'    => 'Ingresa un correo electrónico válido.',
            'num_personas.max'       => 'La mesa solo tiene capacidad para ' . $mesa->capacidad . ' personas.',
            'fecha.after_or_equal'   => 'La fecha debe ser hoy o una fecha futura.',
        ]);

        DB::table('reserva')->insert([
            'restaurante_id'   => $mesa->restaurante_id,
            'mesa_id'          => $mesa->id,
            'cliente_nombre'   => $request->cliente_nombre,
            'cliente_telefono' => $request->cliente_telefono,
            'cliente_email'    => $request->cliente_email,
            'num_personas'     => $request->num_personas,
            'fecha'            => $request->fecha,
            'hora'             => $request->hora,
            'precio_base'      => 0,
            'estado'           => 'pendiente',
            'observacion'      => $request->observacion,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return redirect()->route('reserva.publica.confirmacion');
    }

    public function confirmacion()
    {
        return view('reservas.confirmacion');
    }
}