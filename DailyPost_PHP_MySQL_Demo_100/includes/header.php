<?php
// Shared top of every page.
// Set $page_title, $active_nav and optionally $meta_description
// or $og_image before including this file.

$page_title       = $page_title       ?? 'DailyPost — Stories Worth Sharing';
$active_nav       = $active_nav       ?? '';
$meta_description = $meta_description ?? 'A simple place for good stories. Read what people are sharing, or write something worth remembering.';
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($page_title) ?></title>
<meta name="description" content="<?= e($meta_description) ?>">
<meta property="og:title" content="<?= e($page_title) ?>">
<meta property="og:description" content="<?= e($meta_description) ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="DailyPost">

<?php // Tells search engines which address is the real one when a
      // page can be reached more than one way. ?>
<?php if (!empty($canonical)): ?>
<link rel="canonical" href="<?= e($canonical) ?>">
<?php endif; ?>

<link rel="icon" href="<?= $base ?? '' ?>assets/favicon.ico" sizes="any">
<link rel="icon" type="image/png" sizes="32x32" href="<?= $base ?? '' ?>assets/favicon-32.png">
<link rel="icon" type="image/png" sizes="16x16" href="<?= $base ?? '' ?>assets/favicon-16.png">
<link rel="apple-touch-icon" href="<?= $base ?? '' ?>assets/apple-touch-icon.png">
<meta name="theme-color" content="#e11d48">

<?php if (!empty($og_image)): ?>
<meta property="og:image" content="<?= e($og_image) ?>">
<meta name="twitter:card" content="summary_large_image">
<?php endif; ?>
<?php
// The ?v= number is the stylesheet's last-modified time. Browsers
// cache CSS hard, so without this a visitor keeps using the old
// file after an update - and a half-updated page (new HTML, old
// CSS) looks far more broken than either version alone. Change the
// file and the number changes, so every browser fetches it once and
// then caches the new one.
$css_path = __DIR__ . '/../assets/css/dailypost.css';
$css_v    = is_file($css_path) ? filemtime($css_path) : time();
?>
<link rel="stylesheet" href="<?= $base ?? '' ?>assets/css/dailypost.css?v=<?= $css_v ?>">

<script>
// Applied before the page paints, otherwise a dark-mode visitor
// gets a white flash on every single page load.
(function () {
  var saved = localStorage.getItem('dp-theme');
  var dark  = saved ? saved === 'dark'
                    : window.matchMedia('(prefers-color-scheme: dark)').matches;
  if (dark) document.documentElement.setAttribute('data-theme', 'dark');
})();
</script>
</head>
<body>

<div class="topbar">
  <div class="wrap">
    <span class="date"><?= date('l, F j, Y') ?></span>
    <span class="welcome">Welcome to <b>Daily<span>Post</span></b> — Stories Worth Sharing</span>
    <span class="socials">
      <a href="#" aria-label="Facebook"><svg viewBox="0 0 24 24"><path d="M22 12a10 10 0 1 0-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.4v7A10 10 0 0 0 22 12z"/></svg></a>
      <a href="#" aria-label="X"><svg viewBox="0 0 24 24"><path d="M18.9 2H22l-6.7 7.7L23 22h-6.2l-4.9-6.4L6.3 22H3.2l7.2-8.2L2 2h6.3l4.4 5.8L18.9 2zm-1.1 18h1.7L7.3 3.7H5.5L17.8 20z"/></svg></a>
      <a href="#" aria-label="Instagram"><svg viewBox="0 0 24 24"><path d="M12 2.2c3.2 0 3.6 0 4.9.1 1.2.1 1.8.3 2.2.4.6.2 1 .5 1.4.9.4.4.7.8.9 1.4.2.4.4 1 .4 2.2.1 1.3.1 1.7.1 4.9s0 3.6-.1 4.9c-.1 1.2-.3 1.8-.4 2.2-.2.6-.5 1-.9 1.4-.4.4-.8.7-1.4.9-.4.2-1 .4-2.2.4-1.3.1-1.7.1-4.9.1s-3.6 0-4.9-.1c-1.2-.1-1.8-.3-2.2-.4-.6-.2-1-.5-1.4-.9-.4-.4-.7-.8-.9-1.4-.2-.4-.4-1-.4-2.2C2.2 15.6 2.2 15.2 2.2 12s0-3.6.1-4.9c.1-1.2.3-1.8.4-2.2.2-.6.5-1 .9-1.4.4-.4.8-.7 1.4-.9.4-.2 1-.4 2.2-.4C8.4 2.2 8.8 2.2 12 2.2zm0 5.3a4.5 4.5 0 1 0 0 9 4.5 4.5 0 0 0 0-9zm0 7.4a2.9 2.9 0 1 1 0-5.8 2.9 2.9 0 0 1 0 5.8zm5.7-7.6a1 1 0 1 1-2.1 0 1 1 0 0 1 2.1 0z"/></svg></a>
      <a href="#" aria-label="YouTube"><svg viewBox="0 0 24 24"><path d="M23 12s0-3.2-.4-4.7c-.2-.8-.9-1.5-1.7-1.7C19.3 5.2 12 5.2 12 5.2s-7.3 0-8.9.4c-.8.2-1.5.9-1.7 1.7C1 8.8 1 12 1 12s0 3.2.4 4.7c.2.8.9 1.5 1.7 1.7 1.6.4 8.9.4 8.9.4s7.3 0 8.9-.4c.8-.2 1.5-.9 1.7-1.7.4-1.5.4-4.7.4-4.7zM9.7 15.1V8.9l6.1 3.1-6.1 3.1z"/></svg></a>
    </span>
  </div>
</div>

<header class="site-header">
  <div class="wrap">
    <a class="logo" href="<?= $base ?? '' ?>index.php">
      <div class="name">Daily<span>Post</span></div>
      <div class="tag">Stories Worth Sharing</div>
    </a>

    <nav class="mainnav">
      <a href="<?= $base ?? '' ?>index.php" class="<?= $active_nav === 'read' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/></svg>
        <span>Read</span>
      </a>
      <a href="<?= $base ?? '' ?>write.php" class="<?= $active_nav === 'write' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
        <span>Write</span>
      </a>
      <a href="<?= $base ?? '' ?>search.php" class="<?= $active_nav === 'search' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        <span>Search</span>
      </a>
      <a href="<?= $base ?? '' ?>about.php" class="<?= $active_nav === 'about' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
        <span>About</span>
      </a>
    </nav>

    <div class="header-actions">
      <button class="theme-toggle" id="themeToggle" aria-label="Switch between light and dark">
        <svg class="sun" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4.2"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
        <svg class="moon" viewBox="0 0 24 24"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>
      </button>
      <?php // The old Sign In button lived here. It was only ever
            // the editor's door, so having it in the header invited
            // readers to try creating accounts that do not exist.
            // It now sits quietly in the footer instead. ?>
      <a class="btn" href="<?= $base ?? '' ?>write.php">Write a story</a>
    </div>
  </div>
</header>
