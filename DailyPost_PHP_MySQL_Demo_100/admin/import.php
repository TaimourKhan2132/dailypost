<?php
// ---------------------------------------------------------------
// Bulk import of stories from a spreadsheet.
//
// The workflow this exists for:
//   1. Download the CSV (or the template)
//   2. Edit it in Excel or Google Sheets
//   3. Upload it here
//
// Rows that carry an id update the matching story. Rows with a
// blank id become new stories. Nothing is written unless every row
// passes validation - a partial import is far harder for someone
// to untangle than a rejected one.
// ---------------------------------------------------------------

require '../config/db.php';

require_admin();

$report = null;
$errors = [];

$validCategories = $pdo->query("SELECT slug FROM categories")->fetchAll(PDO::FETCH_COLUMN);
$validStatuses   = ['published', 'pending', 'rejected'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $mode = $_POST['mode'] ?? 'update';
    if (!in_array($mode, ['update', 'replace'], true)) {
        $mode = 'update';
    }

    // --- The upload itself -------------------------------------
    if (!isset($_FILES['csv']) || $_FILES['csv']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = match ($_FILES['csv']['error'] ?? UPLOAD_ERR_NO_FILE) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE =>
                'That file is too large for the server to accept. Split it into two smaller files and import them one after the other.',
            UPLOAD_ERR_NO_FILE => 'Please choose a file first.',
            UPLOAD_ERR_PARTIAL => 'The upload was interrupted. Please try again.',
            default => 'The upload failed. Please try again.',
        };
    }

    if (!$errors) {
        // Read straight from the temporary upload. The file is
        // never saved into the website folder, so there is nothing
        // for anyone to request later.
        $fh = fopen($_FILES['csv']['tmp_name'], 'r');

        if (!$fh) {
            $errors[] = 'The file could not be read.';
        } else {
            // Excel in some regions writes semicolon-separated
            // files and still calls them CSV. Sniff the header.
            $firstLine = fgets($fh);
            $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
            rewind($fh);

            $header = fgetcsv($fh, 0, $delimiter);

            // Excel writes a byte order mark; it would otherwise
            // become part of the first column name.
            if ($header) {
                $header[0] = preg_replace('/^\x{FEFF}/u', '', $header[0]);
                $header = array_map(fn($h) => strtolower(trim($h)), $header);
            }

            if (!$header || !in_array('title', $header, true) || !in_array('body', $header, true)) {
                $errors[] = 'This does not look like a DailyPost spreadsheet — it needs at least a "title" and a "body" column. Download the template and start from that.';
            }

            // --- Pass one: read and check everything ------------
            $rows = [];
            $line = 1;

            if (!$errors) {
                while (($data = fgetcsv($fh, 0, $delimiter)) !== false) {
                    $line++;

                    // Skip blank lines Excel leaves at the end.
                    if (count($data) === 1 && trim((string) $data[0]) === '') {
                        continue;
                    }

                    $r = [];
                    foreach ($header as $i => $col) {
                        $r[$col] = isset($data[$i]) ? trim((string) $data[$i]) : '';
                    }

                    $title  = $r['title']  ?? '';
                    $author = $r['author'] ?? '';
                    $body   = $r['body']   ?? '';

                    if ($title === '')                 { $errors[] = "Row $line: the title is empty."; }
                    elseif (mb_strlen($title) > 180)   { $errors[] = "Row $line: the title is longer than 180 characters."; }

                    if ($author === '')                { $errors[] = "Row $line: the author is empty."; }
                    elseif (mb_strlen($author) > 80)   { $errors[] = "Row $line: the author name is longer than 80 characters."; }

                    if ($body === '')                  { $errors[] = "Row $line: the story text is empty."; }
                    elseif (mb_strlen($body) > 30000)  { $errors[] = "Row $line: the story is longer than 30,000 characters."; }

                    if (mb_strlen($r['excerpt'] ?? '') > 300) {
                        $errors[] = "Row $line: the summary is longer than 300 characters.";
                    }

                    // Anything unrecognised is corrected quietly
                    // rather than failing the whole import.
                    $cat = strtolower($r['category'] ?? '');
                    if (!in_array($cat, $validCategories, true)) { $cat = 'general'; }

                    $st = strtolower($r['status'] ?? '');
                    if (!in_array($st, $validStatuses, true)) { $st = 'published'; }

                    $img = $r['image_url'] ?? '';
                    if ($img !== '' && !is_safe_image_url($img)) { $img = ''; }

                    $pub = $r['published_at'] ?? '';
                    if ($pub !== '' && strtotime($pub) === false) { $pub = ''; }

                    $rows[] = [
                        'id'           => ctype_digit($r['id'] ?? '') ? (int) $r['id'] : null,
                        'title'        => $title,
                        'author'       => $author,
                        'category'     => $cat,
                        'excerpt'      => ($r['excerpt'] ?? '') !== '' ? $r['excerpt'] : null,
                        'body'         => $body,
                        'image_url'    => $img !== '' ? $img : null,
                        'status'       => $st,
                        'published_at' => $pub !== '' ? date('Y-m-d H:i:s', strtotime($pub)) : null,
                    ];

                    // Stop runaway files rather than time out
                    // halfway through on shared hosting.
                    if (count($rows) > 5000) {
                        $errors[] = 'That file has more than 5,000 rows. Please split it into smaller files.';
                        break;
                    }
                }
            }

            fclose($fh);

            if (!$rows && !$errors) {
                $errors[] = 'The file has a header row but no stories in it.';
            }

            // --- Pass two: write, all or nothing ----------------
            if (!$errors) {
                $added = $updated = 0;

                try {
                    $pdo->beginTransaction();

                    if ($mode === 'replace') {
                        $pdo->exec("DELETE FROM stories");
                    }

                    // Which of the ids in the file actually exist?
                    //
                    // Do NOT use rowCount() on the UPDATE to answer
                    // this. MySQL reports rows *changed*, not rows
                    // *matched*, so re-importing an unedited export
                    // returns 0 for every row and every story gets
                    // inserted a second time. One lookup up front
                    // instead, rather than a query per row.
                    $existing = [];
                    if ($mode !== 'replace') {
                        $ids = array_filter(array_column($rows, 'id'));
                        if ($ids) {
                            $ph = implode(',', array_fill(0, count($ids), '?'));
                            $q2 = $pdo->prepare("SELECT id FROM stories WHERE id IN ($ph)");
                            $q2->execute(array_values($ids));
                            $existing = array_flip($q2->fetchAll(PDO::FETCH_COLUMN));
                        }
                    }

                    $ins = $pdo->prepare(
                        "INSERT INTO stories (title, slug, author, category, excerpt, body, image_url, status, created_at, published_at)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)"
                    );
                    $upd = $pdo->prepare(
                        "UPDATE stories SET title=?, author=?, category=?, excerpt=?, body=?, image_url=?, status=?, published_at=?
                         WHERE id=?"
                    );

                    // Slugs are generated here rather than by the
                    // database, and tracked in memory as we go so
                    // that two rows with the same title inside one
                    // file do not collide with each other.
                    $usedSlugs = [];
                    foreach ($pdo->query("SELECT slug FROM stories WHERE slug IS NOT NULL") as $row) {
                        $usedSlugs[$row['slug']] = true;
                    }

                    foreach ($rows as $r) {
                        // A published story with no date given gets
                        // today's, otherwise it sorts to the bottom
                        // forever.
                        $pub = $r['published_at'];
                        if ($pub === null && $r['status'] === 'published') {
                            $pub = date('Y-m-d H:i:s');
                        }

                        // In replace mode every id was just deleted,
                        // so everything is an insert.
                        if ($r['id'] && isset($existing[$r['id']])) {
                            $upd->execute([$r['title'], $r['author'], $r['category'], $r['excerpt'],
                                           $r['body'], $r['image_url'], $r['status'], $pub, $r['id']]);
                            $updated++;
                        } else {
                            $base = make_slug($r['title']);
                            $slug = $base;
                            $n    = 1;
                            while (isset($usedSlugs[$slug])) {
                                $slug = $base . '-' . (++$n);
                            }
                            $usedSlugs[$slug] = true;

                            $ins->execute([$r['title'], $slug, $r['author'], $r['category'], $r['excerpt'],
                                           $r['body'], $r['image_url'], $r['status'], $pub]);
                            $added++;
                        }
                    }

                    $pdo->commit();

                    $report = ['added' => $added, 'updated' => $updated, 'mode' => $mode];

                } catch (Throwable $e) {
                    $pdo->rollBack();
                    error_log('CSV import failed: ' . $e->getMessage());
                    $errors[] = 'Something went wrong while saving, so nothing was changed. Your stories are exactly as they were.';
                }
            }
        }
    }
}

