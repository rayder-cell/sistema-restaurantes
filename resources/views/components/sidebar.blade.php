<aside class="sidebar">
    {{-- Logo --}}
    <div class="sidebar-logo">
        @if (session('restaurante_logo'))
            <img src="{{ session('restaurante_logo') }}" alt="logo" class="w-8 h-8 rounded-md object-cover">
        @else
            <span>🍽</span>
        @endif
        <span class="sidebar-logo-text">{{ session('restaurante_nombre') }}</span>
    </div>

    {{-- Info usuario --}}
    <div class="sidebar-user">
        <p class="user-name">{{ session('usuario_nombre', 'Usuario') }}</p>
        <p class="user-rol">{{ session('usuario_rol', '') }}</p>
        @if (session('restaurante_nombre'))
            <p class="user-restaurante">{{ session('restaurante_nombre') }}</p>
        @endif
    </div>

    {{-- Navegación --}}
    <nav class="sidebar-nav">
        <ul class="space-y-1 px-2">

            {{-- Dashboard (todos los roles excepto mesero, cocinero y cajero) --}}
            @if (!in_array(session('usuario_rol'), ['mesero', 'cocinero', 'cajero']))
                <li>
                    <a href="{{ route('dashboard') }}"
                        class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-chart-line"></i> Dashboard
                    </a>
                </li>
            @endif

            {{-- ══════════════════════════════════════
                 SUPERADMIN
            ══════════════════════════════════════ --}}
            @if (session('usuario_rol') === 'superadmin')
                <li>
                    <p class="nav-section-label">Administración</p>
                </li>
                <li>
                    <a href="{{ route('superadmin.dashboard') }}"
                        class="nav-link {{ request()->routeIs('superadmin.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-store"></i> Restaurantes
                    </a>
                </li>
                <li>
                    <a href="{{ route('usuarios.index') }}"
                        class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-users"></i> Usuarios
                    </a>
                </li>
            @endif

            {{-- ══════════════════════════════════════
                 PROPIETARIO — mismo acceso que administrador
                 + Usuarios propios + Reportes
            ══════════════════════════════════════ --}}
            @if (session('usuario_rol') === 'propietario')
                <li>
                    <a href="{{ route('usuarios.index') }}"
                        class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-users"></i> Usuarios
                    </a>
                </li>

                <li>
                    <a href="{{ route('productos.index') }}"
                        class="nav-link {{ request()->routeIs('productos.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-bowl-food"></i> Menú
                    </a>
                </li>

                <li>
                    <a href="{{ route('mesas.index') }}"
                        class="nav-link {{ request()->routeIs('mesas.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-chair"></i> Mesas
                    </a>
                </li>
                <li>
                    <a href="{{ route('pedidos.index') }}"
                        class="nav-link {{ request()->routeIs('pedidos.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-clipboard-list"></i> Pedidos
                    </a>
                </li>
                <li>
                    <a href="{{ route('cocina.index') }}"
                        class="nav-link {{ request()->routeIs('cocina.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-kitchen-set"></i> Cocina
                    </a>
                </li>
                <li>
                    <a href="{{ route('caja.index') }}"
                        class="nav-link {{ request()->routeIs('caja.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-cash-register"></i> Caja
                    </a>
                </li>
                <li>
                    <a href="{{ route('reservas.index') }}"
                        class="nav-link {{ request()->routeIs('reservas.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-calendar-days"></i> Reservas
                    </a>
                </li>
                <li>
                    <a href="{{ route('insumos.index') }}"
                        class="nav-link {{ request()->routeIs('insumos.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-box"></i> Insumos
                    </a>
                </li>
                <li>
                    <a href="{{ route('proveedores.index') }}"
                        class="nav-link {{ request()->routeIs('proveedores.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-truck"></i> Proveedores
                    </a>
                </li>

                <li>
                    <a href="{{ route('reportes.ventas') }}"
                        class="nav-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-chart-pie"></i> Reportes
                    </a>
                </li>
            @endif

            {{-- ══════════════════════════════════════
                 ADMINISTRADOR
            ══════════════════════════════════════ --}}
            @if (session('usuario_rol') === 'administrador')
                <li>
                    <a href="{{ route('productos.index') }}"
                        class="nav-link {{ request()->routeIs('productos.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-bowl-food"></i> Menú
                    </a>
                </li>

                <li>
                    <a href="{{ route('mesas.index') }}"
                        class="nav-link {{ request()->routeIs('mesas.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-chair"></i> Mesas
                    </a>
                </li>
                <li>
                    <a href="{{ route('pedidos.index') }}"
                        class="nav-link {{ request()->routeIs('pedidos.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-clipboard-list"></i> Pedidos
                    </a>
                </li>
                <li>
                    <a href="{{ route('cocina.index') }}"
                        class="nav-link {{ request()->routeIs('cocina.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-kitchen-set"></i> Cocina
                    </a>
                </li>

                <li>
                    <a href="{{ route('caja.index') }}"
                        class="nav-link {{ request()->routeIs('caja.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-cash-register"></i> Caja
                    </a>
                </li>
                <li>
                    <a href="{{ route('reservas.index') }}"
                        class="nav-link {{ request()->routeIs('reservas.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-calendar-days"></i> Reservas
                    </a>
                </li>

                <li>
                    <a href="{{ route('insumos.index') }}"
                        class="nav-link {{ request()->routeIs('insumos.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-box"></i> Insumos
                    </a>
                </li>
                <li>
                    <a href="{{ route('proveedores.index') }}"
                        class="nav-link {{ request()->routeIs('proveedores.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-truck"></i> Proveedores
                    </a>
                </li>

                <li>
                    <a href="{{ route('reportes.ventas') }}"
                        class="nav-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-chart-pie"></i> Reportes
                    </a>
                </li>
            @endif

            {{-- ══════════════════════════════════════
                 MESERO
            ══════════════════════════════════════ --}}
            @if (session('usuario_rol') === 'mesero')
                <li>
                    <a href="{{ route('mesas.index') }}"
                        class="nav-link {{ request()->routeIs('mesas.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-chair"></i> Mesas
                    </a>
                </li>
                <li>
                    <a href="{{ route('pedidos.index') }}"
                        class="nav-link {{ request()->routeIs('pedidos.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-clipboard-list"></i> Pedidos
                    </a>
                </li>
            @endif

            {{-- ══════════════════════════════════════
                 COCINERO
            ══════════════════════════════════════ --}}
            @if (session('usuario_rol') === 'cocinero')
                <li>
                    <p class="nav-section-label">Cocina</p>
                </li>
                <li>
                    <a href="{{ route('cocina.index') }}"
                        class="nav-link {{ request()->routeIs('cocina.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-kitchen-set"></i> Panel de Cocina
                    </a>
                </li>
            @endif

            {{-- ══════════════════════════════════════
                 CAJERO
            ══════════════════════════════════════ --}}
            @if (session('usuario_rol') === 'cajero')
                <li>
                    <p class="nav-section-label">Caja</p>
                </li>
                <li>
                    <a href="{{ route('pedidos.index') }}"
                        class="nav-link {{ request()->routeIs('pedidos.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-clipboard-list"></i> Pedidos
                    </a>
                </li>
                <li>
                    <a href="{{ route('caja.index') }}"
                        class="nav-link {{ request()->routeIs('caja.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-cash-register"></i> Caja
                    </a>
                </li>
            @endif

        </ul>
    </nav>

    {{-- Logout --}}
    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-btn">
                <i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión
            </button>
        </form>
    </div>
</aside>
