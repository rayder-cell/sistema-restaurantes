<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\CategoriaMenu;
use App\Models\Marca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    // ── Listar productos ───────────────────────────────────────────
    public function index(Request $request)
    {
        $restauranteId = session('restaurante_id');

        $categorias = CategoriaMenu::where('restaurante_id', $restauranteId)
            ->where('activa', true)
            ->orderBy('orden')
            ->get();

        $query = Producto::with('categoria')
            ->where('restaurante_id', $restauranteId);

        // Filtro por categoría
        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        // Filtro por búsqueda
        if ($request->filled('q')) {
            $query->where('nombre', 'ilike', '%' . $request->q . '%');
        }

        $productos = $query->orderBy('created_at', 'desc')->get();

        return view('menu.index', compact('productos', 'categorias'));
    }

    // ── Guardar nuevo producto ─────────────────────────────────────
    public function store(Request $request)
    {

        $restauranteId = session('restaurante_id');

        $request->validate([
            'nombre'       => 'required|string|max:150',
            'categoria_id' => 'required|exists:categoria_menu,id',
            'precio'       => 'required|numeric|min:0',
            'descripcion'  => 'nullable|string|max:500',
            'imagen'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'disponible'   => 'nullable|boolean',
        ], [
            'nombre.required'       => 'El nombre del producto es obligatorio.',
            'categoria_id.required' => 'Debes seleccionar una categoría.',
            'precio.required'       => 'El precio es obligatorio.',
            'precio.min'            => 'El precio no puede ser negativo.',
            'imagen.max'            => 'La imagen no puede superar 5MB.',
        ]);

        // Subir imagen
        $imagenUrl = null;
        if ($request->hasFile('imagen')) {
            $path      = $request->file('imagen')->store('productos', 'public');
            $imagenUrl = Storage::url($path);
        }

        Producto::create([
            'restaurante_id' => $restauranteId,
            'categoria_id'   => $request->categoria_id,
            'marca_id'       => null,
            'nombre'         => $request->nombre,
            'descripcion'    => $request->descripcion,
            'precio'         => $request->precio,
            'imagen_url'     => $imagenUrl,
            'disponible' => $request->input('disponible', '0') === '1',
        ]);

        return redirect()
            ->route('productos.index')
            ->with('success', "Producto \"{$request->nombre}\" agregado correctamente.");
    }

    // ── Actualizar producto ────────────────────────────────────────
    public function update(Request $request, Producto $producto)
    {
        $this->verificarRestaurante($producto);

        $request->validate([
            'nombre'       => 'required|string|max:150',
            'categoria_id' => 'required|exists:categoria_menu,id',
            'precio'       => 'required|numeric|min:0',
            'descripcion'  => 'nullable|string|max:500',
            'imagen'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        // Nueva imagen
        $imagenUrl = $producto->imagen_url;
        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior
            if ($producto->imagen_url) {
                $oldPath = str_replace('/storage/', '', $producto->imagen_url);
                Storage::disk('public')->delete($oldPath);
            }
            $path      = $request->file('imagen')->store('productos', 'public');
            $imagenUrl = Storage::url($path);
        }

        $producto->update([
            'categoria_id' => $request->categoria_id,
            'nombre'       => $request->nombre,
            'descripcion'  => $request->descripcion,
            'precio'       => $request->precio,
            'imagen_url'   => $imagenUrl,
            'disponible' => $request->input('disponible', '0') === '1',
        ]);

        return redirect()
            ->route('productos.index')
            ->with('success', "Producto \"{$producto->nombre}\" actualizado.");
    }

    // ── Toggle disponibilidad (AJAX) ───────────────────────────────
    public function toggleDisponible(Producto $producto)
    {
        $this->verificarRestaurante($producto);

        $producto->update(['disponible' => !$producto->disponible]);

        return response()->json([
            'success'    => true,
            'disponible' => $producto->disponible,
        ]);
    }

    // ── Eliminar producto ──────────────────────────────────────────
    public function destroy(Producto $producto)
    {
        $this->verificarRestaurante($producto);

        // Verificar si el producto tiene pedidos asociados
        $tienePedidos = \Illuminate\Support\Facades\DB::table('detalle_pedido')
            ->where('producto_id', $producto->id)
            ->exists();

        if ($tienePedidos) {
            return redirect()
                ->route('productos.index')
                ->with('error', "No puedes eliminar \"{$producto->nombre}\" porque tiene pedidos registrados. Puedes desactivarlo en su lugar.");
        }

        // Eliminar imagen del storage
        if ($producto->imagen_url) {
            $oldPath = str_replace('/storage/', '', $producto->imagen_url);
            \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
        }

        $nombre = $producto->nombre;
        $producto->delete();

        return redirect()
            ->route('productos.index')
            ->with('success', "Producto \"{$nombre}\" eliminado.");
    }

    // ── Helper: verificar que el producto pertenece al restaurante ─
    private function verificarRestaurante(Producto $producto)
    {
        if ($producto->restaurante_id !== session('restaurante_id')) {
            abort(403, 'No tienes permiso para modificar este producto.');
        }
    }
}
