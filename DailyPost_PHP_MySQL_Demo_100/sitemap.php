<?php
// ---------------------------------------------------------------
// Sitemap for search engines. Served at /sitemap.xml through the
// rewrite rule in .htaccess.
//
// Generated from the database on request rather than written to a
// file, so it is never out of date: publish a story and it appears
// here immediately.
// ---------------------------------------------------------------

require __DIR__ . '/config/db.php';

header('Content-Type: application/xml; charset=utf-8');

$pages = [
    ['loc' => site_url('index.php'),  'priority' => '1.0', 'freq' => 'daily'],
    ['loc' => site_url('search.php'), 'priority' => '0.5', 'freq' => 'weekly'],
    ['loc' => site_url('about.php'),  'priority' => '0.5', 'freq' => 'monthly'],
    ['loc' => site_url('write.php'),  'priority' => '0.6', 'freq' => 'monthly'],
];

$stories = $pdo->query(
    "SELECT id, slug, published_at FROM stories
     WHERE status = 'published'
     ORDER BY published_at DESC
     LIMIT 5000"
)->fetchAll();

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

foreach ($pages as $p) {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($p['loc'], ENT_XML1) . "</loc>\n";
    echo "    <changefreq>{$p['freq']}</changefreq>\n";
    echo "    <priority>{$p['priority']}</priority>\n";
    echo "  </url>\n";
}

foreach ($stories as $s) {
    $loc = site_url(story_url($s));
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($loc, ENT_XML1) . "</loc>\n";
    if ($s['published_at']) {
        echo "    <lastmod>" . date('Y-m-d', strtotime($s['published_at'])) . "</lastmod>\n";
    }
    echo "    <changefreq>monthly</changefreq>\n";
    echo "    <priority>0.8</priority>\n";
    echo "  </url>\n";
}

echo '</urlset>' . "\n";
