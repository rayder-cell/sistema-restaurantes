<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permiso', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('rol_id');
            $table->string('modulo', 60);
            $table->boolean('puede_leer')->default(false);
            $table->boolean('puede_escribir')->default(false);
            $table->boolean('puede_eliminar')->default(false);

            $table->unique(['rol_id', 'modulo']);

            $table->foreign('rol_id')
                  ->references('id')->on('rol')
                  ->onDelete('cascade');
        });

        // Permisos por defecto
        $permisos = [
            ['superadmin',    'restaurantes',  true,  true,  true],
            ['superadmin',    'usuarios',      true,  true,  true],
            ['propietario',   'configuracion', true,  true,  false],
            ['propietario',   'usuarios',      true,  true,  true],
            ['administrador', 'menu',          true,  true,  true],
            ['administrador', 'inventario',    true,  true,  true],
            ['administrador', 'reportes',      true,  false, false],
            ['administrador', 'reservas',      true,  true,  true],
            ['mesero',        'pedidos',       true,  true,  false],
            ['cocinero',      'pedidos',       true,  true,  false],
            ['cajero',        'pagos',         true,  true,  false],
            ['cajero',        'caja',          true,  true,  false],
        ];

        foreach ($permisos as [$rolNombre, $modulo, $leer, $escribir, $eliminar]) {
            $rol = DB::table('rol')->where('nombre', $rolNombre)->first();
            if ($rol) {
                DB::table('permiso')->insert([
                    'rol_id'          => $rol->id,
                    'modulo'          => $modulo,
                    'puede_leer'      => $leer,
                    'puede_escribir'  => $escribir,
                    'puede_eliminar'  => $eliminar,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('permiso');
    }
};
