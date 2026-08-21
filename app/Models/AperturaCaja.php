<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AperturaCaja extends Model
{
    protected $table = 'apertura_caja';
    public $timestamps = false;

    protected $fillable = [
        'caja_id',
        'cajero_id',
        'monto_inicial',
        'monto_cierre',
        'estado',
        'justificacion',
        'cierre_at',
    ];

    protected $casts = [
        'monto_inicial' => 'decimal:2',
        'monto_cierre'  => 'decimal:2',
        'diferencia'    => 'decimal:2', // columna generada
        'apertura_at'   => 'datetime',
        'cierre_at'     => 'datetime',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    public function caja()
    {
        return $this->belongsTo(Caja::class, 'caja_id');
    }

    public function cajero()
    {
        return $this->belongsTo(Usuario::class, 'cajero_id');
    }

    // ─── Helpers ──────────────────────────────────────────────

    public function estaAbierta(): bool
    {
        return $this->estado === 'abierta';
    }
}
