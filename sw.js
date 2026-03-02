const CACHE_NAME = 'ypok-v3';
const urlsToCache = [
  '/ypok_management/ypok_management/',
  '/ypok_management/ypok_management/index.php',
  '/ypok_management/ypok_management/dashboard.php',
  '/ypok_management/ypok_management/assets/css/style.css',
  '/ypok_management/ypok_management/assets/js/app.js',
  '/ypok_management/ypok_management/assets/icons/icon-192x192.jpg',
  '/ypok_management/ypok_management/assets/icons/icon-512x512.jpg'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(urlsToCache))
  );
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
});

self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => response || fetch(event.request))
  );
});
