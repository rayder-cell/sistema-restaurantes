<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'producto';

    protected $fillable = [
        'restaurante_id',
        'categoria_id',
        'marca_id',
        'nombre',
        'descripcion',
        'precio',
        'imagen_url',
        'disponible',
    ];

    protected $casts = [
        'precio'     => 'decimal:2',
        'disponible' => 'boolean',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    public function restaurante()
    {
        return $this->belongsTo(Restaurante::class, 'restaurante_id');
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaMenu::class, 'categoria_id');
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'marca_id');
    }

    public function detallesPedido()
    {
        return $this->hasMany(DetallePedido::class, 'producto_id');
    }

    // ─── Scopes ───────────────────────────────────────────────

    public function scopeDisponible($query)
    {
        return $query->where('disponible', true);
    }

    public function scopeDeRestaurante($query, int $restauranteId)
    {
        return $query->where('restaurante_id', $restauranteId);
    }
}
