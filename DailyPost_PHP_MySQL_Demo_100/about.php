<?php
require 'config/db.php';

dp_session_start();

$categories = [];
foreach ($pdo->query("SELECT * FROM categories ORDER BY sort_order") as $c) {
    $categories[$c['slug']] = $c;
}

$stats = $pdo->query(
    "SELECT COUNT(*) AS stories, COUNT(DISTINCT author) AS authors, SUM(views) AS views
     FROM stories WHERE status = 'published'"
)->fetch();

$page_title = 'About — DailyPost';
$active_nav = 'about';

require 'includes/header.php';
?>

<div class="wrap">
<div class="article">
  <h1>About DailyPost</h1>
  <p class="lead">
    A simple place for good stories. Anyone can write one, everyone can read them.
  </p>

  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin:28px 0 34px">
    <?php foreach ([
      ['Stories published', number_format((int) $stats['stories'])],
      ['Writers',           number_format((int) $stats['authors'])],
      ['Total reads',       number_format((int) $stats['views'])],
    ] as [$label, $value]): ?>
      <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:20px;text-align:center">
        <div style="font-size:30px;font-weight:800;letter-spacing:-.03em"><?= $value ?></div>
        <div style="font-size:13px;color:var(--text-muted);margin-top:2px"><?= $label ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="content">
    <p>
      DailyPost exists for one reason: most people have a story worth telling, and
      almost none of them want to run a website to tell it. Here you write, you
      submit, and it is read. Nothing else to learn.
    </p>

    <h2 style="font-size:23px;font-weight:800;margin:32px 0 12px">How publishing works</h2>
    <p>
      Every submission is read by an editor before it appears. That keeps the site
      free of spam and keeps the quality up, and it means nothing goes live that
      nobody has looked at.
    </p>
    <p style="color:var(--text-muted)">
      Write → Submit → Reviewed by an editor → Published → Permanent.
    </p>

    <h2 style="font-size:23px;font-weight:800;margin:32px 0 12px">What we publish</h2>
    <p>
      Anything thoughtful and your own. Stories are grouped into categories so
      readers can find what interests them:
    </p>
    <p style="display:flex;flex-wrap:wrap;gap:8px">
      <?php foreach ($categories as $c): ?>
        <a href="search.php?category=<?= e($c['slug']) ?>" class="badge"
           style="position:static;background:<?= e($c['badge_color']) ?>"><?= e($c['name']) ?></a>
      <?php endforeach; ?>
    </p>

    <h2 style="font-size:23px;font-weight:800;margin:32px 0 12px">A few ground rules</h2>
    <p>
      Write your own work. Don't publish anything you wouldn't put your name to.
      Published stories can't be edited by their authors afterwards, so read it
      once more before you press submit.
    </p>
  </div>

  <a class="btn" href="write.php">Write a story →</a>
</div>
</div>

<?php require 'includes/footer.php'; ?>
