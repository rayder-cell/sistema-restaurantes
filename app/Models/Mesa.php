<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Mesa extends Model
{
    protected $table = 'mesa';
    public $timestamps = false;

    protected $fillable = [
        'restaurante_id',
        'numero',
        'qr_token',
        'capacidad',
        'estado',
    ];

    protected static function booted(): void
    {
        static::creating(function (Mesa $mesa) {
            if (empty($mesa->qr_token)) {
                $mesa->qr_token = (string) Str::uuid();
            }
        });
    }

    // ─── Relaciones ───────────────────────────────────────────

    public function restaurante()
    {
        return $this->belongsTo(Restaurante::class, 'restaurante_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'mesa_id');
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'mesa_id');
    }

    // ─── Helpers ──────────────────────────────────────────────

    public function estaLibre(): bool
    {
        return $this->estado === 'libre';
    }

    public function estaOcupada(): bool
    {
        return $this->estado === 'ocupada';
    }

    public function estaReservada(): bool
    {
        return $this->estado === 'reservada';
    }

    public function pedidoActivo()
    {
        return $this->pedidos()
            ->whereNotIn('estado', ['entregado', 'cancelado'])
            ->latest()
            ->first();
    }
}
