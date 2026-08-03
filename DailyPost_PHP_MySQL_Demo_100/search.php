<?php
require 'config/db.php';

dp_session_start();

$categories = load_categories($pdo);

$term = trim($_GET['q'] ?? '');
$cat  = $_GET['category'] ?? '';

if (!isset($categories[$cat])) {
    $cat = '';
}

$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 15;
$offset  = ($page - 1) * $perPage;

// Build the WHERE clause from whichever filters are actually in
// use. Every value still goes in as a bound parameter - the SQL
// text itself is assembled only from our own fixed strings.
$where  = ["status = 'published'"];
$params = [];

if ($term !== '') {
    // Each column needs its own placeholder. MySQL's real prepared
    // statements will not let one named parameter appear more than
    // once - it only works if PDO is faking them, and we turned that
    // off in config/db.php.
    $where[] = "(title LIKE :t1 OR excerpt LIKE :t2 OR body LIKE :t3 OR author LIKE :t4)";
    $like = '%' . $term . '%';
    $params['t1'] = $like;
    $params['t2'] = $like;
    $params['t3'] = $like;
    $params['t4'] = $like;
}
if ($cat !== '') {
    $where[] = "category = :c";
    $params['c'] = $cat;
}

$whereSql = 'WHERE ' . implode(' AND ', $where);

$countQ = $pdo->prepare("SELECT COUNT(*) FROM stories $whereSql");
$countQ->execute($params);
$total = (int) $countQ->fetchColumn();
$pages = max(1, (int) ceil($total / $perPage));

$listQ = $pdo->prepare(
    "SELECT * FROM stories $whereSql ORDER BY published_at DESC, id DESC LIMIT :lim OFFSET :off"
);
foreach ($params as $k => $val) {
    $listQ->bindValue($k, $val);
}
$listQ->bindValue('lim', $perPage, PDO::PARAM_INT);
$listQ->bindValue('off', $offset,  PDO::PARAM_INT);
$listQ->execute();
$results = $listQ->fetchAll();

$page_title = $term !== '' ? "Search: $term — DailyPost" : 'Search — DailyPost';
$active_nav = 'search';

require 'includes/header.php';
?>

<div class="wrap">
  <div style="padding:28px 0 6px">
    <h1 style="font-size:32px;font-weight:800;letter-spacing:-.025em;margin:0 0 18px">Search DailyPost</h1>

    <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:22px">
      <input type="search" name="q" value="<?= e($term) ?>" placeholder="Search stories, authors…"
             style="flex:1;min-width:220px;padding:12px 14px;border:1px solid var(--border);border-radius:8px;background:var(--surface);color:var(--text);font:inherit;font-size:15px">
      <select name="category"
              style="padding:12px 14px;border:1px solid var(--border);border-radius:8px;background:var(--surface);color:var(--text);font:inherit;font-size:15px">
        <option value="">All categories</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= e($c['slug']) ?>" <?= $cat === $c['slug'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn" type="submit">Search</button>
    </form>

    <p style="color:var(--text-muted);margin:0 0 18px">
      <?php if ($term === '' && $cat === ''): ?>
        Showing all <?= number_format($total) ?> published stories.
      <?php else: ?>
        <?= number_format($total) ?> <?= $total === 1 ? 'story' : 'stories' ?> found<?php
          if ($term !== '') echo ' for “' . e($term) . '”';
          if ($cat  !== '') echo ' in ' . e($categories[$cat]['name']);
        ?>.
      <?php endif; ?>
    </p>
  </div>

  <?php if (!$results): ?>
    <p style="padding:30px 0 60px;color:var(--text-muted)">
      Nothing matched. Try a different word, or <a href="search.php" style="color:var(--accent)">clear the filters</a>.
    </p>
  <?php else: ?>
    <div class="card-row">
      <?php foreach ($results as $s):
        $sc = $categories[$s['category']] ?? $categories['general']; ?>
        <a class="card" href="story.php?id=<?= (int) $s['id'] ?>">
          <div class="pic">
            <span class="badge" style="background:<?= e($sc['badge_color']) ?>"><?= e($sc['name']) ?></span>
            <img src="<?= e(story_image($s, $categories)) ?>" alt="" loading="lazy"
                 onerror="this.src='<?= e($sc['default_image']) ?>'">
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

    <?php if ($pages > 1):
      $qs = http_build_query(array_filter(['q' => $term, 'category' => $cat])); ?>
      <div class="pager">
        <?php if ($page > 1): ?><a href="?<?= $qs ?>&page=<?= $page - 1 ?>">← Previous</a><?php endif; ?>
        <span class="cur">Page <?= $page ?> of <?= $pages ?></span>
        <?php if ($page < $pages): ?><a href="?<?= $qs ?>&page=<?= $page + 1 ?>">Next →</a><?php endif; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php require 'includes/footer.php'; ?>
