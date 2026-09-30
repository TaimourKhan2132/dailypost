<?php
// ---------------------------------------------------------------
// Every video, paginated. The "View All" destination from the Watch
// row on the homepage.
//
// Served at /watch as well as /watch.php. Individual videos live at
// /watch/<slug>, which is a different rewrite rule.
// ---------------------------------------------------------------

require 'config/db.php';

dp_session_start();

$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 12;
$offset  = ($page - 1) * $perPage;

// The videos table arrives with migration 006. Without it this page
// simply has nothing to show rather than falling over.
$videos = [];
$total  = 0;

try {
    $total = (int) $pdo->query("SELECT COUNT(*) FROM videos WHERE status = 'published'")->fetchColumn();

    $q = $pdo->prepare(
        "SELECT * FROM videos WHERE status = 'published'
         ORDER BY sort_order, id
         LIMIT :lim OFFSET :off"
    );
    $q->bindValue('lim', $perPage, PDO::PARAM_INT);
    $q->bindValue('off', $offset,  PDO::PARAM_INT);
    $q->execute();
    $videos = $q->fetchAll();
} catch (PDOException $e) {
    // migration 006 not imported yet
}

$pages = max(1, (int) ceil($total / $perPage));

$page_title       = 'Watch — DailyPost';
$meta_description = 'Videos worth watching, collected by DailyPost.';
$canonical        = site_url('watch');
$active_nav       = 'read';

require 'includes/header.php';
?>

<div class="wrap">
  <div style="padding:28px 0 6px">
    <h1 style="font-size:32px;font-weight:800;letter-spacing:-.025em;margin:0 0 8px">Watch</h1>
    <p style="color:var(--text-muted);margin:0 0 22px">
      <?php if ($total === 0): ?>
        No videos yet. They will appear here as soon as some are added.
      <?php else: ?>
        <?= number_format($total) ?> video<?= $total === 1 ? '' : 's' ?> to watch.
      <?php endif; ?>
    </p>
  </div>

  <?php if ($videos): ?>
    <div class="video-grid">
      <?php foreach ($videos as $v): ?>
        <a class="vcard" href="<?= e(video_url($v)) ?>">
          <div class="vthumb">
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

    <?php if ($pages > 1): ?>
      <div class="pager">
        <?php if ($page > 1): ?><a href="?page=<?= $page - 1 ?>">← Previous</a><?php endif; ?>
        <span class="cur">Page <?= $page ?> of <?= $pages ?></span>
        <?php if ($page < $pages): ?><a href="?page=<?= $page + 1 ?>">Next →</a><?php endif; ?>
      </div>
    <?php endif; ?>
  <?php else: ?>
    <p style="padding:20px 0 60px;color:var(--text-muted)">
      Nothing to watch just yet. <a href="<?= e(base_path()) ?>index.php" style="color:var(--accent)">Back to the homepage</a>.
    </p>
  <?php endif; ?>
</div>

<script>
// A missing maxresdefault is answered by YouTube with a small grey
// placeholder and status 200, so size is the only reliable tell.
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
