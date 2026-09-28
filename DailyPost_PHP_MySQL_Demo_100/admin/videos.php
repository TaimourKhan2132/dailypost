<?php
// ---------------------------------------------------------------
// Manage the YouTube videos shown in the homepage "Watch" slideshow.
//
// Paste any number of links - watch links, youtu.be links, shorts
// links, links with tracking parameters still attached - and each
// one is reduced to its video id. Nothing is uploaded or stored
// here except that id and a title.
// ---------------------------------------------------------------

require '../config/db.php';

require_admin();

$report = null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $action = $_POST['action'] ?? '';
    $id     = (int) ($_POST['id'] ?? 0);

    if ($action === 'add') {
        $raw = trim($_POST['urls'] ?? '');

        // Titles are looked up by the browser before submitting -
        // see the script at the bottom. The server never has to
        // reach out to YouTube, which matters because free hosting
        // often blocks outbound requests.
        $titles = json_decode($_POST['titles'] ?? '', true);
        if (!is_array($titles)) {
            $titles = [];
        }

        $lines = preg_split('/[\s,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (!$lines) {
            $errors[] = 'Paste at least one YouTube link.';
        } elseif (count($lines) > 60) {
            $errors[] = 'That is more than 60 links at once. Please add them in smaller batches.';
        } else {
            // One lookup of what already exists, rather than relying
            // on rowCount() after an upsert.
            $existing = [];
            foreach ($pdo->query("SELECT youtube_id FROM videos") as $row) {
                $existing[$row['youtube_id']] = true;
            }

            $order = (int) $pdo->query("SELECT COALESCE(MAX(sort_order), 0) FROM videos")->fetchColumn();

            $ins = $pdo->prepare(
                "INSERT INTO videos (youtube_id, title, status, sort_order, created_at)
                 VALUES (?, ?, 'published', ?, NOW())"
            );
            // Only overwrite a stored title when a new one was found,
            // so re-pasting a link never blanks a title.
            $upd = $pdo->prepare("UPDATE videos SET title = COALESCE(?, title) WHERE youtube_id = ?");

            $added = $updated = 0;
            $bad   = [];
            $seen  = [];

            foreach ($lines as $line) {
                $vid = youtube_id($line);

                if ($vid === null) {
                    $bad[] = $line;
                    continue;
                }
                if (isset($seen[$vid])) {
                    continue;          // same video twice in one paste
                }
                $seen[$vid] = true;

                $title = isset($titles[$vid]) && trim((string) $titles[$vid]) !== ''
                    ? mb_substr(trim((string) $titles[$vid]), 0, 200)
                    : null;

                if (isset($existing[$vid])) {
                    $upd->execute([$title, $vid]);
                    $updated++;
                } else {
                    $ins->execute([$vid, $title, ++$order]);
                    $added++;
                }
            }

            $report = ['added' => $added, 'updated' => $updated, 'bad' => $bad];
        }
    }

    if ($id > 0) {
        switch ($action) {
            case 'delete':
                $pdo->prepare("DELETE FROM videos WHERE id = ?")->execute([$id]);
                break;

            case 'toggle':
                $pdo->prepare(
                    "UPDATE videos SET status = IF(status = 'published', 'hidden', 'published') WHERE id = ?"
                )->execute([$id]);
                break;

            case 'up':
            case 'down':
                // Swap places with the neighbour in that direction.
                $cur = $pdo->prepare("SELECT id, sort_order FROM videos WHERE id = ?");
                $cur->execute([$id]);
                $me = $cur->fetch();

                if ($me) {
                    $cmp  = $action === 'up' ? '<' : '>';
                    $dir  = $action === 'up' ? 'DESC' : 'ASC';
                    $nq   = $pdo->prepare(
                        "SELECT id, sort_order FROM videos
                         WHERE sort_order $cmp ? ORDER BY sort_order $dir LIMIT 1"
                    );
                    $nq->execute([$me['sort_order']]);
                    $other = $nq->fetch();

                    if ($other) {
                        $sw = $pdo->prepare("UPDATE videos SET sort_order = ? WHERE id = ?");
                        $sw->execute([$other['sort_order'], $me['id']]);
                        $sw->execute([$me['sort_order'], $other['id']]);
                    }
                }
                break;
        }
    }

    if (!$errors && !$report) {
        header('Location: videos.php');
        exit;
    }
}

