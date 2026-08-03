<?php
// ---------------------------------------------------------------
// Shared helpers, loaded automatically by config/db.php so every
// page has them available.
// ---------------------------------------------------------------


// --- SESSIONS ---------------------------------------------------
// A session is how the server remembers "this visitor is logged in"
// between page loads. The browser holds a cookie with a random id;
// the real data stays on the server.
//
// The three settings below matter:
//   httponly - JavaScript cannot read the cookie, so a scripting
//              bug can't be used to steal a logged-in session
//   samesite - the cookie is not sent when another website makes a
//              request to us, which blocks most CSRF outright
//   secure   - only send the cookie over HTTPS (on automatically
//              once the live site has SSL)

function dp_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);

    session_start();
}


// --- CSRF -------------------------------------------------------
// Cross-Site Request Forgery: another website quietly submits a
// form to ours using your logged-in browser. The defence is a
// random token that we put in our own forms and then check on the
// way back in. An attacker's page cannot read our token, so their
// forged request fails.

function csrf_token(): string
{
    dp_session_start();

    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

// Drop this straight into any <form>.
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

// Call at the top of anything that changes data. Dies on failure.
function csrf_verify(): void
{
    dp_session_start();

    $sent   = $_POST['csrf'] ?? '';
    $stored = $_SESSION['csrf'] ?? '';

    // hash_equals compares in constant time so an attacker cannot
    // guess the token one character at a time by measuring timing.
    if ($stored === '' || !hash_equals($stored, $sent)) {
        http_response_code(400);
        exit('Security check failed. Please go back, reload the page and try again.');
    }
}


// --- OUTPUT ESCAPING --------------------------------------------
// Short name because it gets used on every single piece of text
// that reaches a page. Turns <script> into harmless characters.

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}


// --- VISITOR IDENTITY -------------------------------------------

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

// We store a hash, never the address itself. Enough to recognise a
// repeat submitter, but the database holds no personal data.
function ip_hash(string $ip): string
{
    global $dp_config;
    return hash_hmac('sha256', $ip, $dp_config['app_secret']);
}


// --- RATE LIMITING ----------------------------------------------
// Stops one person (or one bot) flooding the submission form.

function rate_limit_exceeded(PDO $pdo, int $max, int $minutes): bool
{
    // $minutes is cast to int and inlined because MySQL will not
    // accept a bound parameter inside INTERVAL. It never comes from
    // user input, and the cast guarantees it is a plain number.
    $minutes = (int) $minutes;

    $q = $pdo->prepare(
        "SELECT COUNT(*) FROM submission_log
         WHERE ip_hash = ? AND submitted_at > (NOW() - INTERVAL $minutes MINUTE)"
    );
    $q->execute([ip_hash(client_ip())]);

    return (int) $q->fetchColumn() >= $max;
}

function rate_limit_record(PDO $pdo): void
{
    $pdo->prepare("INSERT INTO submission_log (ip_hash, submitted_at) VALUES (?, NOW())")
        ->execute([ip_hash(client_ip())]);
}


// --- LOGIN THROTTLING -------------------------------------------
// Same idea as the rate limiter, applied to password guessing.

function login_locked_out(PDO $pdo, int $max = 5, int $minutes = 15): bool
{
    $minutes = (int) $minutes;

    $q = $pdo->prepare(
        "SELECT COUNT(*) FROM login_attempts
         WHERE ip_hash = ? AND attempted_at > (NOW() - INTERVAL $minutes MINUTE)"
    );
    $q->execute([ip_hash(client_ip())]);

    return (int) $q->fetchColumn() >= $max;
}

function login_record_failure(PDO $pdo): void
{
    $pdo->prepare("INSERT INTO login_attempts (ip_hash, attempted_at) VALUES (?, NOW())")
        ->execute([ip_hash(client_ip())]);
}

function login_clear_failures(PDO $pdo): void
{
    $pdo->prepare("DELETE FROM login_attempts WHERE ip_hash = ?")
        ->execute([ip_hash(client_ip())]);
}

// Every admin page starts with this.
function require_admin(): void
{
    dp_session_start();

    if (empty($_SESSION['admin'])) {
        header('Location: login.php');
        exit;
    }
}


// --- READ TIME --------------------------------------------------
// The screenshot shows "5 min read" on every card. Average adult
// reading speed is roughly 200 words a minute.

function read_time(string $body): int
{
    return max(1, (int) ceil(str_word_count(strip_tags($body)) / 200));
}


// --- IMAGES -----------------------------------------------------
// Writers paste a link rather than uploading a file. A pasted link
// cannot run code, but we still only accept plain http/https URLs
// so nobody can slip in javascript: or data: tricks.

function is_safe_image_url(?string $url): bool
{
    if ($url === null || trim($url) === '') {
        return false;
    }

    if (strlen($url) > 500 || !filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }

    return in_array(strtolower(parse_url($url, PHP_URL_SCHEME) ?? ''), ['http', 'https'], true);
}

// Loads every category with its badge colour and its set of
// fallback photos. Every page needs this, so it lives here rather
// than being copy-pasted five times.
function load_categories(PDO $pdo): array
{
    $cats = [];

    foreach ($pdo->query("SELECT * FROM categories ORDER BY sort_order") as $c) {
        $c['images'] = [];
        $cats[$c['slug']] = $c;
    }

    // Wrapped because the code may reach a server where migration
    // 004 has not been imported yet. A missing table should mean
    // one photo per category, not a broken site.
    try {
        foreach ($pdo->query("SELECT category, url FROM category_images ORDER BY id") as $r) {
            if (isset($cats[$r['category']])) {
                $cats[$r['category']]['images'][] = $r['url'];
            }
        }
    } catch (PDOException $e) {
        // no category_images table - fall through to default_image
    }

    foreach ($cats as $slug => $c) {
        if (!$c['images'] && !empty($c['default_image'])) {
            $cats[$slug]['images'][] = $c['default_image'];
        }
    }

    return $cats;
}

// Falls back to a category photo when a story has no usable image.
// The browser-side onerror handler covers the other case - a link
// that looked fine but is dead by the time someone reads it.
function story_image(array $story, array $categories): string
{
    if (is_safe_image_url($story['image_url'] ?? null)) {
        return $story['image_url'];
    }

    $cat = $categories[$story['category']] ?? $categories['general'] ?? null;
    if (!$cat) {
        return '';
    }

    $images = $cat['images'] ?? [];
    if (!$images) {
        return $cat['default_image'] ?? '';
    }

    // Chosen from the story id rather than at random, so a story
    // keeps the same picture on every page load and between the
    // card, the article and Most Read. Random would make the site
    // flicker on every refresh.
    return $images[((int) ($story['id'] ?? 0)) % count($images)];
}

// The single photo used when a whole category needs one image
// (the onerror fallback on an <img>, for instance).
function category_image(array $categories, string $slug): string
{
    $cat = $categories[$slug] ?? $categories['general'] ?? null;
    if (!$cat) {
        return '';
    }

    return $cat['images'][0] ?? $cat['default_image'] ?? '';
}
