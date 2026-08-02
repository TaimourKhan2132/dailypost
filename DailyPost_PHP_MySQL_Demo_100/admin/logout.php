<?php
require '../config/db.php';

dp_session_start();

// Clear the data, then the cookie, then the session itself.
// The original only called session_destroy(), which left the
// session cookie sitting in the browser.
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

session_destroy();

header('Location: login.php');
exit;
