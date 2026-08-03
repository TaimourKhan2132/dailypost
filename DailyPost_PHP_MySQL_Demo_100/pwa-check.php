<?php
// ---------------------------------------------------------------
// Diagnostic page for the installable-app feature.
//
// Chrome will not say why it is refusing to offer an install, so
// this checks each requirement in turn and reports which one is
// unmet. Not linked from anywhere; visit it directly.
// ---------------------------------------------------------------

$dir  = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
$base = rtrim($dir, '/') . '/';
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Install check — DailyPost</title>
<link rel="manifest" href="<?= htmlspecialchars($base) ?>manifest.json">
<style>
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;background:#0f1115;color:#f3f4f6;margin:0;padding:20px;line-height:1.5}
.wrap{max-width:640px;margin:0 auto}
h1{font-size:22px;margin:0 0 4px}
.sub{color:#9ca3af;font-size:14px;margin:0 0 20px}
.row{display:flex;gap:12px;align-items:flex-start;padding:13px 14px;border:1px solid #2a2f3a;border-radius:10px;margin-bottom:9px;background:#171a21}
.dot{width:20px;height:20px;border-radius:50%;flex-shrink:0;display:grid;place-items:center;font-size:12px;font-weight:700;margin-top:1px}
.ok{background:#16a34a;color:#fff}.bad{background:#e11d48;color:#fff}.wait{background:#6b7280;color:#fff}
.row b{display:block;font-size:14.5px}
.row span{font-size:13px;color:#9ca3af}
button{background:#e11d48;color:#fff;border:0;border-radius:8px;padding:11px 18px;font:inherit;font-weight:600;cursor:pointer;margin-top:14px}
button.g{background:#374151}
pre{background:#0b0d12;border:1px solid #2a2f3a;border-radius:8px;padding:12px;font-size:12px;overflow:auto;color:#9ca3af}
</style>
</head>
<body>
<div class="wrap">
  <h1>Install check</h1>
  <p class="sub">Every one of these must pass before a phone will offer to install DailyPost.</p>

  <div id="rows"></div>

  <button id="installNow" hidden>Install now</button>
  <button class="g" id="reset">Clear saved dismissal &amp; reload</button>

  <h3 style="font-size:15px;margin:22px 0 6px">Manifest as the browser sees it</h3>
  <pre id="mf">loading…</pre>
</div>

<script>
var rows = document.getElementById('rows');
function add(state, title, detail) {
  var sym = state === 'ok' ? '✓' : state === 'bad' ? '✗' : '…';
  rows.insertAdjacentHTML('beforeend',
    '<div class="row"><span class="dot ' + state + '">' + sym + '</span>' +
    '<div><b>' + title + '</b><span>' + detail + '</span></div></div>');
}

// 1. HTTPS
var secure = location.protocol === 'https:' || location.hostname === 'localhost';
add(secure ? 'ok' : 'bad', 'Secure connection',
    secure ? location.protocol + '//' + location.host : 'Install requires HTTPS.');

// 2. Already installed?
var standalone = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone === true;
add(standalone ? 'bad' : 'ok', 'Not already installed',
    standalone ? 'You are already running the installed app - that is why there is no button.'
               : 'Running in a normal browser tab.');

// 3. Previously dismissed?
var dismissed = localStorage.getItem('dp-install-dismissed') === '1';
add(dismissed ? 'bad' : 'ok', 'Prompt not dismissed before',
    dismissed ? 'You dismissed it earlier. Use the grey button below to clear that.'
              : 'No saved dismissal.');

// 4. Manifest
fetch('<?= htmlspecialchars($base) ?>manifest.json', { cache: 'no-store' })
  .then(function (r) {
    if (!r.ok) throw new Error('HTTP ' + r.status);
    return r.json();
  })
  .then(function (j) {
    var icons = (j.icons || []).map(function (i) { return i.sizes; });
    var has192 = icons.indexOf('192x192') > -1, has512 = icons.indexOf('512x512') > -1;
    add(has192 && has512 ? 'ok' : 'bad', 'Manifest',
        'name=' + j.name + ' · start_url=' + j.start_url + ' · icons: ' + icons.join(', '));
    document.getElementById('mf').textContent = JSON.stringify(j, null, 2);
  })
  .catch(function (e) {
    add('bad', 'Manifest', 'Could not load manifest.json — ' + e.message);
    document.getElementById('mf').textContent = 'Failed: ' + e.message;
  });

// 5. Service worker
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('<?= htmlspecialchars($base) ?>sw.js')
    .then(function (reg) {
      add('ok', 'Service worker registered', 'scope: ' + reg.scope);
    })
    .catch(function (e) {
      add('bad', 'Service worker', 'Registration failed — ' + e.message);
    });
} else {
  add('bad', 'Service worker', 'This browser does not support service workers.');
}

// 6. The install event itself
var fired = false, deferred = null;
window.addEventListener('beforeinstallprompt', function (e) {
  e.preventDefault();
  deferred = e;
  fired = true;
  add('ok', 'Browser offered an install', 'Press "Install now" below.');
  document.getElementById('installNow').hidden = false;
});

document.getElementById('installNow').addEventListener('click', function () {
  if (deferred) { deferred.prompt(); }
});

document.getElementById('reset').addEventListener('click', function () {
  localStorage.removeItem('dp-install-dismissed');
  location.reload();
});

// Chrome only fires the event after it decides you have engaged with
// the site, so a short wait before declaring failure is honest.
setTimeout(function () {
  if (!fired && !standalone) {
    var iOS = /iphone|ipad|ipod/i.test(navigator.userAgent);
    add(iOS ? 'ok' : 'wait',
        iOS ? 'iPhone: install is manual' : 'Browser has not offered an install yet',
        iOS ? 'Safari has no install button for sites to trigger. Use Share → Add to Home Screen.'
            : 'Chrome waits until you have used the site a little. Browse a few pages, then come back. ' +
              'On desktop, look for an install icon at the right of the address bar.');
  }
}, 4000);
</script>
</body>
</html>
