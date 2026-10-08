<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurante', function (Blueprint $table) {
            $table->text('descripcion')->nullable()->after('direccion');
            $table->text('mapa_embed_url')->nullable()->after('descripcion');
        });
    }

    public function down(): void
    {
        Schema::table('restaurante', function (Blueprint $table) {
            $table->dropColumn(['descripcion', 'mapa_embed_url']);
        });
    }
};