<?php
// Shown by the service worker when there is no connection.
// Deliberately standalone - it must not need the database, since
// the whole point is that nothing can be reached.

$base = '';
// Work out the site root without helpers.php, which needs the DB.
$dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
$base = rtrim($dir, '/') . '/';
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>No connection — DailyPost</title>
<meta name="theme-color" content="#e11d48">
<link rel="icon" href="<?= htmlspecialchars($base) ?>assets/favicon-32.png">
<style>
:root { color-scheme: light dark; }
body {
  margin: 0; min-height: 100vh;
  display: grid; place-items: center;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  background: #f7f7f8; color: #111827;
  padding: 24px; text-align: center;
}
@media (prefers-color-scheme: dark) {
  body { background: #0f1115; color: #f3f4f6; }
  .card { background: #171a21 !important; border-color: #2a2f3a !important; }
  p { color: #9ca3af !important; }
}
.card {
  background: #fff; border: 1px solid #e5e7eb; border-radius: 14px;
  padding: 40px 32px; max-width: 420px;
}
.mark { font-size: 34px; font-weight: 800; letter-spacing: -.03em; margin-bottom: 18px; }
.mark span { color: #e11d48; }
h1 { font-size: 22px; font-weight: 700; margin: 0 0 10px; }
p { color: #6b7280; margin: 0 0 22px; line-height: 1.6; font-size: 15px; }
button {
  background: #e11d48; color: #fff; border: 0; border-radius: 8px;
  padding: 12px 22px; font: inherit; font-weight: 600; cursor: pointer;
}
button:hover { background: #be123c; }
</style>
</head>
<body>
  <div class="card">
    <div class="mark">Daily<span>Post</span></div>
    <h1>You're offline</h1>
    <p>
      DailyPost needs a connection to load new stories. Pages you have already
      read are still available — check your internet and try again.
    </p>
    <button onclick="location.reload()">Try again</button>
  </div>

  <script>
    // Reload by itself the moment the connection comes back.
    window.addEventListener('online', function () { location.reload(); });
  </script>
</body>
</html>
