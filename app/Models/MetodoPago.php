<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetodoPago extends Model
{
    protected $table = 'metodo_pago';
    public $timestamps = false;

    protected $fillable = [
        'comprobante_id',
        'caja_id',
        'cajero_id',
        'metodo',
        'monto',
        'vuelto',
        'referencia',
    ];

    protected $casts = [
        'monto'     => 'decimal:2',
        'vuelto'    => 'decimal:2',
        'pagado_at' => 'datetime',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    public function comprobante()
    {
        return $this->belongsTo(Comprobante::class, 'comprobante_id');
    }

    public function caja()
    {
        return $this->belongsTo(Caja::class, 'caja_id');
    }

    public function cajero()
    {
        return $this->belongsTo(Usuario::class, 'cajero_id');
    }
}
