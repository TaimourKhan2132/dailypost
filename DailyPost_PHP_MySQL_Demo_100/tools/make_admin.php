<?php
// ---------------------------------------------------------------
// Creates or updates an admin account.
//
//   php tools/make_admin.php
//
// Run from a terminal, never over the web. The check below refuses
// to run if it is ever reached through a browser - otherwise anyone
// who found the URL could hand themselves an admin account.
// ---------------------------------------------------------------

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script can only be run from the command line.');
}

require __DIR__ . '/../config/db.php';

echo "DailyPost — create admin account\n";
echo "--------------------------------\n";

echo "Username: ";
$username = trim(fgets(STDIN));

echo "Password: ";
$password = trim(fgets(STDIN));

if ($username === '' || $password === '') {
    exit("Both a username and a password are required.\n");
}

if (strlen($password) < 12) {
    exit("Password must be at least 12 characters. This is the only door into the site.\n");
}

// password_hash generates a random salt and stores it inside the
// resulting string, so two people with the same password still get
// completely different hashes.
$hash = password_hash($password, PASSWORD_DEFAULT);

$pdo->prepare(
    "INSERT INTO admins (username, password_hash) VALUES (?, ?)
     ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)"
)->execute([$username, $hash]);

echo "\nDone. Admin '$username' is ready.\n";
echo "\nIf you need to set this up on another server by hand, run:\n";
echo "INSERT INTO admins (username, password_hash) VALUES ('"
     . str_replace("'", "''", $username) . "', '" . $hash . "');\n";
