<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    protected $table = 'caja';
    public $timestamps = false;

    protected $fillable = [
        'restaurante_id',
        'nombre',
        'serie',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    public function restaurante()
    {
        return $this->belongsTo(Restaurante::class, 'restaurante_id');
    }

    public function aperturas()
    {
        return $this->hasMany(AperturaCaja::class, 'caja_id');
    }

    public function metodosPago()
    {
        return $this->hasMany(MetodoPago::class, 'caja_id');
    }

    // ─── Helpers ──────────────────────────────────────────────

    public function aperturaActiva()
    {
        return $this->aperturas()->where('estado', 'abierta')->latest('apertura_at')->first();
    }

    public function estaAbierta(): bool
    {
        return $this->aperturas()->where('estado', 'abierta')->exists();
    }
}
