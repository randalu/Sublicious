const CACHE_NAME = 'sublicious-v1';
const OFFLINE_URL = '/offline.html';

const STATIC_ASSETS = [
    '/offline.html',
];

// Install — cache offline fallback
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(STATIC_ASSETS))
    );
    self.skipWaiting();
});

// Activate — clean old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
        )
    );
    self.clients.claim();
});

// Fetch strategy
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    // Never intercept Livewire, API, or admin requests
    if (
        url.pathname.startsWith('/livewire') ||
        url.pathname.startsWith('/api/') ||
        url.pathname.startsWith('/admin') ||
        event.request.method !== 'GET'
    ) {
        return;
    }

    // Static assets — cache first
    if (url.pathname.match(/\.(css|js|woff2?|png|jpg|svg|ico)$/)) {
        event.respondWith(
            caches.match(event.request).then(cached =>
                cached || fetch(event.request).then(response => {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                    return response;
                })
            )
        );
        return;
    }

    // Navigation requests — network first, fallback to offline
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(() => caches.match(OFFLINE_URL))
        );
    }
});

// Push notifications
self.addEventListener('push', (event) => {
    if (!event.data) return;

    let data = {};
    try { data = event.data.json(); } catch(e) { data = { title: 'Sublicious', body: event.data.text() }; }

    event.waitUntil(
        self.registration.showNotification(data.title || 'Sublicious', {
            body: data.body || '',
            icon: '/icons/icon-192.png',
            badge: '/icons/icon-192.png',
            data: { url: data.url || '/app/dashboard' },
            tag: data.tag || 'sublicious-notification',
            renotify: true,
        })
    );
});

// Notification click
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const url = event.notification.data?.url || '/app/dashboard';
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(windows => {
            const existing = windows.find(w => w.url.includes(url));
            if (existing) return existing.focus();
            return clients.openWindow(url);
        })
    );
});
