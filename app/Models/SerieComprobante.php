<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SerieComprobante extends Model
{
    protected $table = 'serie_comprobante';
    public $timestamps = false;

    protected $fillable = [
        'restaurante_id',
        'tipo',
        'serie',
        'correlativo_actual',
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

    public function comprobantes()
    {
        return $this->hasMany(Comprobante::class, 'serie_id');
    }

    // ─── Helpers ──────────────────────────────────────────────

    public function siguienteNumero(): string
    {
        return $this->serie . '-' . str_pad($this->correlativo_actual, 8, '0', STR_PAD_LEFT);
    }
}
