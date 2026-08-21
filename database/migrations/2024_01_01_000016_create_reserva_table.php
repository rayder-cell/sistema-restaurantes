<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reserva', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('restaurante_id');
            $table->unsignedInteger('mesa_id')->nullable();
            $table->string('cliente_nombre', 150);
            $table->string('cliente_telefono', 15);
            $table->string('cliente_email', 150)->nullable();
            $table->integer('num_personas');
            $table->date('fecha');
            $table->time('hora');
            $table->decimal('precio_base', 10, 2)->default(0);
            $table->string('estado', 15)->default('pendiente');
            $table->unsignedInteger('confirmado_por')->nullable();
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->foreign('restaurante_id')
                  ->references('id')->on('restaurante')
                  ->onDelete('cascade');

            $table->foreign('mesa_id')
                  ->references('id')->on('mesa')
                  ->onDelete('set null');

            $table->foreign('confirmado_por')
                  ->references('id')->on('usuario')
                  ->onDelete('set null');
        });

        DB::statement("ALTER TABLE reserva ADD CONSTRAINT reserva_telefono_check CHECK (cliente_telefono ~ '^[0-9]{9}$')");
        DB::statement("ALTER TABLE reserva ADD CONSTRAINT reserva_personas_check CHECK (num_personas > 0)");
        DB::statement("ALTER TABLE reserva ADD CONSTRAINT reserva_estado_check CHECK (estado IN ('pendiente','confirmada','cancelada','completada'))");

        // Columna generada: monto_adelanto = precio_base * 0.5
        DB::statement('ALTER TABLE reserva ADD COLUMN monto_adelanto NUMERIC(10,2) GENERATED ALWAYS AS (precio_base * 0.5) STORED');

        // Índice único parcial: evita doble reserva misma mesa/fecha/hora en estados activos
        DB::statement("CREATE UNIQUE INDEX idx_reserva_mesa_horario ON reserva (mesa_id, fecha, hora) WHERE estado IN ('pendiente', 'confirmada')");
    }

    public function down(): void
    {
        Schema::dropIfExists('reserva');
    }
};
