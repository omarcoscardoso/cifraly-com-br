/**
 * Cifraly PWA Service Worker
 * Focus: Mobile performance, offline resilience, and future extensible sync/push capabilities.
 */

const CACHE_VERSION = 'cifraly-v1.0.7';
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const RUNTIME_CACHE = `${CACHE_VERSION}-runtime`;
const STAGE_CACHE = `${CACHE_VERSION}-stage`;
const OFFLINE_FALLBACK_URL = '/offline.html';

// Critical core assets to precache on install
const PRECACHE_ASSETS = [
    OFFLINE_FALLBACK_URL,
    '/manifest.webmanifest',
    '/manifest.json',
    '/icons/icon.svg',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png',
    '/icons/icon-maskable-192x192.png',
    '/icons/icon-maskable-512x512.png',
    '/apple-touch-icon.png',
    '/favicon.svg',
    '/favicon.ico',
    '/js/tone.js'
];

// Dynamic and mutation patterns that MUST NOT be cached (mutations, livewire RPC updates, auth, telescope)
const EXCLUDED_PATTERNS = [
    /\/livewire\/(update|upload-file|preview-file)(\/|$)/,
    /\/api(\/|$)/,
    /\/login(\/|$)/,
    /\/join(\/|$)/,
    /\/r(\/|$)/,
    /\/telescope(\/|$)/,
    /\/sanctum(\/|$)/
];

// -------------------------------------------------------------
// 1. Install Event - Precache shell with cache reload & dynamic Vite manifest
// -------------------------------------------------------------
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(async (cache) => {
                // 1. Precache fixed core assets
                await Promise.all(
                    PRECACHE_ASSETS.map((url) => {
                        return fetch(new Request(url, { cache: 'reload' }))
                            .then((response) => {
                                if (response && response.ok) {
                                    return cache.put(url, response);
                                }
                            })
                            .catch((err) => console.warn('[SW] Precache failed for:', url, err));
                    })
                );

                // 2. Discover and precache current Vite build assets dynamically (CSS, JS, Fonts)
                try {
                    const manifestRes = await fetch(new Request('/build/manifest.json', { cache: 'reload' }));
                    if (manifestRes && manifestRes.ok) {
                        const manifest = await manifestRes.clone().json();
                        await cache.put('/build/manifest.json', manifestRes);

                        const assetUrls = Object.values(manifest)
                            .map((item) => (item.file ? `/build/${item.file}` : null))
                            .filter(Boolean);

                        await Promise.all(
                            assetUrls.map((url) =>
                                fetch(new Request(url, { cache: 'reload' }))
                                    .then((res) => {
                                        if (res && res.ok) {
                                            return cache.put(url, res);
                                        }
                                    })
                                    .catch((err) => console.warn('[SW] Build asset precache failed:', url, err))
                            )
                        );
                    }
                } catch (err) {
                    console.warn('[SW] Failed to load /build/manifest.json for precache:', err);
                }
            })
            .then(() => self.skipWaiting())
    );
});

// -------------------------------------------------------------
// 2. Activate Event - Clean up stale caches
// -------------------------------------------------------------
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.filter((key) => key !== STATIC_CACHE && key !== RUNTIME_CACHE && key !== STAGE_CACHE)
                    .map((key) => caches.delete(key))
            );
        }).then(() => self.clients.claim())
    );
});

