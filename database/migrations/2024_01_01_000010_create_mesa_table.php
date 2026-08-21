<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mesa', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('restaurante_id');
            $table->string('numero', 10);
            $table->string('qr_token', 100)->unique();
            $table->integer('capacidad')->default(4);
            $table->string('estado', 20)->default('libre');

            $table->unique(['restaurante_id', 'numero']);

            $table->foreign('restaurante_id')
                  ->references('id')->on('restaurante')
                  ->onDelete('cascade');
        });

        DB::statement("ALTER TABLE mesa ADD CONSTRAINT mesa_estado_check CHECK (estado IN ('libre','ocupada','reservada'))");

        // Valor por defecto del qr_token usando gen_random_uuid
        DB::statement("ALTER TABLE mesa ALTER COLUMN qr_token SET DEFAULT gen_random_uuid()::TEXT");
    }

    public function down(): void
    {
        Schema::dropIfExists('mesa');
    }
};
