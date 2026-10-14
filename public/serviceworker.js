const isLocal = self.location.hostname === '127.0.0.1' || self.location.hostname === 'localhost';
const staticCacheName = 'pwa-v' + new Date().getTime();

const filesToCache = [
  '/offline',
  '/css/app.css',
  '/js/app.js',
];

self.addEventListener('install', event => {
  self.skipWaiting();
  if (isLocal) {
    return;
  }

  event.waitUntil(
    caches.open(staticCacheName).then(cache => cache.addAll(filesToCache))
  );
});

self.addEventListener('activate', event => {
  event.waitUntil((async () => {
    const cacheNames = await caches.keys();
    await Promise.all(
      cacheNames
        .filter(cache => isLocal || (cache.startsWith('pwa-') && cache !== staticCacheName))
        .map(cache => caches.delete(cache))
    );

    if (isLocal) {
      await self.registration.unregister();
    }

    await self.clients.claim();
    const clients = await self.clients.matchAll({ type: 'window' });
    await Promise.all(clients.map(client => {
      const url = new URL(client.url);
      if (url.pathname === '/offline') {
        return client.navigate('/');
      }
      return undefined;
    }));
  })());
});

self.addEventListener('fetch', event => {
  if (isLocal || event.request.method !== 'GET') {
    return;
  }

  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request).catch(() => caches.match('/offline'))
    );
    return;
  }

  event.respondWith(
    caches.match(event.request).then(response => response || fetch(event.request))
  );
});
