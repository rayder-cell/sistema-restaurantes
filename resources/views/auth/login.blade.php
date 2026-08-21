<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión — MIKHUNAWASI</title>
    @vite(['resources/assets/css/pages/login.css', 'resources/assets/js/app.js'])
</head>

<body>
    <div class="login-page">
        <div class="login-overlay"></div>
        <div class="login-container">

            <div class="login-logo">
                <div class="logo-icon">🍽</div>
                <h1 class="logo-title">MIKHUNAWASI</h1>
                <p class="logo-subtitle">Panel de administración</p>
            </div>

            <div class="login-card">

                @if ($errors->any())
                    <div class="alert-error">{{ $errors->first() }}</div>
                @endif
                @if (session('error'))
                    <div class="alert-error">{{ session('error') }}</div>
                @endif

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label">Correo Electrónico</label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="form-input"
                            placeholder="admin@ejemplo.com">
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="password" required class="form-input" placeholder="••••••••">
                    </div>

                    <div class="mb-6 text-right">
                        <a href="#" class="text-sm text-purple-600 hover:text-purple-800">¿Olvidaste tu
                            contraseña?</a>
                    </div>

                    <div class="mb-6 flex items-center gap-2">
                        <input type="checkbox" name="remember" id="remember"
                            class="w-4 h-4 rounded border-black-300 text-purple-600 focus:ring-purple-500">
                        <label for="remember" class="text-sm text-black-600">Recordar mi sesión</label>
                    </div>

                    <button type="submit" class="btn-primary">
                        Ingresar →
                    </button>
                </form>
            </div>

            <div class="login-footer">
                <p>© {{ date('Y') }} MIKHUNAWASI. Todos los derechos reservados.</p>
                <div class="login-footer-links">
                    <a href="#" class="login-footer-link">Soporte</a>
                    <a href="#" class="login-footer-link">Términos</a>
                    <a href="#" class="login-footer-link">Privacidad</a>
                </div>
            </div>

        </div>
    </div>
</body>

</html>
