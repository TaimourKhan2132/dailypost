<?php
// ---------------------------------------------------------------
// Checks an admin login.
// ---------------------------------------------------------------

require '../config/db.php';

dp_session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

csrf_verify();

if (login_locked_out($pdo)) {
    header('Location: login.php?locked=1');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

$q = $pdo->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
$q->execute([$username]);
$admin = $q->fetch();

// password_verify re-hashes the supplied password with the salt
// stored inside the hash and compares the result. The real password
// is never stored anywhere, so even a stolen database does not hand
// anyone the login.
if ($admin && password_verify($password, $admin['password_hash'])) {

    login_clear_failures($pdo);

    // Session fixation defence: if an attacker managed to plant a
    // known session id in the browser before login, this throws it
    // away and issues a fresh one at the moment privilege is gained.
    session_regenerate_id(true);

    $_SESSION['admin']       = $admin['username'];
    $_SESSION['admin_since'] = time();

    unset($_SESSION['csrf']);

    header('Location: dashboard.php');
    exit;
}

login_record_failure($pdo);

// Deliberately vague: never reveal whether it was the username or
// the password that was wrong, or an attacker learns which accounts
// exist. The short pause slows down automated guessing further.
usleep(400000);

header('Location: login.php?error=1');
exit;
