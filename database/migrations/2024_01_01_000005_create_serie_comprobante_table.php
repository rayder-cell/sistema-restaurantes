<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('serie_comprobante', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('restaurante_id');
            $table->string('tipo', 10);
            $table->string('serie', 4);
            $table->integer('correlativo_actual')->default(1);
            $table->boolean('activa')->default(true);

            $table->unique(['restaurante_id', 'tipo', 'serie']);

            $table->foreign('restaurante_id')
                  ->references('id')->on('restaurante')
                  ->onDelete('cascade');
        });

        DB::statement("ALTER TABLE serie_comprobante ADD CONSTRAINT serie_comprobante_tipo_check CHECK (tipo IN ('boleta','factura'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('serie_comprobante');
    }
};
