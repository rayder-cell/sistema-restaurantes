<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categoria_menu', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('restaurante_id');
            $table->string('nombre', 100);
            $table->string('descripcion', 255)->nullable();
            $table->integer('orden')->default(0);
            $table->boolean('activa')->default(true);

            $table->unique(['restaurante_id', 'nombre']);

            $table->foreign('restaurante_id')
                  ->references('id')->on('restaurante')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categoria_menu');
    }
};
