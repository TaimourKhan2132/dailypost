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
<link rel="stylesheet" href="../assets/css/dailypost.css">
</head>
<body>
<main class="wrap">
<section class="formwrap" style="max-width:420px">
  <a class="logo" href="../index.php" style="display:block;margin-bottom:22px">
    <div class="name" style="font-size:30px;font-weight:800;letter-spacing:-.03em">Daily<span style="color:var(--accent)">Post</span></div>
  </a>
  <h1 style="font-size:26px">Admin sign in</h1>
  <p class="sub">Editors only. Writers don't need an account — just <a href="../write.php" style="color:var(--accent)">submit a story</a>.</p>

  <?php if (isset($_GET['locked'])): ?>
    <div class="notice error">Too many failed attempts. Please wait 15 minutes and try again.</div>
  <?php elseif (isset($_GET['error'])): ?>
    <div class="notice error">Invalid login.</div>
  <?php endif; ?>

  <form method="post" action="auth.php">
    <?= csrf_field() ?>
    <div class="field">
      <label for="u">Username</label>
      <input id="u" name="username" required autocomplete="username">
    </div>
    <div class="field">
      <label for="p">Password</label>
      <input id="p" type="password" name="password" required autocomplete="current-password">
    </div>
    <button class="btn" type="submit">Sign in →</button>
  </form>
</section>
</main>
</body>
</html>
