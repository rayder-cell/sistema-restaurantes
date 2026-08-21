<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntradaCompra extends Model
{
    protected $table = 'entrada_compra';
    public $timestamps = false;

    protected $fillable = [
        'restaurante_id',
        'orden_id',
        'usuario_id',
        'numero',
        'fecha',
        'total',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'fecha' => 'date',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    public function restaurante()
    {
        return $this->belongsTo(Restaurante::class, 'restaurante_id');
    }

    public function orden()
    {
        return $this->belongsTo(OrdenCompra::class, 'orden_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleEntrada::class, 'entrada_id');
    }
}
