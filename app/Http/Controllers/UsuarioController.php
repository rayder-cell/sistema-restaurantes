<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Restaurante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function index()
    {
        $rolActual     = session('usuario_rol');
        $restauranteId = session('restaurante_id');
        $usuarioId     = session('usuario_id');

        $query = Usuario::with(['rol', 'restaurante'])
            ->whereHas('rol', fn($q) => $q->where('nombre', '!=', 'superadmin'))
            ->where('id', '!=', $usuarioId);

        if ($rolActual !== 'superadmin') {
            $query->where('restaurante_id', $restauranteId);
        }

        $usuarios     = $query->orderBy('nombre')->paginate(15);
        $roles        = Rol::where('nombre', '!=', 'superadmin')->get();
        $restaurantes = $rolActual === 'superadmin'
            ? Restaurante::where('estado', 'activo')->get()
            : collect();

        return view('usuarios.index', compact('usuarios', 'roles', 'restaurantes'));
    }

    public function create()
    {
        $rolActual     = session('usuario_rol');
        $restauranteId = session('restaurante_id');

        $roles        = Rol::where('nombre', '!=', 'superadmin')->get();
        $restaurantes = $rolActual === 'superadmin'
            ? Restaurante::where('estado', 'activo')->get()
            : collect();

        return view('usuarios.create', compact('roles', 'restaurantes', 'rolActual', 'restauranteId'));
    }

    public function store(Request $request)
    {
        $rolActual     = session('usuario_rol');
        $restauranteId = session('restaurante_id');

        $request->validate([
            'nombre'   => 'required|string|max:100',
            'email'    => 'required|email:rfc|unique:usuario,email|max:100',
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
            ],
            'rol_id'   => 'required|exists:rol,id',
        ], [
            'email.email'         => 'Ingresa un correo electrónico válido.',
            'email.unique'        => 'Este correo ya está registrado.',
            'password.min'        => 'La contraseña debe tener al menos 8 caracteres.',
            'password.regex'      => 'La contraseña debe incluir mayúsculas, minúsculas y números.',
            'password.confirmed'  => 'Las contraseñas no coinciden.',
        ]);

        $resId = $rolActual === 'superadmin'
            ? $request->restaurante_id
            : $restauranteId;

        DB::table('usuario')->insert([
            'restaurante_id' => $resId,
            'rol_id'         => $request->rol_id,
            'nombre'         => $request->nombre,
            'email'          => $request->email,
            'password_hash'  => Hash::make($request->password),
            'activo'         => true,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(Usuario $usuario)
    {
        $rolActual     = session('usuario_rol');
        $restauranteId = session('restaurante_id');

        if ($rolActual !== 'superadmin' && $usuario->restaurante_id !== $restauranteId) {
            abort(403);
        }

        $roles        = Rol::where('nombre', '!=', 'superadmin')->get();
        $restaurantes = $rolActual === 'superadmin'
            ? Restaurante::where('estado', 'activo')->get()
            : collect();

        return view('usuarios.edit', compact('usuario', 'roles', 'restaurantes', 'rolActual', 'restauranteId'));
    }

    public function update(Request $request, Usuario $usuario)
    {
        $rolActual     = session('usuario_rol');
        $restauranteId = session('restaurante_id');

        if ($rolActual !== 'superadmin' && $usuario->restaurante_id !== $restauranteId) {
            abort(403);
        }

        $request->validate([
            'nombre'   => 'required|string|max:100',
            'email'    => 'required|email:rfc|unique:usuario,email,' . $usuario->id . '|max:100',
            'rol_id'   => 'required|exists:rol,id',
            'password' => [
                'nullable',
                'confirmed',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
            ],
        ], [
            'email.email'         => 'Ingresa un correo electrónico válido.',
            'email.unique'        => 'Este correo ya está registrado.',
            'password.min'        => 'La contraseña debe tener al menos 8 caracteres.',
            'password.regex'      => 'La contraseña debe incluir mayúsculas, minúsculas y números.',
            'password.confirmed'  => 'Las contraseñas no coinciden.',
        ]);

        $data = [
            'nombre'     => $request->nombre,
            'email'      => $request->email,
            'rol_id'     => $request->rol_id,
            'activo'     => $request->boolean('activo'),
            'updated_at' => now(),
        ];

        if ($request->filled('password')) {
            $data['password_hash'] = Hash::make($request->password);
        }

        if ($rolActual === 'superadmin' && $request->filled('restaurante_id')) {
            $data['restaurante_id'] = $request->restaurante_id;
        }

        DB::table('usuario')->where('id', $usuario->id)->update($data);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(Usuario $usuario)
    {
        $rolActual     = session('usuario_rol');
        $restauranteId = session('restaurante_id');

        if ($rolActual !== 'superadmin' && $usuario->restaurante_id !== $restauranteId) {
            abort(403);
        }

        if ($usuario->id === session('usuario_id')) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        DB::table('usuario')->where('id', $usuario->id)->delete();

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario eliminado correctamente.');
    }

    public function toggleActivo(Usuario $usuario)
    {
        DB::table('usuario')->where('id', $usuario->id)->update([
            'activo'     => !$usuario->activo,
            'updated_at' => now(),
        ]);

        $estado = $usuario->activo ? 'desactivado' : 'activado';
        return back()->with('success', "Usuario {$estado} correctamente.");
    }
}