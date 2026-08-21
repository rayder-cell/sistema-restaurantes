<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kardex extends Model
{
    protected $table = 'kardex';
    public $timestamps = false;

    protected $fillable = [
        'restaurante_id',
        'insumo_id',
        'tipo_movimiento',
        'referencia_doc',
        'cantidad_entrada',
        'cantidad_salida',
        'saldo_resultante',
        'costo_unitario',
    ];

    protected $casts = [
        'cantidad_entrada'  => 'decimal:3',
        'cantidad_salida'   => 'decimal:3',
        'saldo_resultante'  => 'decimal:3',
        'costo_unitario'    => 'decimal:4',
        'costo_total'       => 'decimal:2', // columna generada
        'fecha'             => 'datetime',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    public function restaurante()
    {
        return $this->belongsTo(Restaurante::class, 'restaurante_id');
    }

    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'insumo_id');
    }

    // ─── Scopes ───────────────────────────────────────────────

    public function scopeEntradas($query)
    {
        return $query->where('tipo_movimiento', 'entrada');
    }

    public function scopeSalidas($query)
    {
        return $query->where('tipo_movimiento', 'salida');
    }

    public function scopeDeInsumo($query, int $insumoId)
    {
        return $query->where('insumo_id', $insumoId)->orderBy('fecha');
    }
}
