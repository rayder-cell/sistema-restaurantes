<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleOrden extends Model
{
    protected $table = 'detalle_orden';
    public $timestamps = false;

    protected $fillable = [
        'orden_id',
        'insumo_id',
        'cantidad_pedida',
        'precio_unitario',
    ];

    protected $casts = [
        'cantidad_pedida' => 'decimal:3',
        'precio_unitario' => 'decimal:2',
        'subtotal'        => 'decimal:2', // columna generada
    ];

    // ─── Relaciones ───────────────────────────────────────────

    public function orden()
    {
        return $this->belongsTo(OrdenCompra::class, 'orden_id');
    }

    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'insumo_id');
    }
}
