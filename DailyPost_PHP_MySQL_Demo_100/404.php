<?php
require __DIR__ . '/config/db.php';

dp_session_start();

http_response_code(404);

$categories = load_categories($pdo);

// Something to do other than leave. A dead end that offers nothing
// is how people close the tab.
$suggested = $pdo->query(
    "SELECT * FROM stories WHERE status = 'published' ORDER BY views DESC, published_at DESC LIMIT 4"
)->fetchAll();

$page_title       = 'Page not found — DailyPost';
$meta_description = 'That page does not exist. Here are some stories worth reading instead.';
$active_nav       = '';

require __DIR__ . '/includes/header.php';
?>

<div class="wrap">
  <div style="text-align:center;padding:60px 0 34px">
    <div style="font-size:clamp(64px,10vw,120px);font-weight:800;letter-spacing:-.04em;line-height:1;color:var(--accent)">404</div>
    <h1 style="font-size:clamp(24px,3vw,34px);font-weight:800;letter-spacing:-.02em;margin:10px 0 10px">This page doesn't exist</h1>
    <p style="color:var(--text-muted);max-width:520px;margin:0 auto 24px">
      The link may be broken, or the story may have been removed. Nothing is lost —
      everything else is still here.
    </p>
    <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap">
      <a class="btn" href="index.php">Back to the homepage</a>
      <a class="btn ghost" href="search.php">Search stories</a>
      <a class="btn ghost" href="write.php">Write a story</a>
    </div>
  </div>

  <?php if ($suggested): ?>
    <section>
      <div class="sec-head">
        <h2><span class="dot"></span> Most read on DailyPost</h2>
      </div>
      <div class="card-row">
        <?php foreach ($suggested as $s):
          $c = $categories[$s['category']] ?? $categories['general']; ?>
          <a class="card" href="<?= e(story_url($s)) ?>">
            <div class="pic">
              <span class="badge" style="background:<?= e($c['badge_color']) ?>"><?= e($c['name']) ?></span>
              <img src="<?= e(story_image($s, $categories)) ?>" alt="" loading="lazy"
                   onerror="this.src='<?= e($c['default_image']) ?>'">
            </div>
            <div class="body">
              <h3><?= e(mb_strimwidth($s['title'], 0, 64, '…')) ?></h3>
              <div class="meta"><div class="line">
                <span><?= e($s['author']) ?></span>
                <span><?= read_time($s['body']) ?> min read</span>
              </div></div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
