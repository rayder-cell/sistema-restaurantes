<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login de usuario
     */
    public function login(LoginRequest $request)
    {
        $usuario = Usuario::with(['rol', 'restaurante'])
            ->where('email', $request->email)
            ->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password_hash)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        if (!$usuario->activo) {
            return response()->json([
                'message' => 'Tu cuenta está desactivada. Contacta al administrador.',
            ], 403);
        }

        // Revocar tokens anteriores
        $usuario->tokens()->delete();

        // Crear nuevo token con habilidades según rol
        $token = $usuario->createToken(
            'auth_token',
            $this->habilidadesPorRol($usuario->rol->nombre)
        )->plainTextToken;

        return response()->json([
            'message' => 'Sesión iniciada correctamente.',
            'token'   => $token,
            'usuario' => [
                'id'             => $usuario->id,
                'nombre'         => $usuario->nombre,
                'email'          => $usuario->email,
                'rol'            => $usuario->rol->nombre,
                'restaurante'    => $usuario->restaurante?->nombre,
                'restaurante_id' => $usuario->restaurante_id,
            ],
        ]);
    }

    /**
     * Registro de nuevo usuario (solo superadmin o propietario)
     */
    public function register(RegisterRequest $request)
    {
        $rol = Rol::findOrFail($request->rol_id);

        // Un propietario solo puede crear usuarios de su propio restaurante
        $authUser = $request->user();
        if ($authUser->rol->nombre === 'propietario') {
            if ($request->restaurante_id !== $authUser->restaurante_id) {
                return response()->json([
                    'message' => 'No puedes crear usuarios para otro restaurante.',
                ], 403);
            }
            // Un propietario no puede crear superadmins ni otros propietarios
            if (in_array($rol->nombre, ['superadmin', 'propietario'])) {
                return response()->json([
                    'message' => 'No tienes permisos para asignar ese rol.',
                ], 403);
            }
        }

        $usuario = DB::table('usuario')->insertGetId([
            'restaurante_id' => $request->restaurante_id,
            'rol_id'         => $request->rol_id,
            'nombre'         => $request->nombre,
            'email'          => $request->email,
            'password_hash'  => Hash::make($request->password),
            'activo'         => true,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $usuarioCreado = Usuario::with(['rol', 'restaurante'])->find($usuario);

        return response()->json([
            'message' => 'Usuario creado correctamente.',
            'usuario' => [
                'id'          => $usuarioCreado->id,
                'nombre'      => $usuarioCreado->nombre,
                'email'       => $usuarioCreado->email,
                'rol'         => $usuarioCreado->rol->nombre,
                'restaurante' => $usuarioCreado->restaurante?->nombre,
            ],
        ], 201);
    }

    /**
     * Perfil del usuario autenticado
     */
    public function perfil(Request $request)
    {
        $usuario = $request->user()->load(['rol', 'restaurante']);

        return response()->json([
            'usuario' => [
                'id'             => $usuario->id,
                'nombre'         => $usuario->nombre,
                'email'          => $usuario->email,
                'rol'            => $usuario->rol->nombre,
                'restaurante'    => $usuario->restaurante?->nombre,
                'restaurante_id' => $usuario->restaurante_id,
                'activo'         => $usuario->activo,
                'created_at'     => $usuario->created_at,
            ],
        ]);
    }

    /**
     * Actualizar perfil
     */
    public function actualizarPerfil(Request $request)
    {
        $request->validate([
            'nombre'          => 'sometimes|string|max:150',
            'email'           => 'sometimes|email|unique:usuario,email,' . $request->user()->id,
            'password_actual' => 'required_with:password|string',
            'password'        => 'sometimes|string|min:8|confirmed',
        ]);

        $usuario = $request->user();

        if ($request->filled('password')) {
            if (!Hash::check($request->password_actual, $usuario->password_hash)) {
                throw ValidationException::withMessages([
                    'password_actual' => ['La contraseña actual es incorrecta.'],
                ]);
            }
            DB::table('usuario')
                ->where('id', $usuario->id)
                ->update(['password_hash' => Hash::make($request->password)]);
        }

        if ($request->filled('nombre')) $usuario->nombre = $request->nombre;
        if ($request->filled('email'))  $usuario->email  = $request->email;

        $usuario->save();

        return response()->json(['message' => 'Perfil actualizado correctamente.']);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente.']);
    }

    /**
     * Habilidades (abilities) del token según el rol
     */
    private function habilidadesPorRol(string $rol): array
    {
        return match ($rol) {
            'superadmin'    => ['*'],
            'propietario'   => ['restaurante:gestionar', 'usuarios:gestionar', 'reportes:ver', 'menu:gestionar', 'pedidos:gestionar', 'inventario:gestionar', 'reservas:gestionar', 'caja:gestionar'],
            'administrador' => ['menu:gestionar', 'pedidos:gestionar', 'inventario:gestionar', 'reservas:gestionar', 'caja:gestionar', 'reportes:ver'],
            'mesero'        => ['pedidos:crear', 'pedidos:ver', 'mesas:ver', 'reservas:ver'],
            'cocinero'      => ['pedidos:ver', 'pedidos:estado'],
            default         => ['pedidos:ver'],
        };
    }
}