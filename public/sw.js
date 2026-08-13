const CACHE_NAME = 'apel-guru-v2';
const ASSETS = [
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
  '/icons/icon-192.png',
  '/icons/icon-512.png'
];

// Install event
self.addEventListener('install', (e) => {
  e.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return Promise.allSettled(
        ASSETS.map(url => cache.add(url))
      );
    })
  );
  self.skipWaiting();
});

// Activate event
self.addEventListener('activate', (e) => {
  e.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.map((key) => {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        })
      );
    })
  );
  self.clients.claim();
});

// Fetch event
self.addEventListener('fetch', (e) => {
  // Bypass caching for POST requests, admin requests, and live forms
  if (e.request.method !== 'GET' || e.request.url.includes('/admin') || e.request.url.includes('/submit')) {
    return;
  }
  
  // Network-First Strategy
  e.respondWith(
    fetch(e.request)
      .then((response) => {
        // Cache new static files dynamically
        if (response.status === 200 && (e.request.url.includes('/icons/') || e.request.url.includes('font-awesome'))) {
          const responseClone = response.clone();
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(e.request, responseClone);
          });
        }
        return response;
      })
      .catch(() => {
        // Fallback to cache if offline
        return caches.match(e.request);
      })
  );
});
