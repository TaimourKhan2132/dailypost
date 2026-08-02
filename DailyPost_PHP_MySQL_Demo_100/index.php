<?php
require 'config/db.php';

dp_session_start();

// --- PAGINATION -------------------------------------------------
// The original loaded every published story on every page view.
// That is 100 rows today and unbounded later - the page would get
// slower every time someone published something.

$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 12;
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
$stories = $q->fetchAll();
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>DailyPost — Stories worth sharing.</title>
<meta name="description" content="A simple place for good stories. Read what people are sharing, or write something worth remembering.">
<link rel="stylesheet" href="assets/css/style.css">
<style>
.hp-field{position:absolute;left:-9999px;top:-9999px;height:0;overflow:hidden}
.pager{display:flex;gap:8px;justify-content:center;margin:30px 0;flex-wrap:wrap}
.pager a,.pager span{padding:9px 14px;border:1px solid #ddd;text-decoration:none;color:#333}
.pager span.cur{background:#171717;color:#fff;border-color:#171717}
</style>
</head>
<body>
<header>
  <a class="brand" href="index.php">Daily<span>Post</span></a>
  <span>Stories worth sharing.</span>
  <nav>
    <a href="#read">Read</a>
    <a href="write.php">Write</a>
    <a href="#newsletter">Newsletter</a>
    <a href="admin/login.php">Admin</a>
  </nav>
</header>

<div class="ticker">DAILYPOST · Ideas, stories and voices from people everywhere.</div>

<main>
<section class="hero">
  <div>
    <small>TODAY</small>
    <h1>A simple place<br>for good stories.</h1>
    <p>Read what people are sharing. Write something worth remembering.</p>
    <a class="button" href="write.php">Write a story →</a>
  </div>
  <div class="poster">READ.<br>WRITE.<br>SHARE.</div>
</section>

<section id="read">
  <div class="head">
    <h2>Latest Stories</h2>
    <span><?= $total ?> stories</span>
  </div>

  <div class="grid">
    <?php foreach ($stories as $s): ?>
      <article class="card">
        <div class="art <?= e($s['color']) ?>"><?= e(mb_strtoupper(mb_substr($s['title'], 0, 1))) ?></div>
        <div class="copy">
          <h3><?= e($s['title']) ?></h3>
          <p><?= e($s['excerpt']) ?></p>
          <small>
            By <?= e($s['author']) ?>
            · <?= $s['published_at'] ? date('M j, Y', strtotime($s['published_at'])) : '' ?>
            · <?= read_time($s['body']) ?> min read
          </small>
          <a class="read" href="story.php?id=<?= (int) $s['id'] ?>">Read story →</a>
        </div>
      </article>
    <?php endforeach; ?>
  </div>

  <?php if ($pages > 1): ?>
    <div class="pager">
      <?php if ($page > 1): ?><a href="?page=<?= $page - 1 ?>#read">← Previous</a><?php endif; ?>
      <span class="cur">Page <?= $page ?> of <?= $pages ?></span>
      <?php if ($page < $pages): ?><a href="?page=<?= $page + 1 ?>#read">Next →</a><?php endif; ?>
    </div>
  <?php endif; ?>
</section>

<section class="feature">
  <div>
    <small>MOST READ</small>
    <h2>Your next favorite story is waiting.</h2>
    <p>Readers discover new voices every day.</p>
  </div>
  <div>
    <small>EDITOR'S PICKS</small>
    <h2>Small stories. Big ideas.</h2>
  </div>
</section>

<section id="newsletter" class="newsletter">
  <small>NEWSLETTER</small>
  <h2>One good email. Every day.</h2>

  <?php if (isset($_GET['subscribed'])): ?>
    <p><?= $_GET['subscribed'] === '1'
        ? 'Thanks — you are on the list.'
        : 'That email address did not look right. Please try again.' ?></p>
  <?php endif; ?>

  <form method="post" action="subscribe.php">
    <?= csrf_field() ?>
    <div class="hp-field" aria-hidden="true">
      <label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
    </div>
    <input type="email" name="email" required placeholder="you@example.com" maxlength="180">
    <button>Subscribe</button>
  </form>
</section>
</main>

<footer>
  <b>DailyPost</b>
  <span>Stories worth sharing.</span>
  <small>© <?= date('Y') ?> DailyPost</small>
</footer>
</body>
</html>
