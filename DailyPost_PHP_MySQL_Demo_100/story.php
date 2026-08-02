<?php
require 'config/db.php';

dp_session_start();

$categories = [];
foreach ($pdo->query("SELECT * FROM categories ORDER BY sort_order") as $c) {
    $categories[$c['slug']] = $c;
}

$id = (int) ($_GET['id'] ?? 0);

$q = $pdo->prepare("SELECT * FROM stories WHERE id = ? AND status = 'published'");
$q->execute([$id]);
$s = $q->fetch();

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

    <a class="btn ghost" href="index.php">← Back to DailyPost</a>
  </article>

  <?php if ($related): ?>
    <section style="max-width:1180px;margin:0 auto">
      <div class="sec-head">
        <h2><span class="dot"></span> More in <?= e($cat['name']) ?></h2>
      </div>
      <div class="card-row">
        <?php foreach ($related as $r):
          $rc = $categories[$r['category']] ?? $categories['general']; ?>
          <a class="card" href="story.php?id=<?= (int) $r['id'] ?>">
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
