<header class="main-header">
    <h1 class="header-title">@yield('page-title', 'Dashboard')</h1>
    <div class="header-actions">
        <a href="#" class="header-notif">
            <span class="text-xl">🔔</span>
        </a>
        <span class="header-user">{{ session('usuario_nombre') }}</span>
    </div>
</header>
