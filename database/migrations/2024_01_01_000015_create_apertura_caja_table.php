<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apertura_caja', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('caja_id');
            $table->unsignedInteger('cajero_id');
            $table->decimal('monto_inicial', 10, 2)->default(0);
            $table->decimal('monto_cierre', 10, 2)->nullable();
            $table->string('estado', 10)->default('abierta');
            $table->text('justificacion')->nullable();
            $table->timestamp('apertura_at')->useCurrent();
            $table->timestamp('cierre_at')->nullable();

            $table->foreign('caja_id')
                  ->references('id')->on('caja');

            $table->foreign('cajero_id')
                  ->references('id')->on('usuario');
        });

        DB::statement("ALTER TABLE apertura_caja ADD CONSTRAINT apertura_caja_estado_check CHECK (estado IN ('abierta','cerrada'))");

        // Columna generada: diferencia = monto_cierre - monto_inicial
        DB::statement("
            ALTER TABLE apertura_caja ADD COLUMN diferencia NUMERIC(10,2)
            GENERATED ALWAYS AS (
                CASE WHEN monto_cierre IS NOT NULL
                     THEN monto_cierre - monto_inicial
                     ELSE NULL END
            ) STORED
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('apertura_caja');
    }
};
