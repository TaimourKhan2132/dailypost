<?php
// ---------------------------------------------------------------
// Handles a story submission from write.php.
//
// This is the only page a stranger can send data to, so everything
// arriving here is treated as hostile until proven otherwise.
// ---------------------------------------------------------------

require 'config/db.php';

dp_session_start();

// Only POST. Someone typing the URL in gets sent to the form.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: write.php');
    exit;
}

csrf_verify();

$errors = [];

// --- HONEYPOT ---------------------------------------------------
// write.php contains a "website" field hidden with CSS. A human
// never sees it, so never fills it. Bots fill in every field they
// find. If it has anything in it, we silently pretend success -
// telling a bot why it failed just helps it adapt.
if (trim($_POST['website'] ?? '') !== '') {
    header('Location: write.php?sent=1');
    exit;
}

// --- RATE LIMIT -------------------------------------------------
// Three stories per hour from one address is generous for a person
// and useless for a spam script.
if (rate_limit_exceeded($pdo, 3, 60)) {
    $_SESSION['form_errors'] = ['You have submitted several stories recently. Please try again in an hour.'];
    header('Location: write.php');
    exit;
}

// --- COLLECT ----------------------------------------------------
$title    = trim($_POST['title'] ?? '');
$body     = trim($_POST['body']  ?? '');
$category = $_POST['category']   ?? '';

// The form now asks for one thing: a name or an email address.
//
// If it is an email we keep it in the private email column and
// publish only the part before the @, tidied up. Printing someone's
// address as their byline would put it in front of every reader and
// every spam crawler, which is not what they meant by typing it.
$identity = trim($_POST['author'] ?? '');
$email    = null;
$author   = $identity;

if ($identity !== '' && filter_var($identity, FILTER_VALIDATE_EMAIL)) {
    $email = $identity;

    $name = substr($identity, 0, strpos($identity, '@'));
    $name = trim(preg_replace('/[._\-]+/', ' ', $name));
    $name = preg_replace('/\d+$/', '', $name);          // ali.raza92 -> ali raza
    $name = trim($name);

    $author = $name !== '' ? mb_convert_case($name, MB_CASE_TITLE, 'UTF-8') : 'Anonymous';
}

// These are no longer asked for on the form. The columns stay, so an
// editor can still set a cover image when approving a story and an
// imported spreadsheet can still carry a summary.
$excerpt   = '';
$color     = 'coral';
$image_url = '';

// --- VALIDATE ---------------------------------------------------
// The maxlength attributes in write.php are a convenience for
// people using the form normally. They are trivially removed, so
// every limit is enforced again here. mb_strlen counts characters
// rather than bytes, so Urdu and emoji are measured correctly.

if ($title === '') {
    $errors[] = 'Please give your story a title.';
} elseif (mb_strlen($title) > 180) {
    $errors[] = 'Title must be 180 characters or fewer.';
}

if ($identity === '') {
    $errors[] = 'Please tell us your name or email.';
} elseif ($email !== null) {
    // It parsed as an email, so only the address length matters -
    // the published name was derived from it and is always short.
    if (mb_strlen($email) > 180) {
        $errors[] = 'That email address is too long.';
    }
} elseif (mb_strlen($author) > 80) {
    $errors[] = 'Name must be 80 characters or fewer.';
}

if (mb_strlen($body) < 50) {
    $errors[] = 'Your story is very short - please write at least 50 characters.';
} elseif (mb_strlen($body) > 30000) {
    $errors[] = 'Your story is too long. The limit is 30,000 characters.';
}

// Whitelists, not blacklists: anything not on the list is rejected
// rather than cleaned up and hoped for.
if (!in_array($color, ['coral', 'blue', 'green', 'purple', 'yellow'], true)) {
    $color = 'coral';
}

$valid_categories = $pdo->query("SELECT slug FROM categories")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array($category, $valid_categories, true)) {
    $category = 'general';
}

// An empty image box is fine - the story falls back to its
// category photo. A link that is present but malformed is not.
if ($image_url !== '' && !is_safe_image_url($image_url)) {
    $errors[] = 'The image link must be a full web address starting with http:// or https://';
}

// --- BAIL OUT ---------------------------------------------------
// Hand the errors and what they typed back to the form so nobody
// loses a story they just spent twenty minutes writing.
if ($errors) {
    $_SESSION['form_errors'] = $errors;
    // Give back exactly what was typed, not the name we derived from
    // it - otherwise someone who entered an email sees it silently
    // replaced by half of itself.
    $_SESSION['form_old'] = [
        'title'    => $title,
        'author'   => $identity,
        'body'     => $body,
        'category' => $category,
    ];
    header('Location: write.php');
    exit;
}

// --- SAVE -------------------------------------------------------
// status defaults to 'pending'. Nothing reaches the public site
// until an admin approves it.
$q = $pdo->prepare(
    "INSERT INTO stories (title, slug, author, email, excerpt, body, color, category, image_url, status, created_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())"
);
$q->execute([
    $title,
    unique_slug($pdo, $title),
    $author,
    $email,
    $excerpt !== '' ? $excerpt : null,
    $body,
    $color,
    $category,
    $image_url !== '' ? $image_url : null,
]);

rate_limit_record($pdo);

// Rotate the CSRF token so the same submission cannot be replayed.
unset($_SESSION['csrf']);

header('Location: write.php?sent=1');
exit;
