<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entrada_compra', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('restaurante_id');
            $table->unsignedInteger('orden_id')->nullable();
            $table->unsignedInteger('usuario_id');
            $table->string('numero', 20);
            $table->date('fecha')->useCurrent();
            $table->decimal('total', 12, 2)->default(0);

            $table->unique(['restaurante_id', 'numero']);

            $table->foreign('restaurante_id')
                  ->references('id')->on('restaurante')
                  ->onDelete('cascade');

            $table->foreign('orden_id')
                  ->references('id')->on('orden_compra');

            $table->foreign('usuario_id')
                  ->references('id')->on('usuario');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entrada_compra');
    }
};
