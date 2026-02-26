/**
 * Service Worker — Regalos Purranque v2
 * Estrategia: Network First (prioriza contenido fresco)
 * Cachea CSS, JS e imágenes para uso offline
 */

var CACHE_NAME = 'regalos-v2-cache-v2';
var OFFLINE_URL = '/offline.html';

// Recursos esenciales para cachear al instalar
var PRECACHE_URLS = [
    '/',
    '/offline.html',
    '/assets/css/main.css',
    '/assets/js/app.js',
    '/manifest.json'
];

// ── Install ─────────────────────────────────────────────
self.addEventListener('install', function (event) {
    event.waitUntil(
        caches.open(CACHE_NAME).then(function (cache) {
            return cache.addAll(PRECACHE_URLS);
        }).then(function () {
            return self.skipWaiting();
        })
    );
});

// ── Activate ────────────────────────────────────────────
self.addEventListener('activate', function (event) {
    event.waitUntil(
        caches.keys().then(function (cacheNames) {
            return Promise.all(
                cacheNames
                    .filter(function (name) { return name !== CACHE_NAME; })
                    .map(function (name) { return caches.delete(name); })
            );
        }).then(function () {
            return self.clients.claim();
        })
    );
});

// ── Fetch ───────────────────────────────────────────────
self.addEventListener('fetch', function (event) {
    var request = event.request;

    // Solo interceptar GET
    if (request.method !== 'GET') return;

    // No interceptar requests al admin ni a APIs
    var url = new URL(request.url);
    if (url.pathname.startsWith('/admin') || url.pathname.startsWith('/api/')) return;

    // Estrategia: Network First
    event.respondWith(
        fetch(request)
            .then(function (response) {
                // Si la respuesta es válida, cachearla
                if (response && response.status === 200 && response.type === 'basic') {
                    var responseClone = response.clone();
                    caches.open(CACHE_NAME).then(function (cache) {
                        // Solo cachear assets estáticos y páginas HTML
                        if (isStaticAsset(url.pathname) || isHTMLPage(request)) {
                            cache.put(request, responseClone);
                        }
                    });
                }
                return response;
            })
            .catch(function () {
                // Si falla la red, buscar en cache
                return caches.match(request).then(function (cached) {
                    if (cached) return cached;

                    // Si es una página HTML, mostrar offline.html
                    if (isHTMLPage(request)) {
                        return caches.match(OFFLINE_URL);
                    }

                    return new Response('', { status: 503, statusText: 'Offline' });
                });
            })
    );
});

// ── Helpers ─────────────────────────────────────────────

function isStaticAsset(pathname) {
    return /\.(css|js|png|jpg|jpeg|webp|gif|svg|ico|woff2?)$/i.test(pathname);
}

function isHTMLPage(request) {
    return request.headers.get('accept') && request.headers.get('accept').includes('text/html');
}
