<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pago_reserva', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('reserva_id')->unique();
            $table->string('metodo', 20);
            $table->decimal('monto_pagado', 10, 2);
            $table->string('referencia', 100)->nullable();
            $table->string('estado', 15)->default('pendiente');
            $table->timestamp('pagado_at')->useCurrent();

            $table->foreign('reserva_id')
                  ->references('id')->on('reserva')
                  ->onDelete('cascade');
        });

        DB::statement("ALTER TABLE pago_reserva ADD CONSTRAINT pago_reserva_metodo_check CHECK (metodo IN ('efectivo','tarjeta','yape'))");
        DB::statement('ALTER TABLE pago_reserva ADD CONSTRAINT pago_reserva_monto_check CHECK (monto_pagado > 0)');
        DB::statement("ALTER TABLE pago_reserva ADD CONSTRAINT pago_reserva_estado_check CHECK (estado IN ('pendiente','verificado','devuelto'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('pago_reserva');
    }
};
