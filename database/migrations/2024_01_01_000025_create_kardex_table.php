<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kardex', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('restaurante_id');
            $table->unsignedInteger('insumo_id');
            $table->string('tipo_movimiento', 10);
            $table->string('referencia_doc', 60)->nullable();
            $table->decimal('cantidad_entrada', 12, 3)->default(0);
            $table->decimal('cantidad_salida', 12, 3)->default(0);
            $table->decimal('saldo_resultante', 12, 3);
            $table->decimal('costo_unitario', 10, 4)->default(0);
            $table->timestamp('fecha')->useCurrent();

            $table->foreign('restaurante_id')
                  ->references('id')->on('restaurante');

            $table->foreign('insumo_id')
                  ->references('id')->on('insumo');
        });

        DB::statement("ALTER TABLE kardex ADD CONSTRAINT kardex_tipo_check CHECK (tipo_movimiento IN ('entrada','salida','ajuste'))");

        // Columna generada: costo_total
        DB::statement('ALTER TABLE kardex ADD COLUMN costo_total NUMERIC(14,2) GENERATED ALWAYS AS ((cantidad_entrada - cantidad_salida) * costo_unitario) STORED');
    }

    public function down(): void
    {
        Schema::dropIfExists('kardex');
    }
};
