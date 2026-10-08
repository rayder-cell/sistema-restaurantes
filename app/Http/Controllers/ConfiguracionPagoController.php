<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class ConfiguracionPagoController extends Controller
{
    public function index()
    {
        $restauranteId = session('restaurante_id');

        $restaurante = DB::table('restaurante')->where('id', $restauranteId)->first();

        $tieneCulqiConfigurado = !empty($restaurante->culqi_public_key) && !empty($restaurante->culqi_secret_key);

        return view('configuracion.pagos', compact('restaurante', 'tieneCulqiConfigurado'));
    }

    public function guardar(Request $request)
    {
        $restauranteId = session('restaurante_id');

        $request->validate([
            'culqi_public_key' => 'required|string|starts_with:pk_test_,pk_live_',
            'culqi_secret_key' => 'required|string|starts_with:sk_test_,sk_live_',
        ], [
            'culqi_public_key.starts_with' => 'La llave pública debe empezar con pk_test_ o pk_live_.',
            'culqi_secret_key.starts_with' => 'La llave secreta debe empezar con sk_test_ o sk_live_.',
        ]);

        DB::table('restaurante')->where('id', $restauranteId)->update([
            'culqi_public_key' => $request->culqi_public_key,
            'culqi_secret_key' => Crypt::encryptString($request->culqi_secret_key),
        ]);

        return back()->with('success', 'Llaves de Culqi guardadas correctamente.');
    }

    public function eliminar()
    {
        $restauranteId = session('restaurante_id');

        DB::table('restaurante')->where('id', $restauranteId)->update([
            'culqi_public_key' => null,
            'culqi_secret_key' => null,
        ]);

        return back()->with('success', 'Se desactivó el cobro con tarjeta para este restaurante.');
    }
}