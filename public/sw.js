/**
 * Service Worker — Lumina PWA.
 *
 * Responsabilidades:
 *  1. Cache-first para assets estáticos (CSS, JS, fontes, imagens).
 *  2. Network-first para páginas HTML (com fallback offline).
 *  3. Web Push Notifications (VAPID).
 *  4. Offline fallback page para navegação sem rede.
 */
'use strict';

var CACHE_VERSION = 'lumina-v1';
var OFFLINE_URL = '/offline';

// Assets estáticos para pre-cache no install
var PRECACHE_URLS = [
    '/',
    '/offline',
    '/manifest.json'
];

// ──────────────────────────────────────────────
// INSTALL — pre-cache dos assets essenciais
// ──────────────────────────────────────────────
self.addEventListener('install', function (event) {
    event.waitUntil(
        caches.open(CACHE_VERSION).then(function (cache) {
            return cache.addAll(PRECACHE_URLS);
        }).then(function () {
            return self.skipWaiting();
        })
    );
});

// ──────────────────────────────────────────────
// ACTIVATE — limpar caches antigas
// ──────────────────────────────────────────────
self.addEventListener('activate', function (event) {
    event.waitUntil(
        caches.keys().then(function (cacheNames) {
            return Promise.all(
                cacheNames.filter(function (name) {
                    return name !== CACHE_VERSION;
                }).map(function (name) {
                    return caches.delete(name);
                })
            );
        }).then(function () {
            return self.clients.claim();
        })
    );
});

// ──────────────────────────────────────────────
// FETCH — estratégia de cache por tipo de request
// ──────────────────────────────────────────────
self.addEventListener('fetch', function (event) {
    var request = event.request;

    // Ignorar requests não-GET (POST, PATCH, etc.)
    if (request.method !== 'GET') {
        return;
    }

    // Ignorar requests para APIs externas ou WebSocket
    if (!request.url.startsWith(self.location.origin)) {
        return;
    }

    // Assets estáticos (CSS, JS, fontes, imagens) → Cache-first
    if (isStaticAsset(request.url)) {
        event.respondWith(
            caches.match(request).then(function (cached) {
                if (cached) {
                    return cached;
                }
                return fetch(request).then(function (response) {
                    if (response.ok) {
                        var clone = response.clone();
                        caches.open(CACHE_VERSION).then(function (cache) {
                            cache.put(request, clone);
                        });
                    }
                    return response;
                });
            })
        );
        return;
    }

    // Páginas HTML → Network-first com fallback offline
    if (request.headers.get('Accept') && request.headers.get('Accept').includes('text/html')) {
        event.respondWith(
            fetch(request).then(function (response) {
                if (response.ok) {
                    var clone = response.clone();
                    caches.open(CACHE_VERSION).then(function (cache) {
                        cache.put(request, clone);
                    });
                }
                return response;
            }).catch(function () {
                return caches.match(request).then(function (cached) {
                    return cached || caches.match(OFFLINE_URL);
                });
            })
        );
        return;
    }
});

function isStaticAsset(url) {
    return /\.(css|js|woff2?|ttf|eot|svg|png|jpg|jpeg|gif|ico|webp)(\?.*)?$/.test(url)
        || url.includes('/build/');
}

// ──────────────────────────────────────────────
// PUSH NOTIFICATIONS
// ──────────────────────────────────────────────
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
            for (var i = 0; i < windowClients.length; i++) {
                if (windowClients[i].url === url && 'focus' in windowClients[i]) {
                    return windowClients[i].focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});
