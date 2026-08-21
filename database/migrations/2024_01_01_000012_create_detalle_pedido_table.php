<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_pedido', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pedido_id');
            $table->unsignedInteger('producto_id');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->text('observacion')->nullable();
            $table->string('estado', 20)->default('pendiente');

            $table->foreign('pedido_id')
                  ->references('id')->on('pedido')
                  ->onDelete('cascade');

            $table->foreign('producto_id')
                  ->references('id')->on('producto');
        });

        DB::statement('ALTER TABLE detalle_pedido ADD CONSTRAINT detalle_pedido_cantidad_check CHECK (cantidad > 0)');
        DB::statement("ALTER TABLE detalle_pedido ADD CONSTRAINT detalle_pedido_estado_check CHECK (estado IN ('pendiente','en_preparacion','listo','entregado'))");

        // Columna generada: subtotal = cantidad * precio_unitario
        DB::statement('ALTER TABLE detalle_pedido ADD COLUMN subtotal NUMERIC(10,2) GENERATED ALWAYS AS (cantidad * precio_unitario) STORED');
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_pedido');
    }
};
