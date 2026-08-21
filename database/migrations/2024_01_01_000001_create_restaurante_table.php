<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurante', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre', 150);
            $table->string('ruc', 11)->unique();
            $table->string('direccion', 255);
            $table->string('logo_url', 500)->nullable();
            $table->string('estado', 20)->default('activo');
            $table->timestamps();
        });

        DB::statement("ALTER TABLE restaurante ADD CONSTRAINT restaurante_estado_check CHECK (estado IN ('activo','inactivo','suspendido'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurante');
    }
};
