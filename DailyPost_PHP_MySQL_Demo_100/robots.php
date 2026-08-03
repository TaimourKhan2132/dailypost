<?php
// ---------------------------------------------------------------
// Served at /robots.txt through the rewrite in .htaccess.
//
// Generated rather than static because the Sitemap line has to be
// an absolute URL, and this site will change address when
// dailypost.com.pk is connected. Working this out at request time
// means nobody has to remember to edit it.
// ---------------------------------------------------------------

require __DIR__ . '/config/db.php';

header('Content-Type: text/plain; charset=utf-8');
?>
# DailyPost

User-agent: *

# The admin panel and the form handlers have nothing worth indexing.
Disallow: /admin/
Disallow: /submit.php
Disallow: /subscribe.php

# Search result pages generate endless near-duplicate addresses.
Disallow: /search.php?

Allow: /

Sitemap: <?= site_url('sitemap.xml') ?>
