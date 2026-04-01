/**
 * Service Worker — Lumina PWA.
 *
 * Responsabilidades:
 *  1. Cache-first para assets estáticos (CSS, JS, fontes, imagens).
 *  2. Network-first para páginas HTML (com fallback offline).
 *  3. Stale-while-revalidate para rotas dinâmicas frequentes (dashboard, perfil).
 *  4. Runtime cache com limite de entradas para evitar crescimento ilimitado.
 *  5. Web Push Notifications (VAPID).
 */
'use strict';

var CACHE_VERSION  = 'lumina-v2';
var RUNTIME_CACHE  = 'lumina-runtime-v2';
var OFFLINE_URL    = '/offline';

// Limite de entradas no cache de runtime — evita esgotamento de armazenamento.
var RUNTIME_MAX_ENTRIES = 60;

// Rotas que beneficiam de stale-while-revalidate (rápido + fresco).
var SWR_ROUTES = [
    '/dashboard',
    '/perfil',
    '/perfil/tendencias',
    '/mural',
    '/zona-calma',
];

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
// ACTIVATE — limpar caches de versões anteriores
// ──────────────────────────────────────────────
self.addEventListener('activate', function (event) {
    var currentCaches = [CACHE_VERSION, RUNTIME_CACHE];

    event.waitUntil(
        caches.keys().then(function (cacheNames) {
            return Promise.all(
                cacheNames
                    .filter(function (name) { return !currentCaches.includes(name); })
                    .map(function (name) { return caches.delete(name); })
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

    var url = new URL(request.url);

    // Rotas dinâmicas frequentes → Stale-while-revalidate (resposta imediata + atualização em background)
    if (isSWRRoute(url.pathname)) {
        event.respondWith(staleWhileRevalidate(request));
        return;
    }

    // Páginas HTML → Network-first com fallback para cache e página offline
    if (request.headers.get('Accept') && request.headers.get('Accept').includes('text/html')) {
        event.respondWith(
            fetch(request).then(function (response) {
                if (response.ok) {
                    putInRuntimeCache(request, response.clone());
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

function isSWRRoute(pathname) {
    return SWR_ROUTES.some(function (route) {
        return pathname === route || pathname.startsWith(route + '/');
    });
}

/**
 * Stale-while-revalidate: devolve o cache imediatamente e atualiza em background.
 * Reduz a latência percebida em rotas visitadas frequentemente.
 */
function staleWhileRevalidate(request) {
    return caches.open(RUNTIME_CACHE).then(function (cache) {
        return cache.match(request).then(function (cached) {
            var networkFetch = fetch(request).then(function (response) {
                if (response.ok) {
                    cache.put(request, response.clone());
                    enforceRuntimeCacheLimit(cache);
                }
                return response;
            });

            // Devolve o cache imediatamente, ou aguarda a rede se não houver cache.
            return cached || networkFetch;
        });
    });
}

/**
 * Guarda resposta no runtime cache e limita o número de entradas.
 */
function putInRuntimeCache(request, response) {
    caches.open(RUNTIME_CACHE).then(function (cache) {
        cache.put(request, response);
        enforceRuntimeCacheLimit(cache);
    });
}

/**
 * Remove as entradas mais antigas quando o limite é atingido.
 * Sem este controlo, o cache cresceria indefinidamente com cada visita.
 */
function enforceRuntimeCacheLimit(cache) {
    cache.keys().then(function (keys) {
        if (keys.length > RUNTIME_MAX_ENTRIES) {
            cache.delete(keys[0]);
        }
    });
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
