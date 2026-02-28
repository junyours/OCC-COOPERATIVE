const CACHE_NAME = "occ-coop-v2";

const urlsToCache = [
  "/",
  "/index.php",
  "/assets/css/style.css",
  "/assets/js/script.js"
];

self.addEventListener("install", function(event) {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(function(cache) {
        return cache.addAll(urlsToCache);
      })
  );
});

self.addEventListener("fetch", function(event) {
  event.respondWith(
    caches.match(event.request)
      .then(function(response) {
        return response || fetch(event.request);
      })
  );
});
