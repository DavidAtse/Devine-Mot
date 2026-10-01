/* Service Worker – iMots CI */
const CACHE_NAME = 'imots-ci-v3';

/* Installation : on ne précache RIEN — les pages PHP sont dynamiques */
self.addEventListener('install', e => {
    e.waitUntil(self.skipWaiting());
});

/* Activation : supprime TOUS les anciens caches (mdj-ci-v1, mdj-ci-v2, etc.) */
self.addEventListener('activate', e => {
    e.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.map(k => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

/* Fetch : Network-first pour TOUT (PHP + CSS + JS)
   → Le téléphone va toujours chercher la dernière version sur le serveur.
   → En cas d'absence de réseau, on répond avec le cache si disponible. */
self.addEventListener('fetch', e => {
    // Ne pas intercepter les requêtes non-GET (POST vers php/jouer.php etc.)
    if (e.request.method !== 'GET') return;

    e.respondWith(
        fetch(e.request)
            .then(response => {
                // Mettre en cache les réponses statiques (CSS, JS, PNG)
                const url = new URL(e.request.url);
                const isStatic = /\.(css|js|png|ico|json|jpg|svg)(\?|$)/.test(url.pathname);
                if (isStatic && response.ok) {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(c => c.put(e.request, clone));
                }
                return response;
            })
            .catch(() => caches.match(e.request))
    );
});

/* Push : reçoit la notification */
self.addEventListener('push', e => {
    e.waitUntil(
        fetch('/php/notification-info.php')
            .then(r => r.json())
            .then(data => {
                const opts = {
                    body: data.body || 'Un nouveau mot t\'attend !',
                    icon: '/assets/icons/icon-192.png',
                    badge: '/assets/icons/icon-76.png',
                    tag: 'imots-daily',
                    renotify: false,
                    data: { url: '/' },
                    actions: [
                        { action: 'jouer', title: 'Jouer maintenant' },
                        { action: 'plus-tard', title: 'Plus tard' }
                    ]
                };
                return self.registration.showNotification(data.title || 'iMots CI 🇨🇮', opts);
            })
            .catch(() => {
                return self.registration.showNotification('iMots CI 🇨🇮', {
                    body: 'Le mot du jour t\'attend ! Viens deviner 🔥',
                    icon: '/assets/icons/icon-192.png',
                    tag: 'imots-daily'
                });
            })
    );
});

/* Clic sur notification */
self.addEventListener('notificationclick', e => {
    e.notification.close();
    if (e.action === 'plus-tard') return;

    const target = (e.notification.data && e.notification.data.url)
        ? e.notification.data.url
        : '/';

    e.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(list => {
            for (const c of list) {
                if ('focus' in c) return c.focus();
            }
            return clients.openWindow(target);
        })
    );
});
