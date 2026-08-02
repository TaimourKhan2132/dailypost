<?php
// ---------------------------------------------------------------
// Template. Copy this to credentials.php and fill in real values.
// credentials.php is gitignored; this example file is not.
// ---------------------------------------------------------------

return [
    'db_host' => 'localhost',
    'db_name' => 'YOUR_DATABASE_NAME',
    'db_user' => 'YOUR_DATABASE_USER',
    'db_pass' => 'YOUR_DATABASE_PASSWORD',

    // Generate a fresh one with:
    //   php -r "echo bin2hex(random_bytes(32));"
    'app_secret' => 'PASTE_A_RANDOM_64_CHARACTER_STRING_HERE',

    // Must be false on any public server.
    'debug' => false,
];
