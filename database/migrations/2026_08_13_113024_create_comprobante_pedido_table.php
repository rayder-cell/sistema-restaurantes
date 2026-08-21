<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comprobante_pedido', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comprobante_id')->constrained('comprobante')->onDelete('cascade');
            $table->foreignId('pedido_id')->constrained('pedido')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comprobante_pedido');
    }
};