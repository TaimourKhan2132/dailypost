<?php
// ---------------------------------------------------------------
// One video, laid out like a story: the player on top, the text
// underneath, related videos at the bottom.
//
// Reachable as /watch/some-slug through the rewrite in .htaccess,
// and as video.php?id=42 for anything saved before slugs existed.
// ---------------------------------------------------------------

require 'config/db.php';

dp_session_start();

$categories = load_categories($pdo);

$slug = trim($_GET['slug'] ?? '');
$id   = (int) ($_GET['id'] ?? 0);

// The videos table only exists from migration 006 onwards.
$v = null;
try {
    if ($slug !== '') {
        $q = $pdo->prepare("SELECT * FROM videos WHERE slug = ? AND status = 'published'");
        $q->execute([$slug]);
    } else {
        $q = $pdo->prepare("SELECT * FROM videos WHERE id = ? AND status = 'published'");
        $q->execute([$id]);
    }
    $v = $q->fetch();
} catch (PDOException $e) {
    $v = null;
}

// slug, description and views arrive with migration 007. Until that
// is imported this page still has to work - it simply has no
// address of its own, no write-up and no counter. Guarding the
// table alone was not enough: the columns need checking too.
$hasPageColumns = false;
try {
    $pdo->query("SELECT slug, description, views FROM videos LIMIT 1");
    $hasPageColumns = true;
} catch (PDOException $e) {
    $hasPageColumns = false;
}

// Arrived by id but the video has a readable address: send the
// visitor to the proper one, permanently.
if ($v && $slug === '' && $hasPageColumns && !empty($v['slug'])) {
    header('Location: ' . video_url($v), true, 301);
    exit;
}

if (!$v) {
    http_response_code(404);
    $page_title = 'Video not found — DailyPost';
    $active_nav = '';
    require 'includes/header.php';
    echo '<div class="wrap"><div class="article"><h1>Video not found</h1>'
       . '<p class="lead">That video may have been removed, or the link is wrong.</p>'
       . '<a class="btn" href="' . e(base_path()) . 'index.php">Back to DailyPost</a></div></div>';
    require 'includes/footer.php';
    exit;
}

// One view per video per browsing session, same rule as stories.
$vid   = (int) $v['id'];
$views = (int) ($v['views'] ?? 0);

if ($hasPageColumns && empty($_SESSION['viewed_video'][$vid])) {
    $pdo->prepare("UPDATE videos SET views = views + 1 WHERE id = ?")->execute([$vid]);
    $_SESSION['viewed_video'][$vid] = true;
    $views++;
}

$title       = $v['title'] ?: 'Watch on DailyPost';
$description = (string) ($v['description'] ?? '');

$rel = $pdo->prepare(
    "SELECT * FROM videos WHERE status = 'published' AND id <> ? ORDER BY sort_order, id LIMIT 4"
);
$rel->execute([$vid]);
$related = $rel->fetchAll();

$page_title       = $title . ' — DailyPost';
$meta_description = $description !== ''
    ? mb_strimwidth(trim(preg_replace('/\s+/', ' ', $description)), 0, 160, '…')
    : 'Watch ' . $title . ' on DailyPost.';
$og_image         = youtube_thumb($v['youtube_id']);
$canonical        = $hasPageColumns ? site_url(video_path($v)) : null;
$active_nav       = 'read';

require 'includes/header.php';
?>

