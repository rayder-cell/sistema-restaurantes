<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;

class PushController extends Controller
{
    public function vapidKey()
    {
        return response()->json(['key' => config('webpush.vapid.public_key')]);
    }

    public function subscribe(Request $request)
    {
        $request->validate([
            'endpoint'     => 'required|string',
            'keys.p256dh'  => 'required|string',
            'keys.auth'    => 'required|string',
        ]);

        $usuario = Usuario::find(session('usuario_id'));

        if (!$usuario) {
            return response()->json(['success' => false], 401);
        }

        $usuario->updatePushSubscription(
            $request->endpoint,
            $request->input('keys.p256dh'),
            $request->input('keys.auth')
        );

        return response()->json(['success' => true]);
    }
}