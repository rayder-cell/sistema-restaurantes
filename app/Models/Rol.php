<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'rol';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'rol_id');
    }

    public function permisos()
    {
        return $this->hasMany(Permiso::class, 'rol_id');
    }
}
