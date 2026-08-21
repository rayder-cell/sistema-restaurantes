<?php

namespace App\Http\Controllers;

use App\Models\CategoriaMenu;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    // ── Listar categorías ──────────────────────────────────────────
    public function index()
    {
        $restauranteId = session('restaurante_id');

        $categorias = CategoriaMenu::withCount('productos')
            ->where('restaurante_id', $restauranteId)
            ->orderBy('orden')
            ->get();

        return view('menu.categorias', compact('categorias'));
    }

    // ── Guardar nueva categoría ────────────────────────────────────
    public function store(Request $request)
    {
        $restauranteId = session('restaurante_id');

        $request->validate([
            'nombre'      => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
            'orden'       => 'nullable|integer|min:0',
        ]);

        // Verificar que no exista el mismo nombre en este restaurante
        $existe = CategoriaMenu::where('restaurante_id', $restauranteId)
            ->where('nombre', $request->nombre)
            ->exists();

        if ($existe) {
            return back()->withErrors(['nombre' => 'Ya existe una categoría con ese nombre.']);
        }

        CategoriaMenu::create([
            'restaurante_id' => $restauranteId,
            'nombre'         => $request->nombre,
            'descripcion'    => $request->descripcion,
            'orden'          => $request->orden ?? 0,
            'activa'         => true,
        ]);

        return redirect()
            ->route('categorias.index')
            ->with('success', "Categoría \"{$request->nombre}\" creada.");
    }

    // ── Actualizar categoría ───────────────────────────────────────
    public function update(Request $request, CategoriaMenu $categoria)
    {
        $this->verificarRestaurante($categoria);

        $request->validate([
            'nombre'      => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
            'orden'       => 'nullable|integer|min:0',
        ]);

        $categoria->update([
            'nombre'      => $request->nombre,
            'descripcion' => $request->descripcion,
            'orden'       => $request->orden ?? $categoria->orden,
            'activa'      => $request->has('activa') ? true : false,
        ]);

        return redirect()
            ->route('categorias.index')
            ->with('success', "Categoría \"{$categoria->nombre}\" actualizada.");
    }

    // ── Eliminar categoría ─────────────────────────────────────────
    public function destroy(CategoriaMenu $categoria)
    {
        $this->verificarRestaurante($categoria);

        if ($categoria->productos()->count() > 0) {
            return back()->with('error', 'No puedes eliminar una categoría que tiene productos. Elimina o reasigna los productos primero.');
        }

        $nombre = $categoria->nombre;
        $categoria->delete();

        return redirect()
            ->route('categorias.index')
            ->with('success', "Categoría \"{$nombre}\" eliminada.");
    }

    // ── Helper ─────────────────────────────────────────────────────
    private function verificarRestaurante(CategoriaMenu $categoria)
    {
        if ($categoria->restaurante_id !== session('restaurante_id')) {
            abort(403);
        }
    }
}
