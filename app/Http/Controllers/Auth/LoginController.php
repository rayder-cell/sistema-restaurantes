<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLogin()
    {
        if (session('usuario_id')) {
            if (session('usuario_rol') === 'superadmin') {
                return redirect()->route('superadmin.dashboard');
            }
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required'    => 'El correo es obligatorio.',
            'email.email'       => 'El correo no tiene formato válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        $usuario = Usuario::with(['rol', 'restaurante'])
            ->where('email', $request->email)
            ->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password_hash)) {
            return back()->withErrors(['email' => 'Credenciales incorrectas.'])->withInput();
        }

        if (!$usuario->activo) {
            return back()->with('error', 'Tu cuenta está desactivada. Contacta al administrador.');
        }

        // Guardar datos en sesión
        session([
            'usuario_id'          => $usuario->id,
            'usuario_nombre'      => $usuario->nombre,
            'usuario_email'       => $usuario->email,
            'usuario_rol'         => $usuario->rol->nombre,
            'restaurante_id'      => $usuario->restaurante_id,
            'restaurante_nombre'  => $usuario->restaurante?->nombre,
            'restaurante_logo'    => $usuario->restaurante?->logo_url,
        ]);

        // Redirigir según rol
        if ($usuario->rol->nombre === 'superadmin') {
            return redirect()->route('superadmin.dashboard');
        }

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        return redirect()->route('login');
    }
}
