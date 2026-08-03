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

    <div class="card-row">
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

    <?php if ($pages > 1): ?>
      <div class="pager">
        <?php if ($page > 1): ?><a href="?page=<?= $page - 1 ?>#latest">← Previous</a><?php endif; ?>
        <span class="cur">Page <?= $page ?> of <?= $pages ?></span>
        <?php if ($page < $pages): ?><a href="?page=<?= $page + 1 ?>#latest">Next →</a><?php endif; ?>
      </div>
    <?php endif; ?>
  </section>

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
</script>

<?php require 'includes/footer.php'; ?>
