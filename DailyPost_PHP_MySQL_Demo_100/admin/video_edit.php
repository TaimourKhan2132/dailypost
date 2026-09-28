<?php
// ---------------------------------------------------------------
// Edit one video's title and the text that appears under it on its
// own page.
// ---------------------------------------------------------------

require '../config/db.php';

require_admin();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

// This page needs the columns from migration 007. videos.php shows
// the setup instructions, so send anyone who arrives early there
// rather than throwing.
try {
    $pdo->query("SELECT slug, description, views FROM videos LIMIT 1");
} catch (PDOException $e) {
    header('Location: videos.php');
    exit;
}

$q = $pdo->prepare("SELECT * FROM videos WHERE id = ?");
$q->execute([$id]);
$v = $q->fetch();

if (!$v) {
    header('Location: videos.php');
    exit;
}

$errors = [];
$saved  = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $title = trim($_POST['title'] ?? '');
    $desc  = trim($_POST['description'] ?? '');

    if (mb_strlen($title) > 200) {
        $errors[] = 'Title must be 200 characters or fewer.';
    }
    if (mb_strlen($desc) > 30000) {
        $errors[] = 'The text is too long. The limit is 30,000 characters.';
    }

    if (!$errors) {
        // Re-slug when the title changes, but keep the existing slug
        // otherwise so links that are already out there keep working.
        $slug = $v['slug'];
        if (trim((string) $slug) === '' || $title !== (string) $v['title']) {
            $slug = unique_video_slug($pdo, $title, $v['youtube_id'], $id);
        }

        $pdo->prepare("UPDATE videos SET title = ?, description = ?, slug = ? WHERE id = ?")
            ->execute([$title !== '' ? $title : null, $desc !== '' ? $desc : null, $slug, $id]);

        $q->execute([$id]);
        $v     = $q->fetch();
        $saved = true;
    }
}

$page = $v['title'] ?: 'Untitled video';
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Edit video — DailyPost Admin</title>
<link rel="stylesheet" href="../assets/css/dailypost.css">
<style>
body{background:var(--bg)}
.adminbar{background:var(--surface);border-bottom:1px solid var(--border);padding:0 24px;height:64px;display:flex;align-items:center;gap:16px}
.adminbar .name{font-size:24px;font-weight:800;letter-spacing:-.03em}
.adminbar .name span{color:var(--accent)}
.adminbar .sp{margin-left:auto}
.preview{display:flex;gap:16px;align-items:flex-start;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:16px;margin-bottom:20px}
.preview img{width:200px;aspect-ratio:16/9;object-fit:cover;border-radius:8px;flex-shrink:0;background:#000}
.preview small{color:var(--text-muted);font-size:12.5px;word-break:break-all}
</style>
</head>
<body>

<header class="adminbar">
  <a class="name" href="../index.php">Daily<span>Post</span></a>
  <span style="color:var(--text-muted);font-size:14px">Edit video</span>
  <span class="sp"></span>
  <a class="btn ghost" href="videos.php">All videos</a>
  <a class="btn ghost" href="dashboard.php">Submissions</a>
</header>

<main class="wrap" style="max-width:780px;padding-top:24px">

  <?php if ($saved): ?>
    <div class="notice">
      Saved.
      <a href="../<?= e(video_path($v)) ?>" target="_blank" rel="noopener" style="color:inherit;text-decoration:underline">View the page</a>
    </div>
  <?php endif; ?>

  <?php if ($errors): ?>
    <div class="notice error">
      <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="preview">
    <img src="<?= e(youtube_thumb($v['youtube_id'], 'hqdefault')) ?>" alt="">
    <div>
      <b style="display:block;margin-bottom:4px"><?= e($page) ?></b>
      <small>
        <?= e($v['youtube_id']) ?><br>
        <?= (int) $v['views'] ?> view<?= (int) $v['views'] === 1 ? '' : 's' ?> ·
        <?= $v['slug'] ? '/watch/' . e($v['slug']) : 'no address yet' ?>
      </small>
    </div>
  </div>

  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int) $v['id'] ?>">

    <div class="field">
      <label for="title">Title</label>
      <input id="title" name="title" maxlength="200" value="<?= e($v['title'] ?? '') ?>"
             placeholder="Shown on the card and as the page heading">
      <span class="hint">Changing this also changes the page address.</span>
    </div>

    <div class="field">
      <label for="description">Text under the video</label>
      <textarea id="description" name="description" maxlength="30000"
                placeholder="Write about the video here. Leave a blank line between paragraphs."><?= e($v['description'] ?? '') ?></textarea>
      <span class="hint">This is what a reader sees below the player, the same as a story.</span>
    </div>

    <button class="btn" type="submit">Save</button>
    <a class="btn ghost" href="videos.php">Cancel</a>
  </form>

</main>
</body>
</html>
