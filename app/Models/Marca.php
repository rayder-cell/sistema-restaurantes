<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    protected $table = 'marca';
    public $timestamps = false;

    protected $fillable = [
        'restaurante_id',
        'nombre',
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
        return $this->hasMany(Producto::class, 'marca_id');
    }
}