<div class="wrap">
  <article class="article">

    <?php // Autoplay is fine here: the visitor chose this page.
          // On the homepage the player is never loaded at all. ?>
    <div class="video-frame">
      <iframe src="<?= e(youtube_embed($v['youtube_id'])) ?>"
              title="<?= e($title) ?>"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              allowfullscreen frameborder="0"></iframe>
    </div>

    <h1><?= e($title) ?></h1>

    <div class="byline">
      <span><?= number_format($views) ?> view<?= $views === 1 ? '' : 's' ?></span>
      <span><?= date('F j, Y', strtotime($v['created_at'])) ?></span>
      <span><a href="<?= e(youtube_watch($v['youtube_id'])) ?>" target="_blank" rel="noopener"
               style="color:var(--accent)">Watch on YouTube</a></span>
    </div>

    <?php if (trim($description) !== ''): ?>
      <?php
      // Escaped first, then blank lines become paragraphs - so any
      // HTML typed into the description stays inert text.
      $paras = preg_split('/\n\s*\n/', trim($description));
      ?>
      <div class="content">
        <?php foreach ($paras as $p): ?>
          <p><?= nl2br(e(trim($p))) ?></p>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="lead" style="margin-top:22px">No description has been added for this video yet.</p>
    <?php endif; ?>

    <?php
    $shareUrl  = site_url(video_path($v));
    $shareText = $title . ' — DailyPost';
    ?>
    <div class="share">
      <span class="share-label">Share this video</span>

      <a class="share-btn wa" target="_blank" rel="noopener"
         href="https://wa.me/?text=<?= rawurlencode($shareText . ' ' . $shareUrl) ?>">
        <svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 0 0-8.6 15l-1.3 4.7 4.8-1.3A10 10 0 1 0 12 2zm5.8 14.2c-.2.7-1.2 1.3-2 1.4-.5.1-1.2.1-3.8-.8-3.2-1.3-5.2-4.6-5.4-4.8-.2-.2-1.3-1.7-1.3-3.3 0-1.6.8-2.3 1.1-2.7.3-.3.7-.4.9-.4h.6c.2 0 .5 0 .7.5l1 2.4c.1.2.1.4 0 .6l-.4.6-.4.4c-.1.1-.3.3-.1.6.2.3.8 1.3 1.7 2.1 1.2 1 2.1 1.4 2.4 1.5.3.1.5.1.6-.1l.9-1c.2-.2.4-.2.6-.1l2.2 1.1c.2.1.4.2.5.3.1.2.1.7-.1 1.4z"/></svg>
        WhatsApp
      </a>

      <a class="share-btn fb" target="_blank" rel="noopener"
         href="https://www.facebook.com/sharer/sharer.php?u=<?= rawurlencode($shareUrl) ?>">
        <svg viewBox="0 0 24 24"><path d="M22 12a10 10 0 1 0-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.4v7A10 10 0 0 0 22 12z"/></svg>
        Facebook
      </a>

      <button class="share-btn copy" type="button" data-url="<?= e($shareUrl) ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
        <span>Copy link</span>
      </button>
    </div>

    <a class="btn ghost" href="<?= e(base_path()) ?>index.php#watch">← Back to DailyPost</a>
  </article>

  <?php if ($related): ?>
    <section style="max-width:1180px;margin:0 auto">
      <div class="sec-head">
        <h2><span class="dot"></span> More to watch</h2>
      </div>
      <div class="card-row">
        <?php foreach ($related as $r): ?>
          <a class="vcard" href="<?= e(video_url($r)) ?>">
            <div class="vthumb">
              <img src="<?= e(youtube_thumb($r['youtube_id'])) ?>" alt="" loading="lazy"
                   data-fallback="<?= e(youtube_thumb($r['youtube_id'], 'hqdefault')) ?>"
                   onerror="this.onerror=null;this.src=this.dataset.fallback">
              <span class="vplay" aria-hidden="true">
                <svg viewBox="0 0 68 48">
                  <path class="bg" d="M66.5 7.7a8.6 8.6 0 0 0-6-6C55.2 0 34 0 34 0S12.8 0 7.5 1.6a8.6 8.6 0 0 0-6 6.1A90 90 0 0 0 0 24a90 90 0 0 0 1.5 16.3 8.6 8.6 0 0 0 6 6C12.8 48 34 48 34 48s21.2 0 26.5-1.6a8.6 8.6 0 0 0 6-6.1A90 90 0 0 0 68 24a90 90 0 0 0-1.5-16.3z"/>
                  <path d="M45 24 27 14v20z" fill="#fff"/>
                </svg>
              </span>
            </div>
            <div class="vmeta">
              <h3><?= e($r['title'] ?: 'Watch on DailyPost') ?></h3>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>
</div>

<script>
document.querySelectorAll('.share-btn.copy').forEach(function (b) {
  b.addEventListener('click', function () {
    var label = b.querySelector('span');
    var done  = function () { label.textContent = 'Copied'; setTimeout(function(){ label.textContent = 'Copy link'; }, 1800); };
    if (navigator.clipboard && window.isSecureContext) { navigator.clipboard.writeText(b.dataset.url).then(done); }
    else {
      var t = document.createElement('textarea');
      t.value = b.dataset.url; t.style.position = 'fixed'; t.style.opacity = '0';
      document.body.appendChild(t); t.select();
      try { document.execCommand('copy'); done(); } catch (e) {}
      document.body.removeChild(t);
    }
  });
});

// Thumbnails on the related row: YouTube answers a missing
// maxresdefault with a small grey placeholder and status 200, so
// size is the only reliable tell.
document.querySelectorAll('.vthumb img[data-fallback]').forEach(function (img) {
  var check = function () {
    if (img.naturalWidth && img.naturalWidth <= 150 && img.dataset.fallback) {
      img.src = img.dataset.fallback;
      img.removeAttribute('data-fallback');
    }
  };
  if (img.complete) { check(); } else { img.addEventListener('load', check); }
});
</script>

<?php require 'includes/footer.php'; ?>
