<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    protected $table = 'permiso';
    public $timestamps = false;

    protected $fillable = [
        'rol_id',
        'modulo',
        'puede_leer',
        'puede_escribir',
        'puede_eliminar',
    ];

    protected $casts = [
        'puede_leer'     => 'boolean',
        'puede_escribir' => 'boolean',
        'puede_eliminar' => 'boolean',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }
}
