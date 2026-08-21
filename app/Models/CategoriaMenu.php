<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaMenu extends Model
{
    protected $table = 'categoria_menu';
    public $timestamps = false;

    protected $fillable = [
        'restaurante_id',
        'nombre',
        'descripcion',
        'orden',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    public function restaurante()
    {
        return $this->belongsTo(Restaurante::class, 'restaurante_id');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'categoria_id');
    }

    // ─── Scopes ───────────────────────────────────────────────

    public function scopeActiva($query)
    {
        return $query->where('activa', true)->orderBy('orden');
    }
}