$videos = $pdo->query("SELECT * FROM videos ORDER BY sort_order, id")->fetchAll();
$live   = 0;
foreach ($videos as $v) {
    if ($v['status'] === 'published') {
        $live++;
    }
}
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Videos — DailyPost Admin</title>
<link rel="stylesheet" href="../assets/css/dailypost.css">
<style>
body{background:var(--bg)}
.adminbar{background:var(--surface);border-bottom:1px solid var(--border);padding:0 24px;height:64px;display:flex;align-items:center;gap:16px}
.adminbar .name{font-size:24px;font-weight:800;letter-spacing:-.03em}
.adminbar .name span{color:var(--accent)}
.adminbar .sp{margin-left:auto}
.panel-box{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:22px;margin-bottom:18px}
.vid-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:16px}
.vid{border:1px solid var(--border);border-radius:10px;overflow:hidden;background:var(--surface)}
.vid .thumb{position:relative;aspect-ratio:16/9;background:#000}
.vid .thumb img{width:100%;height:100%;object-fit:cover}
.vid .hidden-flag{position:absolute;inset:0;background:rgba(0,0,0,.62);color:#fff;display:grid;place-items:center;font-size:13px;font-weight:700;letter-spacing:.05em}
.vid .meta{padding:11px 12px}
.vid .meta b{display:block;font-size:13.5px;line-height:1.35;margin-bottom:3px}
.vid .meta small{color:var(--text-muted);font-size:11.5px;word-break:break-all}
.vid .acts{display:flex;gap:5px;flex-wrap:wrap;padding:0 12px 12px}
.vid .acts form{display:inline}
textarea.urls{width:100%;min-height:150px;padding:12px;border:1px solid var(--border);border-radius:8px;background:var(--surface);color:var(--text);font:inherit;font-size:13.5px;line-height:1.6}
.btn-sm{padding:6px 11px;border:0;border-radius:5px;cursor:pointer;font-size:12.5px;background:#171717;color:#fff;text-decoration:none;display:inline-block}
.btn-sm.ghost{background:var(--surface-2);color:var(--text);border:1px solid var(--border)}
.btn-sm.danger{background:#b91c1c;color:#fff}
</style>
</head>
<body>

<header class="adminbar">
  <a class="name" href="../index.php">Daily<span>Post</span></a>
  <span style="color:var(--text-muted);font-size:14px">Videos</span>
  <span class="sp"></span>
  <a class="btn ghost" href="dashboard.php">Submissions</a>
  <a class="btn ghost" href="../index.php">View site</a>
  <a class="btn ghost" href="logout.php">Logout</a>
</header>

<main class="wrap" style="max-width:1100px;padding-top:24px">

  <h1 style="font-size:28px;font-weight:800;letter-spacing:-.025em;margin:0 0 6px">Videos</h1>
  <p style="color:var(--text-muted);margin:0 0 20px">
    <b><?= $live ?></b> showing on the homepage<?= count($videos) > $live ? ', ' . (count($videos) - $live) . ' hidden' : '' ?>.
  </p>

  <?php if ($report): ?>
    <div class="notice">
      <b>Done.</b>
      <?= $report['added'] ?> added<?= $report['updated'] ? ', ' . $report['updated'] . ' already there (title refreshed)' : '' ?>.
      <?php if ($report['bad']): ?>
        <div style="margin-top:6px">
          Not recognised as YouTube links and skipped:
          <?php foreach (array_slice($report['bad'], 0, 8) as $b): ?>
            <div style="font-size:13px">• <?= e(mb_strimwidth($b, 0, 90, '…')) ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <?php if ($errors): ?>
    <div class="notice error">
      <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="panel-box">
    <h2 style="font-size:18px;font-weight:700;margin:0 0 6px">Add videos</h2>
    <p style="color:var(--text-muted);font-size:14px;margin:0 0 14px">
      Paste YouTube links, one per line. Any format works — a normal watch link,
      a youtu.be link, or a link copied with extra tracking text on the end.
      Titles are filled in automatically.
    </p>

    <form method="post" id="addForm">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add">
      <input type="hidden" name="titles" id="titlesField" value="">
      <textarea class="urls" name="urls" id="urlsField" placeholder="https://www.youtube.com/watch?v=...
https://youtu.be/...
https://www.youtube.com/watch?v=..."></textarea>
      <div style="margin-top:12px;display:flex;gap:10px;align-items:center">
        <button class="btn" type="submit" id="addBtn">Add videos</button>
        <span id="addStatus" style="font-size:13.5px;color:var(--text-muted)"></span>
      </div>
    </form>
  </div>

  <?php if ($videos): ?>
    <div class="vid-grid">
      <?php foreach ($videos as $i => $v): ?>
        <div class="vid">
          <div class="thumb">
            <img src="<?= e(youtube_thumb($v['youtube_id'], 'hqdefault')) ?>" alt="" loading="lazy"
                 onerror="this.onerror=null;this.src='<?= e(youtube_thumb($v['youtube_id'], 'hqdefault')) ?>'">
            <?php if ($v['status'] !== 'published'): ?>
              <span class="hidden-flag">HIDDEN</span>
            <?php endif; ?>
          </div>
          <div class="meta">
            <b><?= e($v['title'] ?: 'Untitled video') ?></b>
            <small><?= e($v['youtube_id']) ?></small>
          </div>
          <div class="acts">
            <?php foreach ([['up', '↑'], ['down', '↓'], ['toggle', $v['status'] === 'published' ? 'Hide' : 'Show']] as [$act, $label]): ?>
              <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $v['id'] ?>">
                <button class="btn-sm ghost" name="action" value="<?= $act ?>" type="submit"><?= $label ?></button>
              </form>
            <?php endforeach; ?>
            <a class="btn-sm ghost" href="<?= e(youtube_watch($v['youtube_id'])) ?>" target="_blank" rel="noopener">Open</a>
            <form method="post" onsubmit="return confirm('Remove this video from the site?')">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int) $v['id'] ?>">
              <button class="btn-sm danger" name="action" value="delete" type="submit">Delete</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p style="color:var(--text-muted)">No videos yet. Paste some links above and they will appear on the homepage.</p>
  <?php endif; ?>

</main>

<script>
// Look up each video's title in the browser before submitting.
// YouTube's oEmbed endpoint needs no API key. Doing it here rather
// than on the server means it still works on hosting that blocks
// outbound connections - and if it fails for any reason the video is
// saved anyway, just without a title.
document.getElementById('addForm').addEventListener('submit', function (e) {
  var form   = e.target;
  var field  = document.getElementById('urlsField');
  var status = document.getElementById('addStatus');
  var btn    = document.getElementById('addBtn');

  if (form.dataset.ready === '1') { return; }   // second pass: let it go
  e.preventDefault();

  var ids = [];
  field.value.split(/[\s,]+/).forEach(function (raw) {
    if (!raw) return;
    var m = raw.match(/(?:v=|\/shorts\/|\/embed\/|\/v\/|\/live\/|youtu\.be\/)([A-Za-z0-9_-]{11})/)
         || raw.match(/^([A-Za-z0-9_-]{11})$/);
    if (m && ids.indexOf(m[1]) === -1) ids.push(m[1]);
  });

  if (!ids.length) { form.dataset.ready = '1'; form.submit(); return; }

  btn.disabled = true;
  status.textContent = 'Looking up ' + ids.length + ' video' + (ids.length === 1 ? '' : 's') + '…';

  var titles = {};
  var done = function () {
    document.getElementById('titlesField').value = JSON.stringify(titles);
    form.dataset.ready = '1';
    form.submit();
  };

  // Never let a slow lookup hold up saving.
  var bail = setTimeout(done, 6000);

  Promise.all(ids.map(function (id) {
    var url = 'https://www.youtube.com/oembed?format=json&url='
            + encodeURIComponent('https://www.youtube.com/watch?v=' + id);
    return fetch(url)
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (j) { if (j && j.title) titles[id] = j.title; })
      .catch(function () { /* saved without a title */ });
  })).then(function () {
    clearTimeout(bail);
    done();
  });
});
</script>
</body>
</html>
