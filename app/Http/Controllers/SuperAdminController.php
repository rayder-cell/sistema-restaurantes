<?php

namespace App\Http\Controllers;

use App\Models\Restaurante;
use App\Models\SerieComprobante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $restaurantes = Restaurante::with('seriesComprobante')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $totalRestaurantes = Restaurante::count();
        $activos           = Restaurante::where('estado', 'activo')->count();
        $suspendidos       = Restaurante::where('estado', 'suspendido')->count();
        $nuevosEsteMes     = Restaurante::whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)->count();
        $porcentajeActivos = $totalRestaurantes > 0
            ? round(($activos / $totalRestaurantes) * 100) : 0;

        $primerRestaurante = Restaurante::where('estado', 'activo')
                                ->orderBy('created_at')->first();

        return view('superadmin.dashboard', compact(
            'restaurantes',
            'totalRestaurantes',
            'activos',
            'suspendidos',
            'nuevosEsteMes',
            'porcentajeActivos',
            'primerRestaurante'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'               => 'required|string|max:150',
            'ruc'                  => 'required|digits:11|regex:/^(10|20)\d{9}$/|unique:restaurante,ruc',
            'direccion'            => 'required|string|max:255',
            'boleta_serie'         => 'required|string|max:4',
            'factura_serie'        => 'required|string|max:4',
            'logo'                 => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'propietario_nombre'   => 'required|string|max:100',
            'propietario_email'    => 'required|email|unique:usuario,email',
            'propietario_password' => [
                'required',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
        ], [
            'ruc.required'              => 'El RUC es obligatorio.',
            'ruc.digits'                => 'El RUC debe tener exactamente 11 dígitos numéricos.',
            'ruc.regex'                 => 'El RUC debe empezar con 10 (persona natural) o 20 (persona jurídica).',
            'ruc.unique'                => 'Ya existe un restaurante registrado con ese RUC.',
            'nombre.required'           => 'El nombre del restaurante es obligatorio.',
            'direccion.required'        => 'La dirección fiscal es obligatoria.',
            'boleta_serie.required'     => 'La serie de boleta es obligatoria.',
            'factura_serie.required'    => 'La serie de factura es obligatoria.',
            'logo.image'                => 'El archivo debe ser una imagen.',
            'logo.max'                  => 'El logo no puede superar 2MB.',
            'propietario_nombre.required' => 'El nombre del propietario es obligatorio.',
            'propietario_email.required'  => 'El correo del propietario es obligatorio.',
            'propietario_email.email'     => 'Ingresa un correo electrónico válido.',
            'propietario_email.unique'    => 'Ya existe un usuario registrado con ese correo.',
            'propietario_password.required' => 'La contraseña es obligatoria.',
            'propietario_password.min'      => 'La contraseña no cumple los requisitos de seguridad (mínimo 8 caracteres, mayúscula, minúscula, número y símbolo).',
            'propietario_password.mixed'    => 'La contraseña debe incluir mayúsculas y minúsculas.',
            'propietario_password.numbers'  => 'La contraseña debe incluir al menos un número.',
            'propietario_password.symbols'  => 'La contraseña debe incluir al menos un carácter especial.',
        ]);

        // Subir logo si se envió
        $logoUrl = null;
        if ($request->hasFile('logo')) {
            $path    = $request->file('logo')->store('restaurantes', 'public');
            $logoUrl = Storage::url($path);
        }

        $restaurante = Restaurante::create([
            'nombre'    => $request->nombre,
            'ruc'       => $request->ruc,
            'direccion' => $request->direccion,
            'logo_url'  => $logoUrl,
            'estado'    => 'activo',
        ]);

        SerieComprobante::create([
            'restaurante_id'     => $restaurante->id,
            'tipo'               => 'boleta',
            'serie'              => strtoupper($request->boleta_serie),
            'correlativo_actual' => 1,
            'activa'             => true,
        ]);

        SerieComprobante::create([
            'restaurante_id'     => $restaurante->id,
            'tipo'               => 'factura',
            'serie'              => strtoupper($request->factura_serie),
            'correlativo_actual' => 1,
            'activa'             => true,
        ]);

        $rolPropietario = \App\Models\Rol::where('nombre', 'propietario')->firstOrFail();

        DB::table('usuario')->insert([
            'restaurante_id' => $restaurante->id,
            'rol_id'         => $rolPropietario->id,
            'nombre'         => $request->propietario_nombre,
            'email'          => $request->propietario_email,
            'password_hash'  => Hash::make($request->propietario_password),
            'activo'         => true,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return redirect()->route('superadmin.dashboard')
            ->with('success', "Restaurante \"{$restaurante->nombre}\" registrado con su propietario correctamente.");
    }

    public function show(Restaurante $restaurante)
    {
        $restaurante->load(['seriesComprobante', 'usuarios.rol']);
        return view('superadmin.restaurante-show', compact('restaurante'));
    }

    public function edit(Restaurante $restaurante)
    {
        $restaurante->load(['seriesComprobante', 'usuarios.rol']);
        return view('superadmin.restaurante-edit', compact('restaurante'));
    }

    public function update(Request $request, Restaurante $restaurante)
    {
        $request->validate([
            'nombre'               => 'required|string|max:150',
            'ruc'                  => 'required|digits:11|regex:/^(10|20)\d{9}$/|unique:restaurante,ruc,' . $restaurante->id,
            'direccion'            => 'required|string|max:255',
            'logo'                 => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'propietario_password' => [
                'nullable',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
        ], [
            'ruc.digits' => 'El RUC debe tener exactamente 11 dígitos numéricos.',
            'ruc.regex'  => 'El RUC debe empezar con 10 (persona natural) o 20 (persona jurídica).',
            'ruc.unique' => 'Ya existe un restaurante registrado con ese RUC.',
            'propietario_password.min'     => 'La contraseña no cumple los requisitos de seguridad (mínimo 8 caracteres, mayúscula, minúscula, número y símbolo).',
            'propietario_password.mixed'   => 'La contraseña debe incluir mayúsculas y minúsculas.',
            'propietario_password.numbers' => 'La contraseña debe incluir al menos un número.',
            'propietario_password.symbols' => 'La contraseña debe incluir al menos un carácter especial.',
        ]);

        $data = [
            'nombre'    => $request->nombre,
            'ruc'       => $request->ruc,
            'direccion' => $request->direccion,
        ];

        if ($request->hasFile('logo')) {
            if ($restaurante->logo_url) {
                $oldPath = str_replace('/storage/', '', $restaurante->logo_url);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('logo')->store('restaurantes', 'public');
            $data['logo_url'] = Storage::url($path);
        }

        $restaurante->update($data);

        if ($request->filled('boleta_serie')) {
            $restaurante->seriesComprobante()->where('tipo', 'boleta')
                ->update(['serie' => strtoupper($request->boleta_serie)]);
        }
        if ($request->filled('factura_serie')) {
            $restaurante->seriesComprobante()->where('tipo', 'factura')
                ->update(['serie' => strtoupper($request->factura_serie)]);
        }

        $propietario = $restaurante->usuarios()
            ->whereHas('rol', fn($q) => $q->where('nombre', 'propietario'))
            ->first();

        if ($propietario) {
            $datosP = [];
            if ($request->filled('propietario_nombre'))   $datosP['nombre'] = $request->propietario_nombre;
            if ($request->filled('propietario_email'))    $datosP['email']  = $request->propietario_email;
            if ($request->filled('propietario_password')) $datosP['password_hash'] = Hash::make($request->propietario_password);
            if (!empty($datosP)) {
                $datosP['updated_at'] = now();
                DB::table('usuario')->where('id', $propietario->id)->update($datosP);
            }
        }

        return redirect()->route('superadmin.dashboard')
            ->with('success', "Restaurante \"{$restaurante->nombre}\" actualizado correctamente.");
    }

    public function destroy(Restaurante $restaurante)
    {
        $nombre = $restaurante->nombre;
        $restaurante->seriesComprobante()->delete();
        DB::table('usuario')->where('restaurante_id', $restaurante->id)->delete();
        $restaurante->delete();

        return redirect()->route('superadmin.dashboard')
            ->with('success', "Restaurante \"{$nombre}\" eliminado correctamente.");
    }

    public function suspender(Restaurante $restaurante)
    {
        $restaurante->update(['estado' => 'suspendido']);
        return redirect()->route('superadmin.dashboard')
            ->with('success', "Restaurante \"{$restaurante->nombre}\" suspendido correctamente.");
    }

    public function reactivar(Restaurante $restaurante)
    {
        $restaurante->update(['estado' => 'activo']);
        return redirect()->route('superadmin.dashboard')
            ->with('success', "Restaurante \"{$restaurante->nombre}\" reactivado correctamente.");
    }
}