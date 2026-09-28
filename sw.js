/* Service Worker — Mot du Jour CI */
const CACHE_NAME = 'mdj-ci-v1';
const BASE = '/Projets/Jeux/DevineMot/devine-motV1.0';
const PRECACHE = [
    BASE + '/index.php',
    BASE + '/style/main.css',
    BASE + '/js/main.js',
    BASE + '/manifest.json',
    BASE + '/assets/icons/icon-192.svg',
    BASE + '/assets/icons/icon-512.svg'
];

/* ── Installation : précharge les ressources statiques ── */
self.addEventListener('install', e => {
    e.waitUntil(
        caches.open(CACHE_NAME)
            .then(c => c.addAll(PRECACHE))
            .then(() => self.skipWaiting())
    );
});

/* ── Activation : supprime les anciens caches ── */
self.addEventListener('activate', e => {
    e.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

/* ── Fetch : Network-first pour PHP, cache-first pour statiques ── */
self.addEventListener('fetch', e => {
    const url = new URL(e.request.url);
    const isStatic = /\.(css|js|svg|png|ico|json)(\?|$)/.test(url.pathname);

    if (isStatic) {
        e.respondWith(
            caches.match(e.request).then(cached => cached || fetch(e.request))
        );
    } else {
        e.respondWith(
            fetch(e.request).catch(() => caches.match(e.request))
        );
    }
});

/* ── Push : reçoit la notification (sans payload) ── */
self.addEventListener('push', e => {
    e.waitUntil(
        fetch(BASE + '/php/notification-info.php')
            .then(r => r.json())
            .then(data => {
                const opts = {
                    body: data.body || 'Un nouveau mot t\'attend !',
                    icon: BASE + '/assets/icons/icon-192.svg',
                    badge: BASE + '/assets/icons/icon-192.svg',
                    tag: 'mdj-daily',
                    renotify: false,
                    data: { url: BASE + '/index.php' },
                    actions: [
                        { action: 'jouer', title: '🎮 Jouer maintenant' },
                        { action: 'plus-tard', title: '⏰ Plus tard' }
                    ]
                };
                return self.registration.showNotification(data.title || '🇨🇮 Mot du Jour CI', opts);
            })
            .catch(() => {
                return self.registration.showNotification('🇨🇮 Mot du Jour CI', {
                    body: 'Le mot du jour t\'attend ! Viens deviner 🔥',
                    icon: BASE + '/assets/icons/icon-192.svg',
                    tag: 'mdj-daily'
                });
            })
    );
});

/* ── Clic sur notification ── */
self.addEventListener('notificationclick', e => {
    e.notification.close();
    if (e.action === 'plus-tard') return;

    const target = (e.notification.data && e.notification.data.url)
        ? e.notification.data.url
        : BASE + '/index.php';

    e.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(list => {
            for (const c of list) {
                if (c.url.includes(BASE) && 'focus' in c) return c.focus();
            }
            return clients.openWindow(target);
        })
    );
});
