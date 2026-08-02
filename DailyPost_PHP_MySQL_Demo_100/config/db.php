<?php
// ---------------------------------------------------------------
// Database connection. Every page starts by loading this file.
// The actual username and password live in credentials.php, which
// git never sees.
// ---------------------------------------------------------------

$dp_config = require __DIR__ . '/credentials.php';

require_once __DIR__ . '/../includes/helpers.php';

// On a public server, a raw PHP error can reveal file paths and
// query fragments. Show them locally, log them everywhere else.
if ($dp_config['debug']) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    error_reporting(E_ALL);
}

try {
    $pdo = new PDO(
        "mysql:host={$dp_config['db_host']};dbname={$dp_config['db_name']};charset=utf8mb4",
        $dp_config['db_user'],
        $dp_config['db_pass'],
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

            // Use MySQL's own prepared statements rather than PHP
            // faking them. Values are then sent to the database
            // separately from the SQL text, so a value can never be
            // read as a command. This is what makes SQL injection
            // structurally impossible, not just unlikely.
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);

    if ($dp_config['debug']) {
        exit('Database connection failed: ' . e($e->getMessage()));
    }

    error_log('DB connection failed: ' . $e->getMessage());
    exit('The site is temporarily unavailable. Please try again shortly.');
}
