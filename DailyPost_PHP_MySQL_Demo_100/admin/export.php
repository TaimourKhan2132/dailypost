<?php
// ---------------------------------------------------------------
// Downloads every story as one plain text file.
//
// Stories live as rows in MySQL, not as files on the server, so
// there is nothing to open in the hosting file manager. This is the
// nearest thing: a readable snapshot you can keep, print, or email.
//
// Admin only - the export includes pending and rejected
// submissions, and the email addresses writers gave us.
// ---------------------------------------------------------------

require '../config/db.php';

require_admin();

$status = $_GET['status'] ?? 'published';
if (!in_array($status, ['published', 'pending', 'rejected', 'all'], true)) {
    $status = 'published';
}

$where  = $status === 'all' ? '' : 'WHERE status = :s';
$params = $status === 'all' ? [] : ['s' => $status];

$q = $pdo->prepare("SELECT * FROM stories $where ORDER BY published_at DESC, id DESC");
$q->execute($params);
$stories = $q->fetchAll();

$filename = 'dailypost-' . $status . '-' . date('Y-m-d') . '.txt';

// Tell the browser to save this rather than display it.
header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$line = str_repeat('=', 70);
$thin = str_repeat('-', 70);

echo "$line\n";
echo "DAILYPOST - STORY EXPORT\n";
echo "Exported " . date('j F Y, g:ia') . "\n";
echo ucfirst($status) . " stories: " . count($stories) . "\n";
echo "$line\n\n";

if (!$stories) {
    echo "No stories found.\n";
    exit;
}

foreach ($stories as $i => $s) {
    echo "$thin\n";
    echo "#" . ($i + 1) . "  " . $s['title'] . "\n";
    echo "$thin\n";
    echo "Author    : " . $s['author'] . "\n";
    echo "Category  : " . $s['category'] . "\n";
    echo "Status    : " . $s['status'] . "\n";
    echo "Submitted : " . $s['created_at'] . "\n";

    if ($s['published_at']) {
        echo "Published : " . $s['published_at'] . "\n";
    }

    echo "Views     : " . (int) $s['views'] . "\n";

    if ($s['email']) {
        echo "Contact   : " . $s['email'] . "\n";
    }

    if ($s['excerpt']) {
        echo "\nSummary:\n" . wordwrap($s['excerpt'], 70) . "\n";
    }

    echo "\n" . wordwrap($s['body'], 70) . "\n\n";
}

echo "$line\n";
echo "End of export. " . count($stories) . " stories.\n";
echo "$line\n";
