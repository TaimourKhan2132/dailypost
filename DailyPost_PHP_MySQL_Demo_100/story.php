<?php
require 'config/db.php';

dp_session_start();

$categories = load_categories($pdo);

// Reachable two ways: /story/some-slug (via the rewrite rule in
// .htaccess) and the older story.php?id=42, which still has to work
// because links to it may already be out there.
$slug = trim($_GET['slug'] ?? '');
$id   = (int) ($_GET['id'] ?? 0);

if ($slug !== '') {
    $q = $pdo->prepare("SELECT * FROM stories WHERE slug = ? AND status = 'published'");
    $q->execute([$slug]);
} else {
    $q = $pdo->prepare("SELECT * FROM stories WHERE id = ? AND status = 'published'");
    $q->execute([$id]);
}
$s = $q->fetch();

// Arrived by id but the story has a readable address: send the
// visitor (and any search engine) to the proper one, permanently.
if ($s && $slug === '' && !empty($s['slug'])) {
    header('Location: ' . story_url($s), true, 301);
    exit;
}

if (!$s) {
    http_response_code(404);
    $page_title = 'Story not found — DailyPost';
    $active_nav = '';
    require 'includes/header.php';
    echo '<div class="wrap"><div class="article"><h1>Story not found</h1>'
       . '<p class="lead">That story may have been removed, or the link is wrong.</p>'
       . '<a class="btn" href="index.php">Back to DailyPost</a></div></div>';
    require 'includes/footer.php';
    exit;
}

// Take the id from the row, not the URL. Arriving by slug leaves
// $_GET['id'] empty, which would have made the view counter and the
// related-stories query silently operate on id 0.
$id = (int) $s['id'];

// One view per story per browsing session, not per page load.
if (empty($_SESSION['viewed'][$id])) {
    $pdo->prepare("UPDATE stories SET views = views + 1 WHERE id = ?")->execute([$id]);
    $_SESSION['viewed'][$id] = true;
    $s['views']++;
}

$cat   = $categories[$s['category']] ?? $categories['general'];
$image = story_image($s, $categories);

// More from the same category, excluding this one.
$rel = $pdo->prepare(
    "SELECT * FROM stories
     WHERE status = 'published' AND category = ? AND id <> ?
     ORDER BY published_at DESC LIMIT 5"
);
$rel->execute([$s['category'], $id]);
$related = $rel->fetchAll();

$page_title       = $s['title'] . ' — DailyPost';
$meta_description = $s['excerpt'] ?: mb_strimwidth($s['body'], 0, 160, '…');
$og_image         = $image;
$canonical        = site_url(story_url($s));
$active_nav       = 'read';

require 'includes/header.php';
?>

<div class="wrap">
  <article class="article">

    <?php if ($image): ?>
      <div class="cover">
        <img src="<?= e($image) ?>" alt=""
             onerror="this.src='<?= e($cat['default_image']) ?>'">
      </div>
    <?php endif; ?>

    <span class="badge" style="position:static;display:inline-block;background:<?= e($cat['badge_color']) ?>">
      <?= e($cat['name']) ?>
    </span>

    <h1><?= e($s['title']) ?></h1>

    <?php if ($s['excerpt']): ?>
      <p class="lead"><?= e($s['excerpt']) ?></p>
    <?php endif; ?>

    <div class="byline">
      <span>By <strong style="color:var(--text)"><?= e($s['author']) ?></strong></span>
      <span><?= $s['published_at'] ? date('F j, Y', strtotime($s['published_at'])) : '' ?></span>
      <span><?= read_time($s['body']) ?> min read</span>
      <span><?= number_format((int) $s['views']) ?> views</span>
    </div>

    <?php
    // Escape first, then turn blank lines into paragraphs. Doing it
    // in this order means any HTML the writer typed stays inert text
    // rather than becoming markup.
    $paras = preg_split('/\n\s*\n/', trim($s['body']));
    ?>
    <div class="content">
      <?php foreach ($paras as $p): ?>
        <p><?= nl2br(e(trim($p))) ?></p>
      <?php endforeach; ?>
    </div>

    <?php
    // WhatsApp first, deliberately - it is how links actually
    // travel here. The og: tags in the header are what make the
    // shared link show a picture and a headline rather than a bare
    // address.
    $shareUrl  = site_url(story_url($s));
    $shareText = $s['title'] . ' — DailyPost';
    ?>
    <div class="share">
      <span class="share-label">Share this story</span>

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

      <a class="share-btn xs" target="_blank" rel="noopener"
         href="https://twitter.com/intent/tweet?text=<?= rawurlencode($shareText) ?>&url=<?= rawurlencode($shareUrl) ?>">
        <svg viewBox="0 0 24 24"><path d="M18.9 2H22l-6.7 7.7L23 22h-6.2l-4.9-6.4L6.3 22H3.2l7.2-8.2L2 2h6.3l4.4 5.8L18.9 2zm-1.1 18h1.7L7.3 3.7H5.5L17.8 20z"/></svg>
        X
      </a>

      <button class="share-btn copy" type="button" data-url="<?= e($shareUrl) ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
        <span>Copy link</span>
      </button>
    </div>

    <a class="btn ghost" href="index.php">← Back to DailyPost</a>
  </article>

  <script>
  document.querySelectorAll('.share-btn.copy').forEach(function (b) {
    b.addEventListener('click', function () {
      var label = b.querySelector('span');
      var done  = function () { label.textContent = 'Copied'; setTimeout(function(){ label.textContent = 'Copy link'; }, 1800); };
      // navigator.clipboard needs HTTPS; fall back for plain http.
      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(b.dataset.url).then(done);
      } else {
        var t = document.createElement('textarea');
        t.value = b.dataset.url;
        t.style.position = 'fixed'; t.style.opacity = '0';
        document.body.appendChild(t); t.select();
        try { document.execCommand('copy'); done(); } catch (e) {}
        document.body.removeChild(t);
      }
    });
  });
  </script>

  <?php if ($related): ?>
    <section style="max-width:1180px;margin:0 auto">
      <div class="sec-head">
        <h2><span class="dot"></span> More in <?= e($cat['name']) ?></h2>
      </div>
      <div class="card-row">
        <?php foreach ($related as $r):
          $rc = $categories[$r['category']] ?? $categories['general']; ?>
          <a class="card" href="<?= e(story_url($r)) ?>">
            <div class="pic">
              <span class="badge" style="background:<?= e($rc['badge_color']) ?>"><?= e($rc['name']) ?></span>
              <img src="<?= e(story_image($r, $categories)) ?>" alt="" loading="lazy"
                   onerror="this.src='<?= e($rc['default_image']) ?>'">
            </div>
            <div class="body">
              <h3><?= e(mb_strimwidth($r['title'], 0, 64, '…')) ?></h3>
              <div class="meta"><div class="line">
                <span><?= e($r['author']) ?></span>
                <span><?= read_time($r['body']) ?> min read</span>
              </div></div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>
</div>

<?php require 'includes/footer.php'; ?>
