<?php
// ---------------------------------------------------------------
// Checks whether a password actually matches a stored hash.
//
//   php tools/verify_hash.php
//
// Use it when a login is being refused and you cannot tell whether
// the password is wrong or the hash in the database is damaged.
// Copy the password_hash value out of phpMyAdmin, paste it here,
// type the password, and this says which of the two is at fault.
//
// Touches no database. Sends nothing anywhere.
// ---------------------------------------------------------------

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script can only be run from the command line.');
}

echo "Paste the password_hash value from the admins table:\n> ";
$hash = trim(fgets(STDIN));

echo "\nType the password you are trying to log in with:\n> ";
$password = trim(fgets(STDIN));

echo "\n-----------------------------------------------\n";

// --- Is the hash even well formed? ---
$len = strlen($hash);
echo "Hash length: $len " . ($len === 60 ? "(correct)\n" : "(WRONG - bcrypt is always 60. It was truncated on the way in.)\n");

$prefix = substr($hash, 0, 4);
echo "Prefix: $prefix " . ($prefix === '$2y$' ? "(correct)\n" : "(WRONG - should be \$2y\$)\n");

$info = password_get_info($hash);
echo "Recognised as: " . ($info['algoName'] ?: 'unknown') . "\n";

// --- The actual question ---
echo "\nPassword matches this hash: ";
echo password_verify($password, $hash) ? "YES\n" : "NO\n";

echo "\nWhat that means:\n";
echo "  Length/prefix wrong -> the hash in the database is damaged.\n";
echo "                         Generate a new one and replace the row.\n";
echo "  Hash fine but NO    -> the hash is intact, the password differs.\n";
echo "                         Watch for a capital letter or a trailing space.\n";
echo "  YES                 -> the credentials are fine. The problem is\n";
echo "                         elsewhere: wrong username, a second admin row,\n";
echo "                         or a lockout. Check the admins table.\n";
