self.addEventListener('push', function (event) {
    let data = {};
    try {
        data = event.data ? event.data.json() : {};
    } catch (e) {
        data = { title: 'MIKHUNAWASI', body: event.data ? event.data.text() : '' };
    }

    const title = data.title || 'MIKHUNAWASI';
    const options = {
        body: data.body || '',
        icon: '/storage/restaurantes/V1P2r3sq77BbfVwhqBMXDz9vudeP5cEHqUoVsMZb.png',
        badge: '/storage/restaurantes/V1P2r3sq77BbfVwhqBMXDz9vudeP5cEHqUoVsMZb.png',
        data: data.data || {},
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    const url = event.notification.data?.url || '/mesas';
    event.waitUntil(clients.openWindow(url));
});