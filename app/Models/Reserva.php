<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    protected $table = 'reserva';

    protected $fillable = [
        'restaurante_id',
        'mesa_id',
        'cliente_nombre',
        'cliente_telefono',
        'cliente_email',
        'num_personas',
        'fecha',
        'hora',
        'precio_base',
        'estado',
        'confirmado_por',
        'observacion',
    ];

    protected $casts = [
        'fecha'          => 'date',
        'hora'           => 'datetime',
        'precio_base'    => 'decimal:2',
        'monto_adelanto' => 'decimal:2', // columna generada
    ];

    // ─── Relaciones ───────────────────────────────────────────

    public function restaurante()
    {
        return $this->belongsTo(Restaurante::class, 'restaurante_id');
    }

    public function mesa()
    {
        return $this->belongsTo(Mesa::class, 'mesa_id');
    }

    public function confirmadoPor()
    {
        return $this->belongsTo(Usuario::class, 'confirmado_por');
    }

    public function pagoReserva()
    {
        return $this->hasOne(PagoReserva::class, 'reserva_id');
    }

    // ─── Scopes ───────────────────────────────────────────────

    public function scopeVigentes($query)
    {
        return $query->whereIn('estado', ['pendiente', 'confirmada']);
    }

    public function scopeDeRestaurante($query, int $restauranteId)
    {
        return $query->where('restaurante_id', $restauranteId);
    }

    // ─── Helpers ──────────────────────────────────────────────

    public function estaPendiente(): bool  { return $this->estado === 'pendiente'; }
    public function estaConfirmada(): bool { return $this->estado === 'confirmada'; }
    public function estaCancelada(): bool  { return $this->estado === 'cancelada'; }
    public function estaCompletada(): bool { return $this->estado === 'completada'; }

    public function tienePago(): bool
    {
        return $this->pagoReserva()->exists();
    }
}
