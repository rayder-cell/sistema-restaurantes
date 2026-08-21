<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metodo_pago', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('comprobante_id');
            $table->unsignedInteger('caja_id');
            $table->unsignedInteger('cajero_id');
            $table->string('metodo', 20);
            $table->decimal('monto', 10, 2);
            $table->decimal('vuelto', 10, 2)->default(0);
            $table->string('referencia', 100)->nullable();
            $table->timestamp('pagado_at')->useCurrent();

            $table->foreign('comprobante_id')
                  ->references('id')->on('comprobante')
                  ->onDelete('cascade');

            $table->foreign('caja_id')
                  ->references('id')->on('caja');

            $table->foreign('cajero_id')
                  ->references('id')->on('usuario');
        });

        DB::statement("ALTER TABLE metodo_pago ADD CONSTRAINT metodo_pago_metodo_check CHECK (metodo IN ('efectivo','tarjeta','yape'))");
        DB::statement('ALTER TABLE metodo_pago ADD CONSTRAINT metodo_pago_monto_check CHECK (monto > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('metodo_pago');
    }
};
