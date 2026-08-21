<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenCompra extends Model
{
    protected $table = 'orden_compra';
    public $timestamps = false;

    protected $fillable = [
        'restaurante_id',
        'proveedor_id',
        'usuario_id',
        'numero',
        'estado',
        'total',
        'fecha_emision',
        'fecha_entrega_est',
    ];

    protected $casts = [
        'total'             => 'decimal:2',
        'fecha_emision'     => 'date',
        'fecha_entrega_est' => 'date',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    public function restaurante()
    {
        return $this->belongsTo(Restaurante::class, 'restaurante_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleOrden::class, 'orden_id');
    }

    public function entradas()
    {
        return $this->hasMany(EntradaCompra::class, 'orden_id');
    }

    // ─── Helpers ──────────────────────────────────────────────

    public function esBorrador(): bool  { return $this->estado === 'borrador'; }
    public function estaEnviada(): bool { return $this->estado === 'enviada'; }
    public function estaRecibida(): bool { return $this->estado === 'recibida'; }
    public function estaCancelada(): bool { return $this->estado === 'cancelada'; }
}
