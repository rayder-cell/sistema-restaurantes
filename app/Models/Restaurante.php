<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Restaurante extends Model
{
    protected $table = 'restaurante';

    protected $fillable = [
        'nombre',
        'ruc',
        'direccion',
        'logo_url',
        'estado',
    ];

    // ─── Relaciones ───────────────────────────────────────────

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'restaurante_id');
    }

    public function cajas()
    {
        return $this->hasMany(Caja::class, 'restaurante_id');
    }

    public function seriesComprobante()
    {
        return $this->hasMany(SerieComprobante::class, 'restaurante_id');
    }

    public function categorias()
    {
        return $this->hasMany(CategoriaMenu::class, 'restaurante_id');
    }

    public function marcas()
    {
        return $this->hasMany(Marca::class, 'restaurante_id');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'restaurante_id');
    }

    public function mesas()
    {
        return $this->hasMany(Mesa::class, 'restaurante_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'restaurante_id');
    }

    public function comprobantes()
    {
        return $this->hasMany(Comprobante::class, 'restaurante_id');
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'restaurante_id');
    }

    public function proveedores()
    {
        return $this->hasMany(Proveedor::class, 'restaurante_id');
    }

    public function insumos()
    {
        return $this->hasMany(Insumo::class, 'restaurante_id');
    }

    public function ordenesCompra()
    {
        return $this->hasMany(OrdenCompra::class, 'restaurante_id');
    }

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'restaurante_id');
    }

    // ─── Scopes ───────────────────────────────────────────────

    public function scopeActivo($query)
    {
        return $query->where('estado', 'activo');
    }
}
