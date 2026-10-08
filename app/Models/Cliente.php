<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Cliente extends Authenticatable
{
    protected $table = 'cliente';

    protected $fillable = [
        'restaurante_id',
        'nombre',
        'apellidos',
        'email',
        'password',
        'telefono',
    ];

    protected $hidden = ['password'];

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }
}
