<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GaleriaRestaurante extends Model
{
    protected $table = 'galeria_restaurante';

    protected $fillable = [
        'restaurante_id',
        'imagen_url',
        'orden',
    ];

    public function restaurante()
    {
        return $this->belongsTo(Restaurante::class, 'restaurante_id');
    }
}