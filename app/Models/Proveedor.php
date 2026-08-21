<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $table = 'proveedor';
    public $timestamps = false;

    protected $fillable = [
        'restaurante_id',
        'nombre',
        'ruc',
        'telefono',
        'email',
        'direccion',
        'activo',
    ];

    protected $casts = [
        'activo'     => 'boolean',
        'created_at' => 'datetime',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    public function restaurante()
    {
        return $this->belongsTo(Restaurante::class, 'restaurante_id');
    }

    public function ordenesCompra()
    {
        return $this->hasMany(OrdenCompra::class, 'proveedor_id');
    }

    // ─── Scopes ───────────────────────────────────────────────

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }
}
