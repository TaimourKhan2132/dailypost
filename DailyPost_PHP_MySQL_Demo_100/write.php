<?php
require 'config/db.php';

dp_session_start();

$errors = $_SESSION['form_errors'] ?? [];
$old    = $_SESSION['form_old']    ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_old']);

$categories = load_categories($pdo);

$v = fn(string $k): string => e($old[$k] ?? '');

$page_title = 'Write a Story — DailyPost';
$active_nav = 'write';

require 'includes/header.php';
?>

<div class="wrap">
<div class="formwrap">
  <h1>Write something worth sharing.</h1>
  <p class="sub">
    Submit your story for review. Once an editor approves it, it appears on
    DailyPost for everyone — permanently.
  </p>

  <?php if (isset($_GET['sent'])): ?>
    <div class="notice">Thank you. Your story was submitted and is waiting for review.</div>
  <?php endif; ?>

  <?php if ($errors): ?>
    <div class="notice error">
      <?php foreach ($errors as $err): ?>
        <div><?= e($err) ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="post" action="submit.php">
    <?= csrf_field() ?>

    <div class="hp-field" aria-hidden="true">
      <label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
    </div>

    <div class="field">
      <label for="title">Title</label>
      <input id="title" name="title" maxlength="180" required
             placeholder="Give your story a title" value="<?= $v('title') ?>">
    </div>

    <div class="two-up">
      <div class="field">
        <label for="author">Your name</label>
        <input id="author" name="author" maxlength="80" required
               placeholder="Your name" value="<?= $v('author') ?>">
      </div>
      <div class="field">
        <label for="email">Email <span style="font-weight:400;color:var(--text-muted)">(optional)</span></label>
        <input id="email" type="email" name="email" maxlength="180"
               placeholder="you@example.com" value="<?= $v('email') ?>">
        <span class="hint">Never published. Only so an editor can reach you.</span>
      </div>
    </div>

    <div class="two-up">
      <div class="field">
        <label for="category">Category</label>
        <select id="category" name="category">
          <?php foreach ($categories as $c): ?>
            <option value="<?= e($c['slug']) ?>" <?= ($old['category'] ?? '') === $c['slug'] ? 'selected' : '' ?>>
              <?= e($c['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label for="color">Cover colour</label>
        <select id="color" name="color">
          <?php foreach (['coral','blue','green','purple','yellow'] as $c): ?>
            <option value="<?= $c ?>" <?= ($old['color'] ?? '') === $c ? 'selected' : '' ?>><?= ucfirst($c) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="field">
      <label for="image_url">Cover image link <span style="font-weight:400;color:var(--text-muted)">(optional)</span></label>
      <input id="image_url" type="url" name="image_url" maxlength="500"
             placeholder="https://example.com/photo.jpg" value="<?= $v('image_url') ?>">
      <span class="hint">Paste a link to a photo. Leave it blank and we'll use a picture for your category.</span>
    </div>

    <div class="field">
      <label for="excerpt">Short summary</label>
      <textarea id="excerpt" name="excerpt" maxlength="300" rows="3" style="min-height:auto"
                placeholder="One or two sentences describing your story"><?= $v('excerpt') ?></textarea>
      <span class="hint">Shown on story cards and when the link is shared.</span>
    </div>

    <div class="field">
      <label for="body">Your story</label>
      <textarea id="body" name="body" maxlength="30000" required
                placeholder="Start writing…"><?= $v('body') ?></textarea>
      <span class="hint">Leave a blank line between paragraphs.</span>
    </div>

    <button class="btn" type="submit">Submit story →</button>
  </form>
</div>
</div>

<?php require 'includes/footer.php'; ?>
