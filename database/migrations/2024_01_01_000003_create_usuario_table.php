<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('restaurante_id')->nullable();
            $table->unsignedInteger('rol_id');
            $table->string('nombre', 150);
            $table->string('email', 150)->unique();
            $table->string('password_hash', 255);
            $table->string('foto_url', 500)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('restaurante_id')
                  ->references('id')->on('restaurante')
                  ->onDelete('cascade');

            $table->foreign('rol_id')
                  ->references('id')->on('rol');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario');
    }
};
