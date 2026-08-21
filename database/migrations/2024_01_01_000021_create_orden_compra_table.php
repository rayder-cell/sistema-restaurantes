<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orden_compra', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('restaurante_id');
            $table->unsignedInteger('proveedor_id');
            $table->unsignedInteger('usuario_id');
            $table->string('numero', 20);
            $table->string('estado', 20)->default('borrador');
            $table->decimal('total', 12, 2)->default(0);
            $table->date('fecha_emision')->useCurrent();
            $table->date('fecha_entrega_est')->nullable();

            $table->unique(['restaurante_id', 'numero']);

            $table->foreign('restaurante_id')
                  ->references('id')->on('restaurante')
                  ->onDelete('cascade');

            $table->foreign('proveedor_id')
                  ->references('id')->on('proveedor');

            $table->foreign('usuario_id')
                  ->references('id')->on('usuario');
        });

        DB::statement("ALTER TABLE orden_compra ADD CONSTRAINT orden_compra_estado_check CHECK (estado IN ('borrador','enviada','recibida','cancelada'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('orden_compra');
    }
};
