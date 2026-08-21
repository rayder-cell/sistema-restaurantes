<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'cliente';

    protected $fillable = ['restaurante_id', 'nombre', 'apellidos'];

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }
}