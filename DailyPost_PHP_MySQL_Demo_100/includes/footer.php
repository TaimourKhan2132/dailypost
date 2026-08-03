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

  // Already installed and running from the home screen.
  var standalone = window.matchMedia('(display-mode: standalone)').matches
                || window.navigator.standalone === true;

  var ua      = navigator.userAgent;
  var iOS     = /iphone|ipad|ipod/i.test(ua) || (/Macintosh/.test(ua) && 'ontouchend' in document);
  var android = /android/i.test(ua);

  // Dismissing used to be permanent, so closing the bar once meant it
  // never came back on any device. It now goes quiet for two weeks.
  var KEY = 'dp-install-snooze-until';
  function snoozed() { return Date.now() < parseInt(localStorage.getItem(KEY) || '0', 10); }
  function snooze(days) { localStorage.setItem(KEY, String(Date.now() + days * 86400000)); }
  localStorage.removeItem('dp-install-dismissed');  // clear the old permanent flag

  // How to install by hand, per browser. Safari can install web apps
  // but gives pages no way to trigger it, so on iOS this is the only
  // honest answer rather than a button that silently does nothing.
  function manualSteps() {
    if (iOS)     return 'Tap the Share button at the bottom of Safari, then choose "Add to Home Screen".';
    if (android) return 'Open the ⋮ menu at the top right, then tap "Install app" or "Add to Home screen".';
    return 'Look for the install icon at the right of the address bar, or open the ⋮ menu and choose "Install DailyPost".';
  }

  function showBar(force) {
    if (standalone) return;
    if (!force && snoozed()) return;
    bar.hidden = false;
  }

  // Chrome and Edge fire this only when the site genuinely qualifies,
  // and only after you have actually used the site for a bit.
  window.addEventListener('beforeinstallprompt', function (e) {
    e.preventDefault();
    deferred = e;
    btn.hidden = false;
    hint.textContent = 'Add it to your home screen — no app store needed.';
    showBar(false);
  });

  function install(e) {
    if (e) e.preventDefault();

    if (deferred) {
      deferred.prompt();
      deferred.userChoice.then(function () {
        deferred = null;
        bar.hidden = true;
      });
      return;
    }

    // No install event available - iOS always, and Chrome until it
    // decides you have engaged enough. Show the instructions instead,
    // ignoring any snooze, because this was an explicit request.
    btn.hidden = true;
    hint.textContent = manualSteps();
    showBar(true);
    bar.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  btn.addEventListener('click', install);

  // The footer link is always available and always does something.
  // Previously it was hidden until an install event arrived, which on
  // iPhone never happens - so it was either invisible or dead.
  if (link) {
    link.hidden = false;
    link.addEventListener('click', install);
  }

  dismiss.addEventListener('click', function () {
    bar.hidden = true;
    snooze(14);
  });

  window.addEventListener('appinstalled', function () {
    bar.hidden = true;
    if (link) link.hidden = true;
    snooze(3650);
  });

  // iPhone: offer the bar straight away, since no event is coming.
  if (iOS && !standalone) {
    btn.hidden = true;
    hint.textContent = manualSteps();
    showBar(false);
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
