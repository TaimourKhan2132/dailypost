<?php
// ---------------------------------------------------------------
// Web app manifest. Served at /manifest.json through the rewrite in
// .htaccess.
//
// Generated rather than static so every path is absolute and
// correct - including after the site moves to dailypost.com.pk.
// A manifest with the wrong start_url is the usual reason a site
// installs but then opens a blank page.
// ---------------------------------------------------------------

require __DIR__ . '/config/db.php';

header('Content-Type: application/manifest+json; charset=utf-8');

$base = base_path();

$manifest = [
    'name'             => 'DailyPost — Stories Worth Sharing',
    'short_name'       => 'DailyPost',
    'description'      => 'A simple place for good stories. Read what people are sharing, or write something worth remembering.',

    // Opening the installed app lands on the homepage.
    'start_url'        => $base,

    // Everything under the site root belongs to the app. Links
    // outside it (WhatsApp, Facebook) open in the real browser.
    'scope'            => $base,

    // 'standalone' removes the browser address bar, which is what
    // makes it feel like an app rather than a bookmark.
    'display'          => 'standalone',
    'orientation'      => 'any',

    // background_color is what shows during the launch splash, so
    // it should match the site, not be left white.
    'background_color' => '#0f1115',
    'theme_color'      => '#e11d48',
    'lang'             => 'en',
    'dir'              => 'ltr',

    'icons' => [
        [
            'src'     => $base . 'assets/icon-192.png',
            'sizes'   => '192x192',
            'type'    => 'image/png',
            'purpose' => 'any',
        ],
        [
            'src'     => $base . 'assets/icon-512.png',
            'sizes'   => '512x512',
            'type'    => 'image/png',
            'purpose' => 'any',
        ],
        [
            // Android crops icons to whatever shape the launcher
            // uses. A maskable icon has padding so the DP mark
            // survives a circular crop.
            'src'     => $base . 'assets/icon-maskable-512.png',
            'sizes'   => '512x512',
            'type'    => 'image/png',
            'purpose' => 'maskable',
        ],
    ],

    // Long-pressing the installed icon offers these.
    'shortcuts' => [
        [
            'name'  => 'Write a story',
            'url'   => $base . 'write.php',
            'icons' => [['src' => $base . 'assets/icon-192.png', 'sizes' => '192x192']],
        ],
        [
            'name'  => 'Search',
            'url'   => $base . 'search.php',
            'icons' => [['src' => $base . 'assets/icon-192.png', 'sizes' => '192x192']],
        ],
    ],
];

echo json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
