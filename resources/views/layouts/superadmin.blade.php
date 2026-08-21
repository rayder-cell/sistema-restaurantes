<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Superadmin — MIKHUNAWASI')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/assets/css/pages/superadmin.css', 'resources/assets/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="sa-body">

    <div class="sa-layout">

        {{-- ══════════════════════════════════════════════
         SIDEBAR SUPERADMIN
    ══════════════════════════════════════════════ --}}
        <aside class="sa-sidebar">

            {{-- Perfil --}}
            <div class="sa-profile">
                <div class="sa-avatar">
                    @if (session('foto_url'))
                        <img src="{{ session('foto_url') }}" alt="avatar">
                    @else
                        <span>{{ strtoupper(substr(session('usuario_nombre', 'S'), 0, 1)) }}</span>
                    @endif
                </div>
                <div>
                    <p class="sa-profile-name">{{ session('usuario_nombre', 'Super Admin') }}</p>
                    <p class="sa-profile-role">SUPERADMIN</p>
                </div>
            </div>

            {{-- Navegación --}}
            <nav class="sa-nav">
                <p class="sa-nav-label">MÓDULOS</p>
                <ul>
                    <li>
                        <a href="{{ route('superadmin.dashboard') }}"
                            class="sa-nav-link {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                            <span class="sa-nav-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="7" height="7" />
                                    <rect x="14" y="3" width="7" height="7" />
                                    <rect x="3" y="14" width="7" height="7" />
                                    <rect x="14" y="14" width="7" height="7" />
                                </svg>
                            </span>
                            Central SaaS Tenants
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('superadmin.license') }}" class="sa-nav-link {{ request()->routeIs('superadmin.license') ? 'active' : '' }}">
                            <span class="sa-nav-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    
                                    <rect x="3" y="3" width="7" height="7" />
                                    <rect x="14" y="3" width="7" height="7" />
                                    <rect x="3" y="14" width="7" height="7" />
                                    <rect x="14" y="14" width="7" height="7" />
                                </svg>
                            </span>
                            License Control
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('superadmin.settings') }}" class="sa-nav-link {{ request()->routeIs('superadmin.settings') ? 'active' : '' }}">
                            <span class="sa-nav-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="3" />
                                    
                                </svg>
                            </span>
                            System Settings
                        </a>
                    </li>
                </ul>
            </nav>

            {{-- Footer sidebar --}}
            <div class="sa-sidebar-footer">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="sa-logout-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                            <polyline points="16 17 21 12 16 7" />
                            <line x1="21" y1="12" x2="9" y2="12" />
                        </svg>
                        Cerrar Sesión Privada
                    </button>
                </form>

            </div>

        </aside>

        {{-- ══════════════════════════════════════════════
         CONTENIDO PRINCIPAL
    ══════════════════════════════════════════════ --}}
        <div class="sa-content">

            {{-- Header --}}
            <header class="sa-header">
                <div class="sa-header-left">
                    <h1 class="sa-header-title">Panel de Control</h1>
                </div>
                
                <div class="sa-header-right">
                    
                    <div class="sa-header-divider"></div>
                    <div class="sa-admin-badge">
                        <div>
                            <p class="sa-admin-label">Admin Portal</p>
                            <p class="sa-admin-version">V 1.0.0</p>
                        </div>
                        <div class="sa-admin-avatar">
                            {{ strtoupper(substr(session('usuario_nombre', 'S'), 0, 1)) }}
                        </div>
                    </div>
                </div>
            </header>

            

            {{-- Contenido de página --}}
            <main class="sa-main">
                @yield('content')
            </main>

        </div>
    </div>

    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: '¡Listo!',
                text: @json(session('success')),
                confirmButtonColor: '#7e22ce',
                timer: 3000,
                timerProgressBar: true
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Atención',
                text: @json(session('error')),
                confirmButtonColor: '#7e22ce'
            });
        </script>
    @endif

</body>

</html>
