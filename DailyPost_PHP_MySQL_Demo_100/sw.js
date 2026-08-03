/* ---------------------------------------------------------------
   DailyPost service worker.

   Deliberately conservative. A news site that serves stale pages
   from a cache is worse than one that simply needs a connection, so
   pages are always fetched from the network first and the cache is
   only a fallback for when there is none.

   Static files (stylesheet, icons) go the other way round: they are
   served from cache immediately and refreshed in the background,
   because they change rarely and that is what makes the app feel
   instant.
   --------------------------------------------------------------- */

const VERSION      = 'dailypost-v1';
const STATIC_CACHE = VERSION + '-static';
const PAGE_CACHE   = VERSION + '-pages';

// Relative to this file, which sits at the site root - so this works
// whether the site is at / or at /dailypost/.
const OFFLINE_URL = 'offline.php';

const PRECACHE = [
  OFFLINE_URL,
  'assets/css/dailypost.css',
  'assets/icon-192.png',
  'assets/favicon-32.png'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(STATIC_CACHE)
      // addAll fails the whole install if any single file 404s, so
      // each is added on its own and failures are ignored.
      .then(cache => Promise.all(PRECACHE.map(url =>
        cache.add(url).catch(() => null)
      )))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys()
      .then(names => Promise.all(
        names.filter(n => !n.startsWith(VERSION)).map(n => caches.delete(n))
      ))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', event => {
  const req = event.request;

  // Only GET, and only our own origin. Never touch a WhatsApp or
  // Facebook share, and never cache a form submission.
  if (req.method !== 'GET' || new URL(req.url).origin !== self.location.origin) {
    return;
  }

  // Admin pages must never be cached - a stale dashboard would show
  // submissions that have already been dealt with.
  if (req.url.includes('/admin/')) {
    return;
  }

  const isStatic = /\.(css|js|png|jpg|jpeg|svg|ico|webp|woff2?)$/i.test(new URL(req.url).pathname);

  if (isStatic) {
    // Cache first, then refresh quietly in the background.
    event.respondWith(
      caches.match(req).then(hit => {
        const fetching = fetch(req).then(res => {
          if (res && res.ok) {
            const copy = res.clone();
            caches.open(STATIC_CACHE).then(c => c.put(req, copy));
          }
          return res;
        }).catch(() => hit);
        return hit || fetching;
      })
    );
    return;
  }

  // Pages: network first, fall back to the last copy we saw, and
  // failing that the offline page.
  event.respondWith(
    fetch(req)
      .then(res => {
        if (res && res.ok) {
          const copy = res.clone();
          caches.open(PAGE_CACHE).then(c => c.put(req, copy));
        }
        return res;
      })
      .catch(() =>
        caches.match(req).then(hit =>
          hit || caches.match(OFFLINE_URL).then(off => off || new Response(
            'You are offline.',
            { status: 503, headers: { 'Content-Type': 'text/plain' } }
          ))
        )
      )
  );
});
