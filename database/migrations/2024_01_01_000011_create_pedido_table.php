<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedido', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('restaurante_id');
            $table->unsignedInteger('mesa_id');
            $table->unsignedInteger('usuario_id')->nullable();
            $table->string('origen', 10)->default('mesero');
            $table->string('estado', 20)->default('pendiente');
            $table->decimal('total', 10, 2)->default(0);
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->foreign('restaurante_id')
                  ->references('id')->on('restaurante');

            $table->foreign('mesa_id')
                  ->references('id')->on('mesa');

            $table->foreign('usuario_id')
                  ->references('id')->on('usuario');
        });

        DB::statement("ALTER TABLE pedido ADD CONSTRAINT pedido_origen_check CHECK (origen IN ('mesero','cliente'))");
        DB::statement("ALTER TABLE pedido ADD CONSTRAINT pedido_estado_check CHECK (estado IN ('pendiente','en_preparacion','listo','entregado','cancelado'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido');
    }
};