$total = (int) $pdo->query("SELECT COUNT(*) FROM stories")->fetchColumn();
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Import stories — DailyPost</title>
<link rel="stylesheet" href="../assets/css/dailypost.css">
<style>
body{background:var(--bg)}
.adminbar{background:var(--surface);border-bottom:1px solid var(--border);padding:0 24px;height:64px;display:flex;align-items:center;gap:16px}
.adminbar .name{font-size:24px;font-weight:800;letter-spacing:-.03em}
.adminbar .name span{color:var(--accent)}
.adminbar .sp{margin-left:auto}
.panel-box{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:22px;margin-bottom:18px}
.steps{counter-reset:s;list-style:none;padding:0;margin:0}
.steps li{counter-increment:s;position:relative;padding:0 0 16px 40px}
.steps li::before{content:counter(s);position:absolute;left:0;top:0;width:26px;height:26px;border-radius:50%;background:var(--accent);color:#fff;display:grid;place-items:center;font-size:13px;font-weight:700}
.steps li b{display:block;margin-bottom:3px}
.steps li span{color:var(--text-muted);font-size:14px}
.errlist{max-height:280px;overflow:auto;font-size:14px}
.errlist div{padding:3px 0}
</style>
</head>
<body>

<header class="adminbar">
  <a class="name" href="../index.php">Daily<span>Post</span></a>
  <span style="color:var(--text-muted);font-size:14px">Import &amp; export</span>
  <span class="sp"></span>
  <a class="btn ghost" href="dashboard.php">Back to submissions</a>
</header>

<main class="wrap" style="max-width:900px;padding-top:24px">

  <h1 style="font-size:28px;font-weight:800;letter-spacing:-.025em;margin:0 0 6px">Import stories from a spreadsheet</h1>
  <p style="color:var(--text-muted);margin:0 0 22px">There are currently <b><?= number_format($total) ?></b> stories on the site.</p>

  <?php if ($report): ?>
    <div class="notice">
      <b>Done.</b>
      <?= $report['added'] ?> <?= $report['added'] === 1 ? 'story' : 'stories' ?> added<?php
        if ($report['updated']) echo ', ' . $report['updated'] . ' updated';
        if ($report['mode'] === 'replace') echo '. Everything that was there before was removed.';
        else echo '.';
      ?>
      <a href="../index.php" style="color:inherit;text-decoration:underline">View the site</a>
    </div>
  <?php endif; ?>

  <?php if ($errors): ?>
    <div class="notice error">
      <b>Nothing was changed.</b> Please fix these in the spreadsheet and upload it again:
      <div class="errlist" style="margin-top:8px">
        <?php foreach (array_slice($errors, 0, 60) as $err): ?>
          <div><?= e($err) ?></div>
        <?php endforeach; ?>
        <?php if (count($errors) > 60): ?>
          <div><i>…and <?= count($errors) - 60 ?> more.</i></div>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

  <div class="panel-box">
    <h2 style="font-size:18px;font-weight:700;margin:0 0 16px">How this works</h2>
    <ol class="steps">
      <li>
        <b>Download a spreadsheet</b>
        <span>Start from the template if you are writing new stories, or download the current ones if you want to edit what is already there.</span>
        <div style="margin-top:9px;display:flex;gap:8px;flex-wrap:wrap">
          <a class="btn ghost" href="export_csv.php?template=1">Blank template</a>
          <a class="btn ghost" href="export_csv.php?status=all">Current stories</a>
        </div>
      </li>
      <li>
        <b>Edit it in Excel or Google Sheets</b>
        <span>One story per row. Fill in the title, author and body at minimum. Leave the <b>id</b> column blank for a new story — do not invent numbers in it.</span>
      </li>
      <li>
        <b>Upload it below</b>
        <span>Save it as CSV, then choose the file here.</span>
      </li>
    </ol>
  </div>

  <div class="panel-box">
    <h2 style="font-size:18px;font-weight:700;margin:0 0 14px">Upload</h2>

    <form method="post" enctype="multipart/form-data"
          onsubmit="return !document.getElementById('rep').checked || confirm('This will permanently delete all <?= $total ?> stories currently on the site and replace them with the file. Continue?')">
      <?= csrf_field() ?>

      <div class="field">
        <label for="csv">Spreadsheet file (.csv)</label>
        <input id="csv" type="file" name="csv" accept=".csv,text/csv" required>
      </div>

      <div class="field">
        <label style="font-weight:400;display:flex;gap:9px;align-items:flex-start">
          <input type="radio" name="mode" value="update" checked style="width:auto;margin-top:3px">
          <span><b>Add and update</b><br>
          <span style="color:var(--text-muted);font-size:13.5px">New rows are added. Rows with an id update that story. Nothing is deleted.</span></span>
        </label>
      </div>

      <div class="field">
        <label style="font-weight:400;display:flex;gap:9px;align-items:flex-start">
          <input id="rep" type="radio" name="mode" value="replace" style="width:auto;margin-top:3px">
          <span><b>Replace everything</b><br>
          <span style="color:var(--text-muted);font-size:13.5px">Deletes all <?= number_format($total) ?> current stories first, then imports the file. Use this to clear out the demo content.</span></span>
        </label>
      </div>

      <button class="btn" type="submit">Import stories</button>
    </form>
  </div>

  <div class="panel-box">
    <h2 style="font-size:18px;font-weight:700;margin:0 0 10px">Good to know</h2>
    <ul style="margin:0;padding-left:18px;color:var(--text-muted);font-size:14.5px;line-height:1.7">
      <li><b>Nothing is saved unless every row is valid.</b> If anything is wrong you get a list of which rows, and the site is left untouched.</li>
      <li>Categories that are not recognised become <b>general</b>. Bad image links are ignored and the category photo is used instead.</li>
      <li>Up to <b>5,000 rows</b> per file. For more than that, split it and import twice.</li>
      <li>Export first before a big change — that file is your undo.</li>
    </ul>
  </div>

</main>
</body>
</html>
