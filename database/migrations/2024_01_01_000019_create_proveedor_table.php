<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proveedor', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('restaurante_id');
            $table->string('nombre', 150);
            $table->string('ruc', 11)->nullable();
            $table->string('telefono', 15)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('restaurante_id')
                  ->references('id')->on('restaurante')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedor');
    }
};
