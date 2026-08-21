<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comprobante', function (Blueprint $table) {
            $table->dropUnique('comprobante_pedido_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('comprobante', function (Blueprint $table) {
            $table->unique('pedido_id', 'comprobante_pedido_id_unique');
        });
    }
};