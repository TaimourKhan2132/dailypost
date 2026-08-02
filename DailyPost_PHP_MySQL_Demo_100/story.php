<?php
require 'config/db.php';

dp_session_start();

$id = (int) ($_GET['id'] ?? 0);

$q = $pdo->prepare("SELECT * FROM stories WHERE id = ? AND status = 'published'");
$q->execute([$id]);
$s = $q->fetch();

if (!$s) {
    http_response_code(404);
    exit('Story not found.');
}

// --- VIEW COUNTER -----------------------------------------------
// The original added a view on every single page load, so hitting
// refresh ten times counted ten reads. Now one visitor counts once
// per story per browsing session.
if (empty($_SESSION['viewed'][$id])) {
    $pdo->prepare("UPDATE stories SET views = views + 1 WHERE id = ?")->execute([$id]);
    $_SESSION['viewed'][$id] = true;
    $s['views']++;
}

$excerpt = $s['excerpt'] ?: mb_substr($s['body'], 0, 160);
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($s['title']) ?> — DailyPost</title>

<!-- Description and the og: tags below are what WhatsApp, Facebook
     and X read when someone shares the link. Without them a shared
     story shows up as a bare URL with no title or picture. -->
<meta name="description" content="<?= e($excerpt) ?>">
<meta property="og:type" content="article">
<meta property="og:title" content="<?= e($s['title']) ?>">
<meta property="og:description" content="<?= e($excerpt) ?>">
<?php if (is_safe_image_url($s['image_url'])): ?>
  <meta property="og:image" content="<?= e($s['image_url']) ?>">
<?php endif; ?>
<meta name="twitter:card" content="summary_large_image">

<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header>
  <a class="brand" href="index.php">Daily<span>Post</span></a>
  <nav><a href="index.php">Read</a><a href="write.php">Write</a></nav>
</header>

<main>
<article class="article">
  <?php if (is_safe_image_url($s['image_url'])): ?>
    <img src="<?= e($s['image_url']) ?>" alt=""
         style="width:100%;max-height:420px;object-fit:cover"
         onerror="this.remove()">
  <?php else: ?>
    <div class="art <?= e($s['color']) ?> big"><?= e(mb_strtoupper(mb_substr($s['title'], 0, 1))) ?></div>
  <?php endif; ?>

  <small>
    BY <?= e(mb_strtoupper($s['author'])) ?>
    · <?= $s['published_at'] ? date('M j, Y', strtotime($s['published_at'])) : '' ?>
    · <?= read_time($s['body']) ?> MIN READ
  </small>

  <h1><?= e($s['title']) ?></h1>

  <?php if ($s['excerpt']): ?>
    <p class="lead"><?= e($s['excerpt']) ?></p>
  <?php endif; ?>

  <!-- htmlspecialchars first, then nl2br. Doing it in this order
       means the writer's line breaks become <br> tags, but any HTML
       they typed stays inert text. -->
  <div class="body"><?= nl2br(e($s['body'])) ?></div>

  <a href="index.php">← Back to DailyPost</a>
</article>
</main>
</body>
</html>
