<?php
require 'config/db.php';

dp_session_start();

// Errors and previous input are handed over by submit.php, then
// cleared so a refresh does not show them again.
$errors = $_SESSION['form_errors'] ?? [];
$old    = $_SESSION['form_old']    ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_old']);

$categories = $pdo->query("SELECT slug, name FROM categories ORDER BY sort_order")->fetchAll();

// Small helper so each field can remember what was typed.
$v = fn(string $k): string => e($old[$k] ?? '');
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Write — DailyPost</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
/* The honeypot. Hidden from people, visible to bots that read the
   HTML rather than render it. Kept off-screen rather than
   display:none, which the better bots know to skip. */
.hp-field { position:absolute; left:-9999px; top:-9999px; height:0; overflow:hidden; }
</style>
</head>
<body>
<header>
  <a class="brand" href="index.php">Daily<span>Post</span></a>
  <nav><a href="index.php">Read</a></nav>
</header>

<main>
<section class="write">
  <small>YOUR TURN</small>
  <h1>Write something worth sharing.</h1>
  <p>Submit your story for review. Published stories cannot be edited by authors.</p>

  <?php if (isset($_GET['sent'])): ?>
    <div class="notice">Thank you. Your story was submitted for review.</div>
  <?php endif; ?>

  <?php if ($errors): ?>
    <div class="notice error">
      <?php foreach ($errors as $err): ?>
        <div><?= e($err) ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="post" action="submit.php">
    <?= csrf_field() ?>

    <div class="hp-field" aria-hidden="true">
      <label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
    </div>

    <label>Title
      <input name="title" maxlength="180" required value="<?= $v('title') ?>">
    </label>

    <label>Your name
      <input name="author" maxlength="80" required value="<?= $v('author') ?>">
    </label>

    <label>Email (optional, not published)
      <input type="email" name="email" maxlength="180" value="<?= $v('email') ?>">
    </label>

    <label>Category
      <select name="category">
        <?php foreach ($categories as $c): ?>
          <option value="<?= e($c['slug']) ?>" <?= ($old['category'] ?? '') === $c['slug'] ? 'selected' : '' ?>>
            <?= e($c['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>

    <label>Cover image link (optional)
      <input type="url" name="image_url" maxlength="500" placeholder="https://example.com/photo.jpg" value="<?= $v('image_url') ?>">
      <small class="hint">Paste a link to a photo. Leave blank and we'll use a picture for your category.</small>
    </label>

    <label>Short excerpt
      <textarea name="excerpt" maxlength="300" rows="3"><?= $v('excerpt') ?></textarea>
    </label>

    <label>Your story
      <textarea name="body" maxlength="30000" rows="14" required><?= $v('body') ?></textarea>
    </label>

    <label>Cover color
      <select name="color">
        <?php foreach (['coral','blue','green','purple','yellow'] as $c): ?>
          <option value="<?= $c ?>" <?= ($old['color'] ?? '') === $c ? 'selected' : '' ?>><?= ucfirst($c) ?></option>
        <?php endforeach; ?>
      </select>
    </label>

    <button class="button">Submit story →</button>
  </form>
</section>
</main>
</body>
</html>
