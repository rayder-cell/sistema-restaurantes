<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('galeria_restaurante', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurante_id')->constrained('restaurante')->onDelete('cascade');
            $table->string('imagen_url');
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('galeria_restaurante');
    }
};