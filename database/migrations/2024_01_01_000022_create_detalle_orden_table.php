<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_orden', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('orden_id');
            $table->unsignedInteger('insumo_id');
            $table->decimal('cantidad_pedida', 12, 3);
            $table->decimal('precio_unitario', 10, 2);

            $table->foreign('orden_id')
                  ->references('id')->on('orden_compra')
                  ->onDelete('cascade');

            $table->foreign('insumo_id')
                  ->references('id')->on('insumo');
        });

        DB::statement('ALTER TABLE detalle_orden ADD CONSTRAINT detalle_orden_cantidad_check CHECK (cantidad_pedida > 0)');
        DB::statement('ALTER TABLE detalle_orden ADD CONSTRAINT detalle_orden_precio_check CHECK (precio_unitario >= 0)');

        // Columna generada: subtotal
        DB::statement('ALTER TABLE detalle_orden ADD COLUMN subtotal NUMERIC(12,2) GENERATED ALWAYS AS (cantidad_pedida * precio_unitario) STORED');
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_orden');
    }
};
