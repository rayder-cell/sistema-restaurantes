<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Pedido extends Model
{
    protected $table = 'pedido';

    protected $fillable = [
        'restaurante_id',
        'mesa_id',
        'usuario_id',
        'origen',
        'estado',
        'total',
        'observacion',
        'cliente_id',
    ];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    // ─── Relaciones ───────────────────────────────────────────

    public function restaurante()
    {
        return $this->belongsTo(Restaurante::class, 'restaurante_id');
    }

    public function mesa()
    {
        return $this->belongsTo(Mesa::class, 'mesa_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'pedido_id');
    }

    public function comprobante()
    {
        return $this->hasOne(Comprobante::class, 'pedido_id');
    }

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'pedido_id');
    }

    // ─── Scopes ───────────────────────────────────────────────

    public function scopeActivos($query)
    {
        return $query->whereNotIn('estado', ['entregado', 'cancelado']);
    }

    public function scopeDeRestaurante($query, int $restauranteId)
    {
        return $query->where('restaurante_id', $restauranteId);
    }

    public function scopePendientesEnCocina($query)
    {
        return $query->whereIn('estado', ['pendiente', 'en_preparacion']);
    }

    // ─── Helpers ──────────────────────────────────────────────

    public function estaPendiente(): bool
    {
        return $this->estado === 'pendiente';
    }
    public function estaEnPreparacion(): bool
    {
        return $this->estado === 'en_preparacion';
    }
    public function estaListo(): bool
    {
        return $this->estado === 'listo';
    }
    public function estaEntregado(): bool
    {
        return $this->estado === 'entregado';
    }
    public function estaCancelado(): bool
    {
        return $this->estado === 'cancelado';
    }
}
