<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MIKHUNAWASI')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/assets/css/components/sidebar.css', 'resources/assets/css/pages/dashboard.css', 'resources/assets/css/pages/crud.css', 'resources/assets/css/pages/superadmin.css', 'resources/assets/css/pages/menu.css', 'resources/assets/css/pages/pedidos.css', 'resources/assets/js/app.js'])
    @stack('styles')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <div class="app-layout">

        {{-- Sidebar como componente --}}
        <x-sidebar />

        {{-- Contenido principal --}}
        <div class="app-content">

            {{-- Contenido de cada página --}}
            <main class="app-main">
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

    @if (session('usuario_rol') === 'mesero')
        <script>
            (function() {
                if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                    console.warn('Este navegador no soporta notificaciones push.');
                    return;
                }

                function urlBase64ToUint8Array(base64String) {
                    const padding = '='.repeat((4 - base64String.length % 4) % 4);
                    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
                    const rawData = window.atob(base64);
                    const outputArray = new Uint8Array(rawData.length);
                    for (let i = 0; i < rawData.length; ++i) {
                        outputArray[i] = rawData.charCodeAt(i);
                    }
                    return outputArray;
                }
                async function suscribirPush() {
                    try {
                        const registration = await navigator.serviceWorker.register('/service-worker.js');
                        const permiso = await Notification.requestPermission();
                        if (permiso !== 'granted') {
                            console.warn('Permiso de notificaciones no concedido.');
                            return;
                        }
                        const existente = await registration.pushManager.getSubscription();
                        if (existente) return; // ya suscrito, no repetir
                        const {
                            key
                        } = await fetch('{{ route('push.vapid-key') }}').then(r => r.json());
                        const subscription = await registration.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: urlBase64ToUint8Array(key),
                        });
                        await fetch('{{ route('push.subscribe') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify(subscription),
                        });
                    } catch (err) {
                        console.error('Error al suscribir notificaciones push:', err);
                    }
                }
                suscribirPush();
            })();
        </script>
    @endif

</body>

</html>
