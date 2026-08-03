<?php // Floating Write button. Phones only, and not on the write
      // page itself where it would just point at the current page. ?>
<?php if (($active_nav ?? '') !== 'write'): ?>
  <a class="fab" href="<?= e(base_path()) ?>write.php" aria-label="Write a story" title="Write a story">
    <svg viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
  </a>
<?php endif; ?>

<footer class="site-footer">
  <div class="wrap">
    <div class="cols">
      <div>
        <div class="brand">Daily<span>Post</span></div>
        <p style="margin:10px 0 0;max-width:320px">
          A simple place for good stories. Anyone can write, everyone can read.
        </p>
      </div>
      <div>
        <h4>Explore</h4>
        <a href="<?= e(base_path()) ?>index.php">Latest Stories</a>
        <a href="<?= e(base_path()) ?>search.php">Search</a>
        <a href="<?= e(base_path()) ?>index.php#picks">Editor's Picks</a>
      </div>
      <div>
        <h4>Take Part</h4>
        <a href="<?= e(base_path()) ?>write.php">Write a Story</a>
        <a href="<?= e(base_path()) ?>index.php#newsletter">Newsletter</a>
        <a href="<?= e(base_path()) ?>about.php">About</a>
        <?php // Shown only once the browser confirms the site can
              // actually be installed. Offering it otherwise would
              // be a button that does nothing. ?>
        <a href="#" id="installLink" hidden>Install app</a>
      </div>
      <div>
        <h4>Categories</h4>
        <?php
        // Only shown if the page already loaded the category list.
        foreach (array_slice($categories ?? [], 0, 5) as $c):
        ?>
          <a href="<?= e(base_path()) ?>search.php?category=<?= e($c['slug']) ?>"><?= e($c['name']) ?></a>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="base">
      <span>© <?= date('Y') ?> DailyPost. All rights reserved.</span>
      <span>Stories Worth Sharing.</span>
      <?php // Editors only. Kept down here rather than in the header
            // so readers are not invited to create an account that
            // does not exist. ?>
      <a class="editor-link" href="<?= e(base_path()) ?>admin/login.php">Editor sign in</a>
    </div>
  </div>
</footer>

<?php // --- INSTALL PROMPT --------------------------------------
      // Hidden until the browser says the site is installable, or
      // until we detect an iPhone, where Safari has no install API
      // and the user has to be told where the button is. ?>
<div class="install-bar" id="installBar" hidden>
  <img src="<?= e(base_path()) ?>assets/icon-192.png" alt="">
  <div class="install-text">
    <b>Install DailyPost</b>
    <span id="installHint">Add it to your home screen — no app store needed.</span>
  </div>
  <button class="btn" id="installBtn" type="button">Install</button>
  <button class="install-close" id="installDismiss" type="button" aria-label="Not now">&times;</button>
</div>

<script>
(function () {
  var bar     = document.getElementById('installBar');
  var btn     = document.getElementById('installBtn');
  var link    = document.getElementById('installLink');
  var hint    = document.getElementById('installHint');
  var dismiss = document.getElementById('installDismiss');
  var deferred = null;

  // Already installed and running from the home screen - nothing to offer.
  var standalone = window.matchMedia('(display-mode: standalone)').matches
                || window.navigator.standalone === true;

  function hidden() { return localStorage.getItem('dp-install-dismissed') === '1'; }

  function showBar() { if (!standalone && !hidden()) bar.hidden = false; }

  // Chrome and Edge fire this only when the site genuinely qualifies:
  // served over HTTPS, has a manifest, and has a service worker.
  window.addEventListener('beforeinstallprompt', function (e) {
    e.preventDefault();
    deferred = e;
    if (link) link.hidden = false;
    showBar();
  });

  function install() {
    if (!deferred) return;
    deferred.prompt();
    deferred.userChoice.then(function () {
      deferred = null;
      bar.hidden = true;
      if (link) link.hidden = true;
    });
  }

  btn.addEventListener('click', install);
  if (link) link.addEventListener('click', function (e) { e.preventDefault(); install(); });

  dismiss.addEventListener('click', function () {
    bar.hidden = true;
    localStorage.setItem('dp-install-dismissed', '1');
  });

  window.addEventListener('appinstalled', function () {
    bar.hidden = true;
    if (link) link.hidden = true;
    localStorage.setItem('dp-install-dismissed', '1');
  });

  // iPhone and iPad: Safari supports installing but exposes no API
  // for it, so the only honest thing is to say where the button is.
  var iOS = /iphone|ipad|ipod/i.test(navigator.userAgent);
  if (iOS && !standalone && !hidden()) {
    btn.hidden = true;
    hint.textContent = 'Tap the Share button, then "Add to Home Screen".';
    bar.hidden = false;
    if (link) link.hidden = false;
  }
})();

// --- SERVICE WORKER -------------------------------------------
// Gives the site an offline page and makes repeat visits faster.
// Registration is deliberately after load so it never competes with
// the page itself for bandwidth.
if ('serviceWorker' in navigator) {
  window.addEventListener('load', function () {
    navigator.serviceWorker.register('<?= e(base_path()) ?>sw.js')
      .catch(function (err) { console.log('Service worker not registered:', err); });
  });
}

// Theme toggle. The initial value was already applied in <head>;
// this only handles the click and remembers the choice.
document.getElementById('themeToggle').addEventListener('click', function () {
  var root = document.documentElement;
  var dark = root.getAttribute('data-theme') === 'dark';
  if (dark) { root.removeAttribute('data-theme'); localStorage.setItem('dp-theme', 'light'); }
  else      { root.setAttribute('data-theme', 'dark'); localStorage.setItem('dp-theme', 'dark'); }
});
</script>
</body>
</html>
