-- ---------------------------------------------------------------
-- DailyPost migration 001
-- Adds everything the new front-end design needs.
-- Run this AFTER importing database_demo.sql.
-- ---------------------------------------------------------------

-- 1. CATEGORIES ---------------------------------------------------
-- The screenshot shows coloured badges (TRAVEL, TECH, PAKISTAN...).
-- Keeping these in a table rather than hard-coding them means Sheraz
-- can add a category later without anyone touching the PHP.
--
-- default_image is the fallback photo: if a writer pastes a broken
-- image link, the story falls back to its category's picture instead
-- of showing an empty grey box.

CREATE TABLE IF NOT EXISTS categories (
  slug          VARCHAR(40)  NOT NULL PRIMARY KEY,
  name          VARCHAR(60)  NOT NULL,
  badge_color   VARCHAR(20)  NOT NULL,          -- CSS colour for the badge
  default_image VARCHAR(500) NOT NULL,          -- fallback cover photo
  sort_order    INT          NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO categories (slug, name, badge_color, default_image, sort_order) VALUES
  ('tech',        'Tech',        '#2563eb', '', 1),
  ('pakistan',    'Pakistan',    '#16a34a', '', 2),
  ('business',    'Business',    '#7c3aed', '', 3),
  ('culture',     'Culture',     '#ea580c', '', 4),
  ('sports',      'Sports',      '#0891b2', '', 5),
  ('travel',      'Travel',      '#dc2626', '', 6),
  ('lifestyle',   'Lifestyle',   '#db2777', '', 7),
  ('inspiration', 'Inspiration', '#e11d48', '', 8),
  ('science',     'Science',     '#059669', '', 9),
  ('health',      'Health',      '#0d9488', '', 10),
  ('general',     'General',     '#6b7280', '', 99)
ON DUPLICATE KEY UPDATE name = VALUES(name);


-- 2. NEW COLUMNS ON stories --------------------------------------
-- category      - which badge to show, links to categories.slug
-- image_url     - the link the writer pastes; NULL means use the
--                 category default
-- featured      - 1 = appears in the hero carousel (5 slides)
-- editors_pick  - 1 = appears in the Editor's Picks row
-- author_avatar - the little round photo next to the author's name

ALTER TABLE stories
  ADD COLUMN category      VARCHAR(40)  NOT NULL DEFAULT 'general' AFTER author,
  ADD COLUMN image_url     VARCHAR(500) DEFAULT NULL               AFTER color,
  ADD COLUMN author_avatar VARCHAR(500) DEFAULT NULL               AFTER image_url,
  ADD COLUMN featured      TINYINT(1)   NOT NULL DEFAULT 0,
  ADD COLUMN editors_pick  TINYINT(1)   NOT NULL DEFAULT 0;


-- 3. INDEXES ------------------------------------------------------
-- The homepage runs four separate queries. Without these indexes
-- MySQL reads every row each time; with them it jumps straight to
-- the rows it needs. Cheap now, essential at a few thousand stories.

CREATE INDEX idx_featured  ON stories (status, featured,     published_at);
CREATE INDEX idx_picks     ON stories (status, editors_pick, published_at);
CREATE INDEX idx_mostread  ON stories (status, views);
CREATE INDEX idx_category  ON stories (status, category,     published_at);


-- 4. SPAM CONTROL -------------------------------------------------
-- Records one row per submission so submit.php can refuse a third
-- story from the same IP within an hour. We store a HASH of the IP,
-- not the address itself - enough to spot a flood, but it isn't
-- personal data sitting in the database.

CREATE TABLE IF NOT EXISTS submission_log (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  ip_hash      CHAR(64) NOT NULL,
  submitted_at DATETIME NOT NULL,
  INDEX (ip_hash, submitted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- 5. SPREAD THE DEMO DATA ACROSS THE NEW DESIGN -------------------
-- The 100 demo stories all look identical right now. This scatters
-- them across categories and flags a few as featured and picks, so
-- the new homepage has something realistic to render.

UPDATE stories SET category = ELT(1 + (id % 10),
  'tech','pakistan','business','culture','sports',
  'travel','lifestyle','inspiration','science','health');

UPDATE stories SET featured     = 1 WHERE status = 'published' ORDER BY views DESC LIMIT 5;
UPDATE stories SET editors_pick = 1 WHERE status = 'published' AND featured = 0 ORDER BY id DESC LIMIT 4;
