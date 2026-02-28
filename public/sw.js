/**
 * Service Worker para Web Push Notifications.
 *
 * Regista-se no browser do utilizador e intercepta eventos push
 * enviados pelo servidor via VAPID. A payload chega em JSON com
 * o formato definido nas classes de Notification do Laravel.
 */
'use strict';

self.addEventListener('push', function (event) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    var data = {};
    if (event.data) {
        data = event.data.json();
    }

    var title = data.title || 'Lumina';
    var options = {
        body: data.body || 'Tens uma nova notificação.',
        icon: data.icon || '/images/lumina-icon-192.png',
        badge: data.badge || '/images/lumina-badge-72.png',
        tag: data.tag || 'lumina-notification',
        data: {
            url: data.action_url || data.url || '/'
        },
        vibrate: [100, 50, 100],
        requireInteraction: false
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    var url = event.notification.data && event.notification.data.url
        ? event.notification.data.url
        : '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (windowClients) {
            // Se já existe uma janela aberta com esse URL, foca nela
            for (var i = 0; i < windowClients.length; i++) {
                if (windowClients[i].url === url && 'focus' in windowClients[i]) {
                    return windowClients[i].focus();
                }
            }
            // Caso contrário, abre uma nova
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});
