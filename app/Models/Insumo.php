<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Insumo extends Model
{
    protected $table = 'insumo';
    public $timestamps = false;

    protected $fillable = [
        'restaurante_id',
        'nombre',
        'unidad_medida',
        'stock_actual',
        'stock_minimo',
        'categoria',
        'activo',
    ];

    protected $casts = [
        'stock_actual' => 'decimal:3',
        'stock_minimo' => 'decimal:3',
        'activo'       => 'boolean',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    public function restaurante()
    {
        return $this->belongsTo(Restaurante::class, 'restaurante_id');
    }

    public function detallesOrden()
    {
        return $this->hasMany(DetalleOrden::class, 'insumo_id');
    }

    public function detallesEntrada()
    {
        return $this->hasMany(DetalleEntrada::class, 'insumo_id');
    }

    public function kardex()
    {
        return $this->hasMany(Kardex::class, 'insumo_id');
    }

    // ─── Helpers ──────────────────────────────────────────────

    public function stockCritico(): bool
    {
        return $this->stock_actual <= $this->stock_minimo;
    }

    // ─── Scopes ───────────────────────────────────────────────

    public function scopeStockCritico($query)
    {
        return $query->whereColumn('stock_actual', '<=', 'stock_minimo');
    }

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }
}
