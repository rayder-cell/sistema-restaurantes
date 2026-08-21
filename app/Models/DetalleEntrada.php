<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleEntrada extends Model
{
    protected $table = 'detalle_entrada';
    public $timestamps = false;

    protected $fillable = [
        'entrada_id',
        'insumo_id',
        'cantidad_recibida',
        'precio_unitario',
    ];

    protected $casts = [
        'cantidad_recibida' => 'decimal:3',
        'precio_unitario'   => 'decimal:2',
        'subtotal'          => 'decimal:2', // columna generada
    ];

    // ─── Relaciones ───────────────────────────────────────────

    public function entrada()
    {
        return $this->belongsTo(EntradaCompra::class, 'entrada_id');
    }

    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'insumo_id');
    }
}
