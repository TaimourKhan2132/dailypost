<?php
// ---------------------------------------------------------------
// Downloads stories as a spreadsheet (CSV), which Excel and Google
// Sheets both open directly.
//
// The id column is what makes round-trip editing work: export,
// edit in Excel, upload again, and import.php updates the matching
// rows instead of creating duplicates. Leave id blank on a new row
// and it becomes a new story.
//
//   export_csv.php              published stories
//   export_csv.php?status=all   everything
//   export_csv.php?template=1   headers plus one example row
// ---------------------------------------------------------------

require '../config/db.php';

require_admin();

$template = isset($_GET['template']);

$status = $_GET['status'] ?? 'published';
if (!in_array($status, ['published', 'pending', 'rejected', 'all'], true)) {
    $status = 'published';
}

$columns = ['id', 'title', 'author', 'category', 'excerpt', 'body',
            'image_url', 'status', 'published_at'];

$filename = $template
    ? 'dailypost-template.csv'
    : 'dailypost-' . $status . '-' . date('Y-m-d') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');

// Excel on Windows assumes the old regional encoding unless a
// byte order mark tells it otherwise. Without these three bytes,
// any Urdu text or curly quote arrives as mojibake. This is the
// one place where a BOM is wanted rather than a bug.
fwrite($out, "\xEF\xBB\xBF");

fputcsv($out, $columns);

if ($template) {
    fputcsv($out, [
        '',
        'Leave the id blank to create a new story',
        'Author name',
        'general',
        'One or two sentences shown on the story card',
        "The full story goes here.\n\nLeave a blank line between paragraphs.",
        '',
        'published',
        '',
    ]);
    exit;
}

// Streamed one row at a time so a thousand stories never sit in
// memory all at once.
$where  = $status === 'all' ? '' : 'WHERE status = :s';
$params = $status === 'all' ? [] : ['s' => $status];

$q = $pdo->prepare("SELECT " . implode(',', $columns) . " FROM stories $where ORDER BY id");
$q->execute($params);

while ($row = $q->fetch()) {
    fputcsv($out, $row);
}

fclose($out);
