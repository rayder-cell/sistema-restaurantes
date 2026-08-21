<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comprobante', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('restaurante_id');
            $table->unsignedInteger('pedido_id')->unique();
            $table->unsignedInteger('serie_id');
            $table->string('tipo', 10);
            $table->string('numero_correlativo', 20);
            $table->string('cliente_nombre', 150)->nullable();
            $table->string('cliente_ruc', 11)->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('igv', 10, 2);
            $table->decimal('total', 10, 2);
            $table->boolean('anulado')->default(false);
            $table->timestamp('emitido_at')->useCurrent();

            $table->unique(['restaurante_id', 'tipo', 'numero_correlativo']);

            $table->foreign('restaurante_id')
                  ->references('id')->on('restaurante');

            $table->foreign('pedido_id')
                  ->references('id')->on('pedido');

            $table->foreign('serie_id')
                  ->references('id')->on('serie_comprobante');
        });

        DB::statement("ALTER TABLE comprobante ADD CONSTRAINT comprobante_tipo_check CHECK (tipo IN ('boleta','factura'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('comprobante');
    }
};
