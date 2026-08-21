<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('restaurante_id');
            $table->unsignedInteger('categoria_id');
            $table->unsignedInteger('marca_id')->nullable();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2);
            $table->string('imagen_url', 500)->nullable();
            $table->boolean('disponible')->default(true);
            $table->timestamps();

            $table->foreign('restaurante_id')
                  ->references('id')->on('restaurante')
                  ->onDelete('cascade');

            $table->foreign('categoria_id')
                  ->references('id')->on('categoria_menu');

            $table->foreign('marca_id')
                  ->references('id')->on('marca');
        });

        DB::statement('ALTER TABLE producto ADD CONSTRAINT producto_precio_check CHECK (precio >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('producto');
    }
};
