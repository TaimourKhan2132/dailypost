<?php
// ---------------------------------------------------------------
// Fills in the slug for every story that has not got one.
//
//   php tools/backfill_slugs.php            fill in the database
//   php tools/backfill_slugs.php --sql      print SQL instead
//
// The --sql form exists because InfinityFree has no command line.
// Run it here, paste the output into phpMyAdmin on the live site.
// ---------------------------------------------------------------

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script can only be run from the command line.');
}

require __DIR__ . '/../config/db.php';

$sqlOnly = in_array('--sql', $argv, true);

$rows = $pdo->query("SELECT id, title, slug FROM stories ORDER BY id")->fetchAll();

if (!$rows) {
    exit("No stories found.\n");
}

// Track slugs as they are assigned so duplicates inside this run
// collide with each other too, not just with what is already saved.
$taken = [];
foreach ($rows as $r) {
    if ($r['slug'] !== null && $r['slug'] !== '') {
        $taken[$r['slug']] = true;
    }
}

$lines = [];
$done  = 0;

foreach ($rows as $r) {
    if ($r['slug'] !== null && $r['slug'] !== '') {
        continue;
    }

    $base = make_slug($r['title']);
    $slug = $base;
    $n    = 1;
    while (isset($taken[$slug])) {
        $slug = $base . '-' . (++$n);
    }
    $taken[$slug] = true;

    if ($sqlOnly) {
        $lines[] = "UPDATE stories SET slug = '" . str_replace("'", "''", $slug) . "' WHERE id = " . (int) $r['id'] . ";";
    } else {
        $pdo->prepare("UPDATE stories SET slug = ? WHERE id = ?")->execute([$slug, $r['id']]);
    }

    $done++;
}

if ($sqlOnly) {
    echo "-- DailyPost: backfill slugs for stories that have none.\n";
    echo "-- Safe to run once. Generated " . date('Y-m-d H:i') . ".\n\n";
    echo "SET NAMES utf8mb4;\n\n";
    echo implode("\n", $lines) . "\n";
} else {
    echo "Filled in $done slug" . ($done === 1 ? '' : 's') . ".\n";
}
