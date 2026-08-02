<?php
require '../config/db.php';

dp_session_start();

if (!empty($_SESSION['admin'])) {
    header('Location: dashboard.php');
    exit;
}
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>DailyPost Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<main>
<section class="write">
  <h1>DailyPost Admin</h1>

  <?php if (isset($_GET['locked'])): ?>
    <div class="notice error">Too many failed attempts. Please wait 15 minutes and try again.</div>
  <?php elseif (isset($_GET['error'])): ?>
    <div class="notice error">Invalid login.</div>
  <?php endif; ?>

  <form method="post" action="auth.php">
    <?= csrf_field() ?>
    <label>Username<input name="username" required autocomplete="username"></label>
    <label>Password<input type="password" name="password" required autocomplete="current-password"></label>
    <button class="button">Login →</button>
  </form>
</section>
</main>
</body>
</html>
