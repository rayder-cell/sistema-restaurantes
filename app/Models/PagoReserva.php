<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoReserva extends Model
{
    protected $table = 'pago_reserva';
    public $timestamps = false;

    protected $fillable = [
        'reserva_id',
        'metodo',
        'monto_pagado',
        'referencia',
        'estado',
    ];

    protected $casts = [
        'monto_pagado' => 'decimal:2',
        'pagado_at'    => 'datetime',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }
}
