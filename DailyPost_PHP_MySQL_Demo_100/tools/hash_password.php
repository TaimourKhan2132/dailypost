<?php
// ---------------------------------------------------------------
// Prints a ready-to-paste SQL statement for creating an admin on a
// server you cannot reach from a command line - which is most free
// hosting, including InfinityFree.
//
//   php tools/hash_password.php
//
// Run it here, copy the statement it prints, then paste it into
// phpMyAdmin on the live host. Touches no database of its own.
// ---------------------------------------------------------------

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script can only be run from the command line.');
}

echo "DailyPost — generate an admin login for the live site\n";
echo "-----------------------------------------------------\n";

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

$hash = password_hash($password, PASSWORD_DEFAULT);

// Sanity check: never hand over a hash that does not verify. This
// is exactly the failure that shipped in the original project.
if (!password_verify($password, $hash)) {
    exit("Something went wrong - the generated hash does not verify. Do not use it.\n");
}

echo "\nVerified working. Paste this into phpMyAdmin on the live site:\n\n";
echo "INSERT INTO admins (username, password_hash) VALUES ('"
     . str_replace("'", "''", $username) . "', '" . $hash . "');\n\n";
echo "Then delete the tools/ folder from the server.\n";
