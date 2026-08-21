<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'notificacion';
    public $timestamps = false;

    protected $fillable = [
        'restaurante_id',
        'usuario_id',
        'pedido_id',
        'tipo',
        'mensaje',
        'leida',
    ];

    protected $casts = [
        'leida'      => 'boolean',
        'created_at' => 'datetime',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    public function restaurante()
    {
        return $this->belongsTo(Restaurante::class, 'restaurante_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    // ─── Scopes ───────────────────────────────────────────────

    public function scopeNoLeidas($query)
    {
        return $query->where('leida', false);
    }
}
