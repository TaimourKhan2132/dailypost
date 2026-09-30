<?php
require 'config/db.php';

dp_session_start();

// --- CATEGORIES --------------------------------------------------
// Keyed by slug so a story can look up its badge colour and its
// fallback photo in one step.
$categories = load_categories($pdo);

// --- THE FOUR HOMEPAGE QUERIES -----------------------------------
// Each one is small and indexed. This is why migration 001 added
// idx_featured, idx_picks and idx_mostread.

$featured = $pdo->query(
    "SELECT * FROM stories
     WHERE status = 'published' AND featured = 1
     ORDER BY published_at DESC LIMIT 5"
)->fetchAll();

$mostRead = $pdo->query(
    "SELECT * FROM stories
     WHERE status = 'published'
     ORDER BY views DESC, published_at DESC LIMIT 5"
)->fetchAll();

$picks = $pdo->query(
    "SELECT * FROM stories
     WHERE status = 'published' AND editors_pick = 1
     ORDER BY published_at DESC LIMIT 4"
)->fetchAll();

// Videos for the Watch slideshow. Wrapped because the table only
// exists once migration 006 has been imported - a missing table
// should mean no Watch section, not a broken homepage.
$videos = [];
try {
    $videos = $pdo->query(
        "SELECT * FROM videos WHERE status = 'published' ORDER BY sort_order, id LIMIT 30"
    )->fetchAll();
} catch (PDOException $e) {
    // migration 006 not applied yet
}

// Latest, with paging.
$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$offset  = ($page - 1) * $perPage;

$total = (int) $pdo->query("SELECT COUNT(*) FROM stories WHERE status = 'published'")->fetchColumn();
$pages = max(1, (int) ceil($total / $perPage));

$q = $pdo->prepare(
    "SELECT * FROM stories
     WHERE status = 'published'
     ORDER BY published_at DESC, id DESC
     LIMIT :lim OFFSET :off"
);
$q->bindValue('lim', $perPage, PDO::PARAM_INT);
$q->bindValue('off', $offset,  PDO::PARAM_INT);
$q->execute();
$latest = $q->fetchAll();

// If nothing has been flagged as featured yet, fall back to the
// newest stories so the carousel is never empty.
if (!$featured) {
    $featured = array_slice($latest, 0, 5);
}

$active_nav = 'read';
$og_image   = $featured ? story_image($featured[0], $categories) : null;

require 'includes/header.php';
?>

