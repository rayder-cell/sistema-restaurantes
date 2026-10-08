<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('comprobante', function (Blueprint $table) {
            $table->string('sunat_serie', 10)->nullable();
            $table->integer('sunat_numero')->nullable();
            $table->string('sunat_enlace_pdf', 255)->nullable();
            $table->string('sunat_enlace_xml', 255)->nullable();
            $table->string('sunat_estado', 20)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comprobante', function (Blueprint $table) {
            //
        });
    }
};
