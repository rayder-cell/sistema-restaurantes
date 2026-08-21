<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rol', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre', 50)->unique();
            $table->string('descripcion', 255)->nullable();
        });

        // Roles iniciales
        DB::table('rol')->insert([
            ['nombre' => 'superadmin',    'descripcion' => 'Administrador de la plataforma completa'],
            ['nombre' => 'propietario',   'descripcion' => 'Propietario de un restaurante'],
            ['nombre' => 'administrador', 'descripcion' => 'Administrador de un restaurante'],
            ['nombre' => 'mesero',        'descripcion' => 'Toma y gestiona pedidos por mesa'],
            ['nombre' => 'cocinero',      'descripcion' => 'Visualiza y actualiza estado de pedidos en cocina'],
            ['nombre' => 'cajero',        'descripcion' => 'Registra pagos y gestiona la caja'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('rol');
    }
};