<div class="wrap">
  <div class="home-top">

    <!-- ---------- HERO CAROUSEL ---------- -->
    <section class="hero" id="hero">
      <span class="featured-flag">★ Featured Story</span>

      <?php foreach ($featured as $i => $s):
        $cat = $categories[$s['category']] ?? $categories['general']; ?>
        <article class="slide <?= $i === 0 ? 'on' : '' ?>" data-slide="<?= $i ?>">
          <img src="<?= e(story_image($s, $categories)) ?>" alt=""
               onerror="this.src='<?= e($cat['default_image']) ?>'">
          <div class="shade"></div>
          <div class="copy">
            <span class="badge" style="position:static;display:inline-block;background:<?= e($cat['badge_color']) ?>">
              <?= e($cat['name']) ?>
            </span>
            <h2><a href="<?= e(story_url($s)) ?>"><?= e($s['title']) ?></a></h2>
            <p><?= e(mb_strimwidth($s['excerpt'] ?: $s['body'], 0, 150, '…')) ?></p>
            <div class="byline">
              <span class="who">
                <span class="avatar"><?= e(mb_strtoupper(mb_substr($s['author'], 0, 1))) ?></span>
                <?= e($s['author']) ?>
              </span>
              <span class="bit">
                <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 11h18"/></svg>
                <?= $s['published_at'] ? date('M j, Y', strtotime($s['published_at'])) : '' ?>
              </span>
              <span class="bit">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                <?= read_time($s['body']) ?> min read
              </span>
            </div>
          </div>
        </article>
      <?php endforeach; ?>

      <?php if (count($featured) > 1): ?>
        <button class="hero-nav prev" id="heroPrev" aria-label="Previous story">‹</button>
        <button class="hero-nav next" id="heroNext" aria-label="Next story">›</button>
        <div class="hero-dots" id="heroDots">
          <?php foreach ($featured as $i => $s): ?>
            <button class="<?= $i === 0 ? 'on' : '' ?>" data-go="<?= $i ?>"
                    aria-label="Story <?= $i + 1 ?>"></button>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

    <!-- ---------- DIGEST + NEWSLETTER ---------- -->
    <div class="side-stack">
      <div class="panel digest">
        <h3>
          <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round"><path d="M4 19V6a2 2 0 0 1 2-2h9l5 5v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2z"/><path d="M8 10h6M8 14h8"/></svg>
          Daily Digest
        </h3>
        <p>Your daily dose of top stories, insights and ideas.</p>
        <a class="btn green" href="#latest">Read Digest</a>
      </div>

      <div class="panel newsletter" id="newsletter">
        <h3>
          <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="#e11d48" stroke-width="2" stroke-linecap="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
          Newsletter
        </h3>
        <p>Get the best stories delivered straight to your inbox.</p>

        <?php if (isset($_GET['subscribed'])): ?>
          <div class="notice <?= $_GET['subscribed'] === '1' ? '' : 'error' ?>" style="margin-bottom:12px">
            <?= $_GET['subscribed'] === '1'
                ? 'Thanks — you are on the list.'
                : 'That email address did not look right.' ?>
          </div>
        <?php endif; ?>

        <form class="sub-form" method="post" action="subscribe.php">
          <?= csrf_field() ?>
          <div class="hp-field" aria-hidden="true">
            <label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
          </div>
          <input type="email" name="email" required placeholder="Your email address" maxlength="180">
          <button class="btn" type="submit">Subscribe</button>
        </form>
      </div>
    </div>

    <!-- ---------- MOST READ ---------- -->
    <aside class="mostread-col">
      <div class="mostread">
        <h3>🔥 Most Read</h3>
        <ol>
          <?php
          $rankColors = ['#e11d48', '#ea580c', '#16a34a', '#2563eb', '#7c3aed'];
          foreach ($mostRead as $i => $s):
            $cat = $categories[$s['category']] ?? $categories['general'];
          ?>
            <li>
              <span class="rank" style="background:<?= $rankColors[$i] ?? '#6b7280' ?>"><?= $i + 1 ?></span>
              <img class="thumb" src="<?= e(story_image($s, $categories)) ?>" alt=""
                   onerror="this.src='<?= e($cat['default_image']) ?>'">
              <a class="txt" href="<?= e(story_url($s)) ?>">
                <b><?= e(mb_strimwidth($s['title'], 0, 52, '…')) ?></b>
                <small><?= $s['published_at'] ? date('M j, Y', strtotime($s['published_at'])) : '' ?></small>
              </a>
            </li>
          <?php endforeach; ?>
        </ol>
      </div>
    </aside>

  </div>

  <!-- ---------- LATEST STORIES ---------- -->
  <section id="latest">
    <div class="sec-head">
      <h2><span class="dot"></span> Latest Stories</h2>
      <a class="more" href="search.php">View All</a>
    </div>

    <?php // A sliding row rather than a static grid. The cards are
          // unchanged - only the container moves. ?>
    <div class="slider" data-slider data-interval="3000">
      <div class="slider-viewport">
        <div class="slider-track">
          <?php foreach (array_slice($latest, 0, 10) as $s):
            $cat = $categories[$s['category']] ?? $categories['general']; ?>
            <a class="card" href="<?= e(story_url($s)) ?>">
              <div class="pic">
                <span class="badge" style="background:<?= e($cat['badge_color']) ?>"><?= e($cat['name']) ?></span>
                <img src="<?= e(story_image($s, $categories)) ?>" alt=""
                     loading="lazy" onerror="this.src='<?= e($cat['default_image']) ?>'">
              </div>
              <div class="body">
                <h3><?= e(mb_strimwidth($s['title'], 0, 64, '…')) ?></h3>
                <div class="meta">
                  <div class="line">
                    <span><?= e($s['author']) ?></span>
                    <span><?= $s['published_at'] ? date('M j, Y', strtotime($s['published_at'])) : '' ?></span>
                  </div>
                  <div class="line"><span><?= read_time($s['body']) ?> min read</span></div>
                </div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
      <button class="slider-nav prev" type="button" aria-label="Previous stories">‹</button>
      <button class="slider-nav next" type="button" aria-label="Next stories">›</button>
      <div class="slider-dots" aria-hidden="true"></div>
    </div>

    <?php if ($pages > 1): ?>
      <div class="pager">
        <?php if ($page > 1): ?><a href="?page=<?= $page - 1 ?>#latest">← Previous</a><?php endif; ?>
        <span class="cur">Page <?= $page ?> of <?= $pages ?></span>
        <?php if ($page < $pages): ?><a href="?page=<?= $page + 1 ?>#latest">Next →</a><?php endif; ?>
      </div>
    <?php endif; ?>
  </section>

  <!-- ---------- WATCH ---------- -->
  <?php // Nothing is rendered at all when there are no videos, so
        // the section simply does not exist until Sheraz adds some. ?>
  <?php if ($videos): ?>
    <section id="watch">
      <div class="sec-head">
        <h2><span class="dot"></span> Watch</h2>
      </div>

      <div class="slider" data-slider data-interval="3000">
        <div class="slider-viewport">
          <div class="slider-track">
            <?php foreach ($videos as $v): ?>
              <?php // The tile is a link to the video's own page, where
                    // the player sits above the write-up. ?>
              <a class="vcard" href="<?= e(video_url($v)) ?>" data-yt="<?= e($v['youtube_id']) ?>">
                <div class="vthumb">
                  <?php // Just a picture. No YouTube code loads here
                        // unless the silent preview runs. ?>
                  <img src="<?= e(youtube_thumb($v['youtube_id'])) ?>" alt="" loading="lazy"
                       data-fallback="<?= e(youtube_thumb($v['youtube_id'], 'hqdefault')) ?>"
                       onerror="this.onerror=null;this.src=this.dataset.fallback">
                  <span class="vplay" aria-hidden="true">
                    <svg viewBox="0 0 68 48">
                      <path class="bg" d="M66.5 7.7a8.6 8.6 0 0 0-6-6C55.2 0 34 0 34 0S12.8 0 7.5 1.6a8.6 8.6 0 0 0-6 6.1A90 90 0 0 0 0 24a90 90 0 0 0 1.5 16.3 8.6 8.6 0 0 0 6 6C12.8 48 34 48 34 48s21.2 0 26.5-1.6a8.6 8.6 0 0 0 6-6.1A90 90 0 0 0 68 24a90 90 0 0 0-1.5-16.3z"/>
                      <path d="M45 24 27 14v20z" fill="#fff"/>
                    </svg>
                  </span>
                </div>
                <div class="vmeta">
                  <h3><?= e($v['title'] ?: 'Watch on DailyPost') ?></h3>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
        <button class="slider-nav prev" type="button" aria-label="Previous videos">‹</button>
        <button class="slider-nav next" type="button" aria-label="Next videos">›</button>
        <div class="slider-dots" aria-hidden="true"></div>
      </div>
    </section>
  <?php endif; ?>

  <!-- ---------- EDITOR'S PICKS ---------- -->
  <?php if ($picks): ?>
    <section id="picks">
      <div class="sec-head">
        <h2>⭐ Editor's Picks</h2>
        <a class="more" href="search.php">View All</a>
      </div>

      <div class="pick-row">
        <?php foreach ($picks as $s):
          $cat = $categories[$s['category']] ?? $categories['general']; ?>
          <a class="pick" href="<?= e(story_url($s)) ?>">
            <img src="<?= e(story_image($s, $categories)) ?>" alt=""
                 loading="lazy" onerror="this.src='<?= e($cat['default_image']) ?>'">
            <div class="shade"></div>
            <div class="copy">
              <span class="badge" style="background:<?= e($cat['badge_color']) ?>"><?= e($cat['name']) ?></span>
              <h3><?= e(mb_strimwidth($s['title'], 0, 58, '…')) ?></h3>
              <small><?= e($s['author']) ?> · <?= read_time($s['body']) ?> min read</small>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

