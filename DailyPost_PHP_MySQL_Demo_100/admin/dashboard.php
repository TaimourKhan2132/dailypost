<?php
require '../config/db.php';

require_admin();

// --- ACTIONS ----------------------------------------------------
// These used to be plain links (?action=publish&id=5). That meant
// anything which followed a link while Sheraz was logged in - a
// browser prefetcher, an <img> tag on a hostile page - could publish
// or reject stories on his behalf. Actions that change data are now
// POST only, and carry a CSRF token.

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $id     = (int) ($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($id > 0) {
        switch ($action) {
            case 'publish':
                // The admin can correct or remove the writer's image
                // link at the moment of approval.
                $img = trim($_POST['image_url'] ?? '');
                $img = is_safe_image_url($img) ? $img : null;

                $pdo->prepare(
                    "UPDATE stories
                     SET status = 'published',
                         published_at = COALESCE(published_at, NOW()),
                         image_url = ?
                     WHERE id = ?"
                )->execute([$img, $id]);
                break;

            case 'reject':
                $pdo->prepare("UPDATE stories SET status = 'rejected' WHERE id = ?")->execute([$id]);
                break;

            case 'unpublish':
                $pdo->prepare("UPDATE stories SET status = 'pending' WHERE id = ?")->execute([$id]);
                break;

            case 'delete':
                $pdo->prepare("DELETE FROM stories WHERE id = ?")->execute([$id]);
                break;

            case 'toggle_featured':
                $pdo->prepare("UPDATE stories SET featured = 1 - featured WHERE id = ?")->execute([$id]);
                break;

            case 'toggle_pick':
                $pdo->prepare("UPDATE stories SET editors_pick = 1 - editors_pick WHERE id = ?")->execute([$id]);
                break;
        }
    }

    // Redirect after POST so a refresh does not repeat the action.
    header('Location: dashboard.php?filter=' . urlencode($_POST['filter'] ?? 'pending'));
    exit;
}

// --- LISTING ----------------------------------------------------
$filter = $_GET['filter'] ?? 'pending';
if (!in_array($filter, ['pending', 'published', 'rejected', 'all'], true)) {
    $filter = 'pending';
}

$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 20;
$offset  = ($page - 1) * $perPage;

$where  = $filter === 'all' ? '' : 'WHERE status = :status';
$params = $filter === 'all' ? [] : ['status' => $filter];

$countQ = $pdo->prepare("SELECT COUNT(*) FROM stories $where");
$countQ->execute($params);
$total = (int) $countQ->fetchColumn();
$pages = max(1, (int) ceil($total / $perPage));

// LIMIT and OFFSET cannot be bound as strings, hence bindValue with
// an explicit integer type.
$listQ = $pdo->prepare("SELECT * FROM stories $where ORDER BY created_at DESC, id DESC LIMIT :lim OFFSET :off");
foreach ($params as $k => $v) {
    $listQ->bindValue($k, $v);
}
$listQ->bindValue('lim', $perPage, PDO::PARAM_INT);
$listQ->bindValue('off', $offset,  PDO::PARAM_INT);
$listQ->execute();
$stories = $listQ->fetchAll();

