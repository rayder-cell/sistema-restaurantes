<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificacion', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('restaurante_id');
            $table->unsignedInteger('usuario_id');
            $table->unsignedInteger('pedido_id')->nullable();
            $table->string('tipo', 30);
            $table->text('mensaje');
            $table->boolean('leida')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('restaurante_id')
                  ->references('id')->on('restaurante');

            $table->foreign('usuario_id')
                  ->references('id')->on('usuario')
                  ->onDelete('cascade');

            $table->foreign('pedido_id')
                  ->references('id')->on('pedido')
                  ->onDelete('set null');
        });

        DB::statement("ALTER TABLE notificacion ADD CONSTRAINT notificacion_tipo_check CHECK (tipo IN ('cambio_estado_pedido','stock_minimo','reserva_nueva','reserva_confirmada'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('notificacion');
    }
};