</div>

<script>
// --- HERO CAROUSEL ---
// Plain JavaScript, no library. Advances every 6 seconds, pauses
// while the pointer is over it, and stops entirely if the visitor
// has asked their system to reduce motion.
(function () {
  var slides = document.querySelectorAll('#hero .slide');
  if (slides.length < 2) return;

  var dots = document.querySelectorAll('#heroDots button');
  var at   = 0;
  var timer;

  function show(n) {
    at = (n + slides.length) % slides.length;
    slides.forEach(function (s, i) { s.classList.toggle('on', i === at); });
    dots.forEach(function (d, i) { d.classList.toggle('on', i === at); });
  }

  function start() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    stop();
    timer = setInterval(function () { show(at + 1); }, 6000);
  }
  function stop() { clearInterval(timer); }

  document.getElementById('heroPrev').onclick = function () { show(at - 1); start(); };
  document.getElementById('heroNext').onclick = function () { show(at + 1); start(); };
  dots.forEach(function (d) {
    d.onclick = function () { show(+d.dataset.go); start(); };
  });

  var hero = document.getElementById('hero');
  hero.addEventListener('mouseenter', stop);
  hero.addEventListener('mouseleave', start);

  start();
})();

// --- SLIDING ROWS ----------------------------------------------
// Drives both the Latest Stories row and the Watch row. The cards
// themselves are ordinary cards; only the track moves.
document.querySelectorAll('[data-slider]').forEach(function (slider) {
  var track = slider.querySelector('.slider-track');
  var items = Array.prototype.slice.call(track.children);
  var dots  = slider.querySelector('.slider-dots');
  var prev  = slider.querySelector('.slider-nav.prev');
  var next  = slider.querySelector('.slider-nav.next');
  if (!items.length) return;

  var at = 0, timer = null, stopped = false;

  // How many fit at this width. Read from CSS so the breakpoints
  // live in one place rather than being duplicated here.
  function perView() {
    var v = parseInt(getComputedStyle(track).getPropertyValue('--per'), 10);
    return v > 0 ? v : 1;
  }
  function maxIndex() { return Math.max(0, items.length - perView()); }

  function render() {
    at = Math.min(at, maxIndex());

    var hideNav = items.length <= perView();

    // Everything already fits: centre the items and do not move the
    // track at all. Shifting by offsetLeft here would push a centred
    // row off to the left, because offsetLeft is no longer zero.
    slider.classList.toggle('is-short', hideNav);
    track.style.transform = hideNav
      ? 'none'
      // Offset from the item's own position, so the gap between cards
      // never has to be recalculated here.
      : 'translateX(' + (-items[at].offsetLeft) + 'px)';

    if (prev) prev.hidden = hideNav;
    if (next) next.hidden = hideNav;

    if (dots) {
      if (dots.children.length !== maxIndex() + 1) {
        dots.innerHTML = '';
        for (var i = 0; i <= maxIndex(); i++) {
          var b = document.createElement('button');
          b.type = 'button';
          b.setAttribute('aria-label', 'Go to position ' + (i + 1));
          (function (n) { b.onclick = function () { go(n); rest(); }; })(i);
          dots.appendChild(b);
        }
      }
      Array.prototype.forEach.call(dots.children, function (d, i) {
        d.classList.toggle('on', i === at);
      });
      dots.hidden = hideNav;
    }
  }

  function go(n) {
    var max = maxIndex();
    at = n < 0 ? max : (n > max ? 0 : n);
    render();
  }

  function play() {
    if (stopped) return;
    // Someone who has asked their system to reduce motion should not
    // get a row that moves on its own.
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    if (items.length <= perView()) return;
    rest();
    timer = setInterval(function () { go(at + 1); }, parseInt(slider.dataset.interval, 10) || 8000);
  }
  function rest() { clearInterval(timer); timer = null; }
  function stop() { stopped = true; rest(); }

  if (prev) prev.onclick = function () { go(at - 1); rest(); play(); };
  if (next) next.onclick = function () { go(at + 1); rest(); play(); };

  slider.addEventListener('mouseenter', rest);
  slider.addEventListener('mouseleave', play);
  slider.addEventListener('focusin', rest);
  slider.addEventListener('touchstart', rest, { passive: true });

  // Swipe on a phone.
  var x0 = null;
  slider.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
  slider.addEventListener('touchend', function (e) {
    if (x0 === null) return;
    var dx = e.changedTouches[0].clientX - x0;
    if (Math.abs(dx) > 40) go(dx < 0 ? at + 1 : at - 1);
    x0 = null;
    play();
  });

  // Once a video is playing, stop moving the row out from under it.
  slider.addEventListener('dp:playing', stop);

  window.addEventListener('resize', render);

  // A small handle so the preview code below can hold the row still
  // while a clip is running, then move it on itself.
  slider.dpSlider = {
    pause: rest,
    resume: play,
    next: function () { go(at + 1); },
    current: function () { return at; },
    perView: perView
  };

  render();
  play();
});

