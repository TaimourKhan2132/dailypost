<?php
require 'config/db.php';

dp_session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

csrf_verify();

// Same honeypot trick as the story form.
if (trim($_POST['website'] ?? '') !== '') {
    header('Location: index.php?subscribed=1#newsletter');
    exit;
}

$email = trim($_POST['email'] ?? '');

if ($email !== '' && mb_strlen($email) <= 180 && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // INSERT IGNORE means a repeat signup is quietly accepted
    // rather than throwing a duplicate-key error at the visitor.
    $pdo->prepare("INSERT IGNORE INTO subscribers (email) VALUES (?)")->execute([$email]);
    header('Location: index.php?subscribed=1#newsletter');
    exit;
}

header('Location: index.php?subscribed=0#newsletter');
exit;
