<?php // Floating Write button. Phones only, and not on the write
      // page itself where it would just point at the current page. ?>
<?php if (($active_nav ?? '') !== 'write'): ?>
  <a class="fab" href="<?= $base ?? '' ?>write.php" aria-label="Write a story" title="Write a story">
    <svg viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
  </a>
<?php endif; ?>

<footer class="site-footer">
  <div class="wrap">
    <div class="cols">
      <div>
        <div class="brand">Daily<span>Post</span></div>
        <p style="margin:10px 0 0;max-width:320px">
          A simple place for good stories. Anyone can write, everyone can read.
        </p>
      </div>
      <div>
        <h4>Explore</h4>
        <a href="<?= $base ?? '' ?>index.php">Latest Stories</a>
        <a href="<?= $base ?? '' ?>search.php">Search</a>
        <a href="<?= $base ?? '' ?>index.php#picks">Editor's Picks</a>
      </div>
      <div>
        <h4>Take Part</h4>
        <a href="<?= $base ?? '' ?>write.php">Write a Story</a>
        <a href="<?= $base ?? '' ?>index.php#newsletter">Newsletter</a>
        <a href="<?= $base ?? '' ?>about.php">About</a>
      </div>
      <div>
        <h4>Categories</h4>
        <?php
        // Only shown if the page already loaded the category list.
        foreach (array_slice($categories ?? [], 0, 5) as $c):
        ?>
          <a href="<?= $base ?? '' ?>search.php?category=<?= e($c['slug']) ?>"><?= e($c['name']) ?></a>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="base">
      <span>© <?= date('Y') ?> DailyPost. All rights reserved.</span>
      <span>Stories Worth Sharing.</span>
      <?php // Editors only. Kept down here rather than in the header
            // so readers are not invited to create an account that
            // does not exist. ?>
      <a class="editor-link" href="<?= $base ?? '' ?>admin/login.php">Editor sign in</a>
    </div>
  </div>
</footer>

<script>
// Theme toggle. The initial value was already applied in <head>;
// this only handles the click and remembers the choice.
document.getElementById('themeToggle').addEventListener('click', function () {
  var root = document.documentElement;
  var dark = root.getAttribute('data-theme') === 'dark';
  if (dark) { root.removeAttribute('data-theme'); localStorage.setItem('dp-theme', 'light'); }
  else      { root.setAttribute('data-theme', 'dark'); localStorage.setItem('dp-theme', 'dark'); }
});
</script>
</body>
</html>
