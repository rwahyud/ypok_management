const CACHE_NAME = 'ypok-v2'; // Updated cache version to force refresh
const urlsToCache = [
  '/',
  '/index.php',
  '/pages/dashboard.php',
  '/assets/css/style.css',
  '/assets/js/app.js'
];

self.addEventListener('install', event => {
  event.waitUntil(
    // Clear old cache
    caches.delete('ypok-v1').then(() => {
      return caches.open(CACHE_NAME)
        .then(cache => cache.addAll(urlsToCache));
    })
  );
  self.skipWaiting(); // Activate immediately
});

self.addEventListener('fetch', event => {
  // Network first for CSS and JS (always check for updates)
  if (event.request.url.includes('.css') || event.request.url.includes('.js')) {
    event.respondWith(
      fetch(event.request)
        .then(response => {
          const cache = caches.open(CACHE_NAME);
          cache.then(c => c.put(event.request, response.clone()));
          return response;
        })
        .catch(() => caches.match(event.request))
    );
  } else {
    // Cache first for other resources
    event.respondWith(
      caches.match(event.request)
        .then(response => response || fetch(event.request))
    );
  }
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  self.clients.claim();
});