// Counts for the tab labels.
$counts = [];
foreach ($pdo->query("SELECT status, COUNT(*) n FROM stories GROUP BY status") as $row) {
    $counts[$row['status']] = (int) $row['n'];
}
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Admin — DailyPost</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
.tabs{display:flex;gap:10px;margin:20px 0;flex-wrap:wrap}
.tabs a{padding:8px 14px;border:1px solid #ddd;border-radius:20px;text-decoration:none;color:#333;font-size:14px}
.tabs a.on{background:#171717;color:#fff;border-color:#171717}
.admin-card{border:1px solid #e2e2e2;padding:18px;margin-bottom:14px;border-radius:8px;background:#fff}
.admin-card .row{display:flex;gap:16px;align-items:flex-start}
.admin-card img.thumb{width:120px;height:80px;object-fit:cover;border-radius:6px;background:#f0f0f0;flex-shrink:0}
.admin-card .meta{font-size:13px;color:#666;margin:4px 0 8px}
.admin-card form{display:inline}
.admin-card .actions{margin-top:12px;display:flex;gap:8px;flex-wrap:wrap;align-items:center}
.admin-card input.imgurl{width:100%;max-width:420px;padding:7px;border:1px solid #ccc;border-radius:5px;font-size:13px;margin-top:6px}
.btn-sm{padding:7px 13px;border:0;border-radius:5px;cursor:pointer;font-size:13px;background:#171717;color:#fff}
.btn-sm.warn{background:#b45309}
.btn-sm.danger{background:#b91c1c}
.btn-sm.ghost{background:#eee;color:#333}
.badge{font-size:11px;padding:3px 8px;border-radius:10px;background:#eee;color:#444;text-transform:uppercase;letter-spacing:.05em}
.pager{display:flex;gap:8px;margin-top:24px;flex-wrap:wrap}
.pager a,.pager span{padding:7px 12px;border:1px solid #ddd;border-radius:5px;text-decoration:none;color:#333;font-size:14px}
.pager span.cur{background:#171717;color:#fff;border-color:#171717}
</style>
</head>
<body>
<header>
  <a class="brand" href="../index.php">Daily<span>Post</span></a>
  <nav>
    <span style="color:#666;font-size:14px">Signed in as <?= e($_SESSION['admin']) ?></span>
    <a href="logout.php">Logout</a>
  </nav>
</header>

<main>
<section class="section">
  <h1>Submissions</h1>

  <div class="tabs">
    <?php foreach (['pending' => 'Pending', 'published' => 'Published', 'rejected' => 'Rejected', 'all' => 'All'] as $key => $label): ?>
      <a class="<?= $filter === $key ? 'on' : '' ?>" href="?filter=<?= $key ?>">
        <?= $label ?><?= isset($counts[$key]) ? ' (' . $counts[$key] . ')' : '' ?>
      </a>
    <?php endforeach; ?>
  </div>

  <?php if (!$stories): ?>
    <p>Nothing here.</p>
  <?php endif; ?>

  <?php foreach ($stories as $s): ?>
    <article class="admin-card">
      <div class="row">
        <?php if (is_safe_image_url($s['image_url'])): ?>
          <img class="thumb" src="<?= e($s['image_url']) ?>" alt=""
               onerror="this.style.display='none'">
        <?php endif; ?>

        <div style="flex:1;min-width:0">
          <b><?= e($s['title']) ?></b>
          <div class="meta">
            By <?= e($s['author']) ?>
            · <span class="badge"><?= e($s['category']) ?></span>
            · <span class="badge"><?= e($s['status']) ?></span>
            · <?= e($s['created_at']) ?>
            · <?= (int) $s['views'] ?> views
            <?= $s['featured']     ? '· <span class="badge">featured</span>' : '' ?>
            <?= $s['editors_pick'] ? '· <span class="badge">pick</span>'     : '' ?>
          </div>
          <p style="margin:0;color:#444"><?= e(mb_substr($s['body'], 0, 220)) ?>…</p>

          <div class="actions">
            <?php if ($s['status'] === 'pending'): ?>
              <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                <input type="hidden" name="filter" value="<?= e($filter) ?>">
                <input type="hidden" name="action" value="publish">
                <input class="imgurl" type="url" name="image_url"
                       value="<?= e($s['image_url'] ?? '') ?>"
                       placeholder="Cover image link — leave blank to use the category photo">
                <button class="btn-sm" type="submit">Publish</button>
              </form>
              <form method="post" onsubmit="return confirm('Reject this story?')">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                <input type="hidden" name="filter" value="<?= e($filter) ?>">
                <button class="btn-sm warn" name="action" value="reject" type="submit">Reject</button>
              </form>
            <?php endif; ?>

            <?php if ($s['status'] === 'published'): ?>
              <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                <input type="hidden" name="filter" value="<?= e($filter) ?>">
                <button class="btn-sm ghost" name="action" value="toggle_featured" type="submit">
                  <?= $s['featured'] ? 'Remove from carousel' : 'Add to carousel' ?>
                </button>
              </form>
              <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                <input type="hidden" name="filter" value="<?= e($filter) ?>">
                <button class="btn-sm ghost" name="action" value="toggle_pick" type="submit">
                  <?= $s['editors_pick'] ? "Remove from picks" : "Add to Editor's Picks" ?>
                </button>
              </form>
              <form method="post" onsubmit="return confirm('Take this story off the site?')">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                <input type="hidden" name="filter" value="<?= e($filter) ?>">
                <button class="btn-sm warn" name="action" value="unpublish" type="submit">Unpublish</button>
              </form>
            <?php endif; ?>

            <form method="post" onsubmit="return confirm('Delete permanently? This cannot be undone.')">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
              <input type="hidden" name="filter" value="<?= e($filter) ?>">
              <button class="btn-sm danger" name="action" value="delete" type="submit">Delete</button>
            </form>

            <a class="btn-sm ghost" style="text-decoration:none"
               href="../story.php?id=<?= (int) $s['id'] ?>" target="_blank">View</a>
          </div>
        </div>
      </div>
    </article>
  <?php endforeach; ?>

  <?php if ($pages > 1): ?>
    <div class="pager">
      <?php for ($p = 1; $p <= $pages; $p++): ?>
        <?php if ($p === $page): ?>
          <span class="cur"><?= $p ?></span>
        <?php else: ?>
          <a href="?filter=<?= e($filter) ?>&page=<?= $p ?>"><?= $p ?></a>
        <?php endif; ?>
      <?php endfor; ?>
    </div>
  <?php endif; ?>
</section>
</main>
</body>
</html>