// -------------------------------------------------------------
// 3. Fetch Event - Strategy Routing
// -------------------------------------------------------------
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Only handle http and https requests (ignore chrome-extension:, moz-extension:, etc.)
    if (!url.protocol.startsWith('http')) {
        return;
    }

    // Only handle GET requests and same-origin / CDN requests
    if (request.method !== 'GET') {
        return;
    }

    // Check if the request is for a stage screen (chord sheet) e.g. /app/{org}/events/{id}/stage or /app/{org}/songs/{id}/stage
    const isStageRoute = /\/app\/[^\/]+\/(events|songs)\/[^\/]+\/stage/.test(url.pathname);

    // A. Stage Views / Chord Sheets: Network-first with dedicated STAGE_CACHE fallback
    if (isStageRoute) {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    // Only cache successful responses
                    if (response && response.status === 200 && response.type === 'basic') {
                        const copy = response.clone();
                        caches.open(STAGE_CACHE).then((cache) => cache.put(request, copy));
                    }
                    return response;
                })
                .catch(async () => {
                    const cachedResponse = await caches.match(request);
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    const fallback = await caches.match(OFFLINE_FALLBACK_URL);
                    return fallback || new Response('Cifra indisponível offline.', {
                        status: 503,
                        headers: { 'Content-Type': 'text/plain; charset=utf-8' }
                    });
                })
        );
        return;
    }

    // B. General HTML Navigation Requests: Network-First with Offline Fallback
    // Lets network handle standard HTTP redirects (302/301), auth session cookies,
    // and Filament/Livewire lifecycle natively without opaque-redirect errors.
    // If network fails (device offline), gracefully responds with the precached offline.html.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    // Armazena páginas HTML navegadas com sucesso para consulta offline
                    if (response && response.status === 200 && response.type === 'basic') {
                        const copy = response.clone();
                        caches.open(RUNTIME_CACHE).then((cache) => cache.put(request, copy));
                    }
                    return response;
                })
                .catch(async () => {
                    const cachedResponse = await caches.match(request);
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    const fallback = await caches.match(OFFLINE_FALLBACK_URL);
                    return fallback || new Response('Cifraly está offline. Verifique sua conexão.', {
                        status: 503,
                        headers: { 'Content-Type': 'text/html; charset=utf-8' }
                    });
                })
        );
        return;
    }

    // Never cache excluded patterns for non-navigation requests (Livewire updates, API calls, auth mutations)
    if (EXCLUDED_PATTERNS.some((pattern) => pattern.test(url.pathname))) {
        return;
    }


    // B. Static Assets (CSS, JS, Fonts, Images, SVG): Stale-While-Revalidate
    const isStaticAsset = 
        url.pathname.startsWith('/build/') ||
        url.pathname.startsWith('/css/') ||
        url.pathname.startsWith('/js/') ||
        url.pathname.startsWith('/icons/') ||
        url.pathname.startsWith('/fonts/') ||
        url.pathname.startsWith('/images/') ||
        /\.(css|js|woff2?|ttf|png|jpe?g|svg|ico|webp)$/i.test(url.pathname);

    if (isStaticAsset) {
        event.respondWith(
            caches.match(request, { ignoreSearch: true }).then((cachedResponse) => {
                const fetchPromise = fetch(request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const copy = networkResponse.clone();
                        caches.open(RUNTIME_CACHE)
                            .then((cache) => cache.put(request, copy))
                            .catch(() => {});
                    }
                    return networkResponse;
                }).catch(() => cachedResponse);

                return cachedResponse || fetchPromise;
            })
        );
        return;
    }
});

// -------------------------------------------------------------
// 4. Background Sync API (Offline actions queue)
// -------------------------------------------------------------
self.addEventListener('sync', (event) => {
    if (event.tag === 'cifraly-sync-rosters') {
        event.waitUntil(handleRosterSync());
    } else if (event.tag === 'cifraly-sync-favorites') {
        event.waitUntil(handleFavoritesSync());
    }
});

async function handleRosterSync() {
    // Placeholder handler for future IndexedDB offline queued confirmations
    console.log('[SW] Background sync triggered: cifraly-sync-rosters');
}

async function handleFavoritesSync() {
    // Placeholder handler for future offline favorites/songs sync
    console.log('[SW] Background sync triggered: cifraly-sync-favorites');
}

// -------------------------------------------------------------
// 4.1. Periodic Background Sync API
// -------------------------------------------------------------
self.addEventListener('periodicsync', (event) => {
    if (event.tag === 'cifraly-periodic-sync') {
        event.waitUntil(handlePeriodicSync());
    }
});

async function handlePeriodicSync() {
    console.log('[SW] Periodic background sync triggered: cifraly-periodic-sync');
    try {
        const cache = await caches.open(STATIC_CACHE);
        await Promise.all(
            PRECACHE_ASSETS.map((url) =>
                fetch(new Request(url, { cache: 'reload' }))
                    .then((res) => {
                        if (res && res.ok) {
                            return cache.put(url, res);
                        }
                    })
                    .catch(() => {})
            )
        );
    } catch (err) {
        console.warn('[SW] Periodic sync error:', err);
    }
}

// -------------------------------------------------------------
// 5. Push Notifications API (Web Push)
// -------------------------------------------------------------
self.addEventListener('push', (event) => {
    let payload = {
        title: 'Cifraly',
        body: 'Você possui uma nova atualização na sua escala.',
        icon: '/icons/icon-192x192.png',
        badge: '/icons/icon-192x192.png',
        data: { url: '/app' }
    };

    if (event.data) {
        try {
            const json = event.data.json();
            payload = { ...payload, ...json };
        } catch (e) {
            payload.body = event.data.text();
        }
    }

    const options = {
        body: payload.body,
        icon: payload.icon || '/icons/icon-192x192.png',
        badge: payload.badge || '/icons/icon-192x192.png',
        vibrate: [100, 50, 100],
        data: payload.data || { url: '/app' },
        actions: [
            { action: 'open', title: 'Abrir Aplicativo' },
            { action: 'dismiss', title: 'Fechar' }
        ]
    };

    event.waitUntil(self.registration.showNotification(payload.title, options));
});

// -------------------------------------------------------------
// 6. Notification Click Handler
// -------------------------------------------------------------
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    if (event.action === 'dismiss') {
        return;
    }

    const targetUrl = event.notification.data?.url || '/app';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (const client of clientList) {
                if (client.url.includes(targetUrl) && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});

// -------------------------------------------------------------
// 7. Message Handler (Client commands like skipWaiting)
// -------------------------------------------------------------
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});
