<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use NotificationChannels\WebPush\HasPushSubscriptions;

class Usuario extends Authenticatable
{
    use HasApiTokens, Notifiable, HasPushSubscriptions;

    protected $table = 'usuario';

    protected $fillable = [
        'restaurante_id',
        'rol_id',
        'nombre',
        'email',
        'password_hash',
        'foto_url',
        'activo',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'activo'     => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ── Relaciones ──────────────────────────────────────────

    public function restaurante()
    {
        return $this->belongsTo(Restaurante::class, 'restaurante_id');
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'usuario_id');
    }

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'usuario_id');
    }

    public function aperturasCaja()
    {
        return $this->hasMany(AperturaCaja::class, 'usuario_id');
    }

    public function reservasConfirmadas()
    {
        return $this->hasMany(Reserva::class, 'confirmado_por');
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    public function scopeDeRestaurante($query, string $restauranteId)
    {
        return $query->where('restaurante_id', $restauranteId);
    }

    public function scopeConRol($query, string $rol)
    {
        return $query->whereHas('rol', fn($q) => $q->where('nombre', $rol));
    }

    // ── Helpers ──────────────────────────────────────────────

    public function esSuperadmin(): bool
    {
        return $this->rol?->nombre === 'superadmin';
    }

    public function esPropietario(): bool
    {
        return $this->rol?->nombre === 'propietario';
    }

    public function esAdministrador(): bool
    {
        return $this->rol?->nombre === 'administrador';
    }

    public function esMesero(): bool
    {
        return $this->rol?->nombre === 'mesero';
    }

    public function esCocinero(): bool
    {
        return $this->rol?->nombre === 'cocinero';
    }

    public function getAuthPassword()
    {
        return $this->password_hash;
    }
}