// --- THUMBNAIL QUALITY ------------------------------------------
// Not every video has a maxresdefault image. When it is missing
// YouTube does not return 404 - it returns a small grey placeholder
// with status 200, so an onerror handler never fires. The only
// reliable tell is how big the image turned out to be.
document.querySelectorAll('.vthumb img[data-fallback]').forEach(function (img) {
  var check = function () {
    if (img.naturalWidth && img.naturalWidth <= 150 && img.dataset.fallback) {
      img.src = img.dataset.fallback;
      img.removeAttribute('data-fallback');
    }
  };
  if (img.complete) { check(); } else { img.addEventListener('load', check); }
});

// --- SILENT PREVIEW ---------------------------------------------
// Each video in the Watch row plays its first three seconds, muted,
// as it comes into view. The tile itself is a link: clicking it
// opens the video's own page, where it plays properly with sound.
//
// Loading a YouTube player is not free - roughly a megabyte of
// player code and video for every preview - so this is deliberately
// bounded:
//   * one pass only. Each video previews once, then the row goes
//     back to sliding quietly. It does not loop forever burning
//     data while nobody is watching.
//   * not on phones, where that data costs the reader most.
//   * not at all if the browser reports Save Data, or if the
//     visitor has asked their system to reduce motion.
// Every one of those limits is a single line to change.
(function () {
  var section = document.getElementById('watch');
  if (!section) return;

  var slider = section.querySelector('[data-slider]');
  if (!slider || !slider.dpSlider) return;

  var cards = Array.prototype.slice.call(slider.querySelectorAll('.vcard'));
  if (!cards.length) return;

  var conn = navigator.connection || navigator.webkitConnection;
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  if (conn && conn.saveData) return;
  if (window.matchMedia('(max-width: 860px)').matches) return;

  var api        = slider.dpSlider;
  var PREVIEW_MS = 3000;
  var seen       = {};
  var current    = null;
  var timer      = null;

  function stopPreview() {
    if (!current) return;
    var card = current;
    current = null;
    card.classList.remove('previewing');
    var frame = card.querySelector('.vthumb iframe');
    if (frame) frame.parentNode.removeChild(frame);
  }

  function preview(card, index) {
    stopPreview();

    var id = card.dataset.yt;
    if (!id) return false;

    current = card;
    seen[index] = true;
    card.classList.add('previewing');

    var f = document.createElement('iframe');
    // Muted, no controls, not focusable - it is decoration, and the
    // CSS makes it ignore clicks so the tile stays a link.
    f.src = 'https://www.youtube-nocookie.com/embed/' + encodeURIComponent(id)
          + '?autoplay=1&mute=1&controls=0&rel=0&modestbranding=1&playsinline=1&disablekb=1&fs=0';
    f.allow = 'autoplay; encrypted-media';
    f.setAttribute('frameborder', '0');
    f.setAttribute('tabindex', '-1');
    f.setAttribute('aria-hidden', 'true');
    card.querySelector('.vthumb').appendChild(f);

    // Hold the row still for the length of the clip, then move on.
    api.pause();
    clearTimeout(timer);
    timer = setTimeout(function () {
      stopPreview();
      api.next();
      step();
    }, PREVIEW_MS);

    return true;
  }

  function step() {
    var i = api.current();

    // Everything has had its turn: stop and let the row slide on
    // its own from here.
    if (seen[i] || !cards[i]) {
      stopPreview();
      api.resume();
      return;
    }

    preview(cards[i], i);
  }

  // Give the thumbnails a moment to paint first.
  var begin = setTimeout(step, 700);

  // Hovering means someone is choosing - stop interrupting them.
  slider.addEventListener('mouseenter', function () {
    clearTimeout(begin);
    clearTimeout(timer);
    stopPreview();
  });

  // Leaving the tab should not leave a player running.
  document.addEventListener('visibilitychange', function () {
    if (document.hidden) {
      clearTimeout(timer);
      stopPreview();
    }
  });
})();
</script>

<?php require 'includes/footer.php'; ?>
