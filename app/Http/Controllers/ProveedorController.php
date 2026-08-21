<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        $restauranteId = session('restaurante_id');

        $query = DB::table('proveedor')
            ->where('restaurante_id', $restauranteId);

        if ($request->filled('buscar')) {
            $query->where(function($q) use ($request) {
                $q->where('nombre', 'ilike', '%' . $request->buscar . '%')
                  ->orWhere('ruc', 'ilike', '%' . $request->buscar . '%');
            });
        }

        $proveedores = $query->orderBy('nombre')->paginate(15);

        return view('proveedores.index', compact('proveedores'));
    }

    public function create()
    {
        return view('proveedores.create');
    }

    public function store(Request $request)
    {
        $restauranteId = session('restaurante_id');

        $request->validate([
            'nombre'    => 'required|string|max:150',
            'ruc'       => 'nullable|string|max:11',
            'telefono'  => 'nullable|string|max:20',
            'email'     => 'nullable|email|max:100',
            'direccion' => 'nullable|string|max:255',
        ]);

        DB::table('proveedor')->insert([
            'restaurante_id' => $restauranteId,
            'nombre'         => $request->nombre,
            'ruc'            => $request->ruc,
            'telefono'       => $request->telefono,
            'email'          => $request->email,
            'direccion'      => $request->direccion,
            'activo'         => true,
            'created_at'     => now(),
        ]);

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor registrado correctamente.');
    }

    public function edit($id)
    {
        $restauranteId = session('restaurante_id');
        $proveedor = DB::table('proveedor')
            ->where('id', $id)
            ->where('restaurante_id', $restauranteId)
            ->first();
        if (!$proveedor) abort(404);
        return view('proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, $id)
    {
        $restauranteId = session('restaurante_id');

        $request->validate([
            'nombre'    => 'required|string|max:150',
            'ruc'       => 'nullable|string|max:11',
            'telefono'  => 'nullable|string|max:20',
            'email'     => 'nullable|email|max:100',
            'direccion' => 'nullable|string|max:255',
        ]);

        DB::table('proveedor')
            ->where('id', $id)
            ->where('restaurante_id', $restauranteId)
            ->update([
                'nombre'    => $request->nombre,
                'ruc'       => $request->ruc,
                'telefono'  => $request->telefono,
                'email'     => $request->email,
                'direccion' => $request->direccion,
                'activo'    => $request->boolean('activo'),
            ]);

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor actualizado correctamente.');
    }

    public function destroy($id)
    {
        $restauranteId = session('restaurante_id');
        DB::table('proveedor')
            ->where('id', $id)
            ->where('restaurante_id', $restauranteId)
            ->delete();
        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor eliminado correctamente.');
    }

    public function ordenes($id)
    {
        $restauranteId = session('restaurante_id');
        $proveedor = DB::table('proveedor')
            ->where('id', $id)
            ->where('restaurante_id', $restauranteId)
            ->first();
        if (!$proveedor) abort(404);

        $ordenes = DB::table('orden_compra')
            ->where('proveedor_id', $id)
            ->where('restaurante_id', $restauranteId)
            ->orderBy('fecha_emision', 'desc')
            ->get();

        return view('proveedores.ordenes', compact('proveedor', 'ordenes'));
    }
}
