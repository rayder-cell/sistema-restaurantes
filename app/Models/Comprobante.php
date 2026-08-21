<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comprobante extends Model
{
    protected $table = 'comprobante';
    public $timestamps = false;

    protected $fillable = [
        'restaurante_id',
        'pedido_id',
        'serie_id',
        'tipo',
        'numero_correlativo',
        'cliente_nombre',
        'cliente_ruc',
        'subtotal',
        'igv',
        'total',
        'anulado',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'igv'      => 'decimal:2',
        'total'    => 'decimal:2',
        'anulado'  => 'boolean',
        'emitido_at' => 'datetime',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    public function restaurante()
    {
        return $this->belongsTo(Restaurante::class, 'restaurante_id');
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function serie()
    {
        return $this->belongsTo(SerieComprobante::class, 'serie_id');
    }

    public function metodosPago()
    {
        return $this->hasMany(MetodoPago::class, 'comprobante_id');
    }

    // ─── Helpers ──────────────────────────────────────────────

    public function numeroCompleto(): string
    {
        return $this->serie->serie . '-' . str_pad($this->numero_correlativo, 8, '0', STR_PAD_LEFT);
    }
}
