<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_entrada', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('entrada_id');
            $table->unsignedInteger('insumo_id');
            $table->decimal('cantidad_recibida', 12, 3);
            $table->decimal('precio_unitario', 10, 2);

            $table->foreign('entrada_id')
                  ->references('id')->on('entrada_compra')
                  ->onDelete('cascade');

            $table->foreign('insumo_id')
                  ->references('id')->on('insumo');
        });

        DB::statement('ALTER TABLE detalle_entrada ADD CONSTRAINT detalle_entrada_cantidad_check CHECK (cantidad_recibida > 0)');
        DB::statement('ALTER TABLE detalle_entrada ADD CONSTRAINT detalle_entrada_precio_check CHECK (precio_unitario >= 0)');

        // Columna generada: subtotal
        DB::statement('ALTER TABLE detalle_entrada ADD COLUMN subtotal NUMERIC(12,2) GENERATED ALWAYS AS (cantidad_recibida * precio_unitario) STORED');
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_entrada');
    }
};
