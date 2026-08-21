<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InsumoController extends Controller
{
    public function index(Request $request)
    {
        $restauranteId = session('restaurante_id');

        $query = DB::table('insumo')
            ->where('restaurante_id', $restauranteId);

        if ($request->filled('buscar')) {
            $query->where('nombre', 'ilike', '%' . $request->buscar . '%');
        }
        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }
        if ($request->filled('stock')) {
            if ($request->stock === 'bajo') {
                $query->whereRaw('stock_actual <= stock_minimo');
            } elseif ($request->stock === 'ok') {
                $query->whereRaw('stock_actual > stock_minimo');
            }
        }

        $insumos = $query->orderBy('nombre')->paginate(20);

        // Stats
        $stats = DB::table('insumo')
            ->where('restaurante_id', $restauranteId)
            ->selectRaw("
                count(*) as total,
                sum(case when stock_actual <= stock_minimo then 1 else 0 end) as bajo_stock,
                sum(case when activo = true then 1 else 0 end) as activos
            ")->first();

        $categorias = DB::table('insumo')
            ->where('restaurante_id', $restauranteId)
            ->distinct()->pluck('categoria')->filter()->values();

        return view('insumos.index', compact('insumos', 'stats', 'categorias'));
    }

    public function create()
    {
        return view('insumos.create');
    }

    public function store(Request $request)
    {
        $restauranteId = session('restaurante_id');

        $request->validate([
            'nombre'        => 'required|string|max:150',
            'unidad_medida' => 'required|string|max:20',
            'stock_actual'  => 'required|numeric|min:0',
            'stock_minimo'  => 'required|numeric|min:0',
            'categoria'     => 'nullable|string|max:100',
        ]);

        DB::table('insumo')->insert([
            'restaurante_id' => $restauranteId,
            'nombre'         => $request->nombre,
            'unidad_medida'  => $request->unidad_medida,
            'stock_actual'   => $request->stock_actual,
            'stock_minimo'   => $request->stock_minimo,
            'categoria'      => $request->categoria,
            'activo'         => true,
        ]);

        return redirect()->route('insumos.index')
            ->with('success', 'Insumo registrado correctamente.');
    }

    public function edit($id)
    {
        $restauranteId = session('restaurante_id');
        $insumo = DB::table('insumo')
            ->where('id', $id)
            ->where('restaurante_id', $restauranteId)
            ->first();
        if (!$insumo) abort(404);
        return view('insumos.edit', compact('insumo'));
    }

    public function update(Request $request, $id)
    {
        $restauranteId = session('restaurante_id');

        $request->validate([
            'nombre'        => 'required|string|max:150',
            'unidad_medida' => 'required|string|max:20',
            'stock_actual'  => 'required|numeric|min:0',
            'stock_minimo'  => 'required|numeric|min:0',
            'categoria'     => 'nullable|string|max:100',
        ]);

        DB::table('insumo')
            ->where('id', $id)
            ->where('restaurante_id', $restauranteId)
            ->update([
                'nombre'        => $request->nombre,
                'unidad_medida' => $request->unidad_medida,
                'stock_actual'  => $request->stock_actual,
                'stock_minimo'  => $request->stock_minimo,
                'categoria'     => $request->categoria,
                'activo'        => $request->boolean('activo'),
            ]);

        return redirect()->route('insumos.index')
            ->with('success', 'Insumo actualizado correctamente.');
    }

    public function destroy($id)
    {
        $restauranteId = session('restaurante_id');
        DB::table('insumo')
            ->where('id', $id)
            ->where('restaurante_id', $restauranteId)
            ->delete();
        return redirect()->route('insumos.index')
            ->with('success', 'Insumo eliminado correctamente.');
    }

    public function kardex($id)
    {
        $restauranteId = session('restaurante_id');

        $insumo = DB::table('insumo')
            ->where('id', $id)
            ->where('restaurante_id', $restauranteId)
            ->first();
        if (!$insumo) abort(404);

        $movimientos = DB::table('kardex')
            ->where('insumo_id', $id)
            ->where('restaurante_id', $restauranteId)
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('insumos.kardex', compact('insumo', 'movimientos'));
    }
}
