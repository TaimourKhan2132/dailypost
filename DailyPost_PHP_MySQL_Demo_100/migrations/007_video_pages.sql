-- ===============================================================
-- DailyPost migration 007
-- Gives every video its own page, the way a story has one.
--
--   /watch/andrew-ng-the-biggest-opportunities-in-ai
--
-- slug        readable address, same idea as stories.slug
-- description the text that sits under the player
-- views       so a video can be counted and ranked like a story
--
-- All three are nullable or defaulted, so videos added before this
-- migration keep working - they simply fall back to ?id= links
-- until a slug is filled in.
--
-- Safe to run more than once is NOT true of ALTER TABLE, so this
-- checks first.
-- ===============================================================

SET NAMES utf8mb4;

-- Adding a column that already exists is an error, which would stop
-- the whole import. These statements are skipped when the column is
-- already present.
SET @add_slug := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE videos ADD COLUMN slug VARCHAR(200) NULL AFTER youtube_id,
                        ADD UNIQUE KEY uniq_video_slug (slug)',
    'SELECT "slug already present"')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'videos' AND COLUMN_NAME = 'slug'
);
PREPARE s FROM @add_slug; EXECUTE s; DEALLOCATE PREPARE s;

SET @add_desc := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE videos ADD COLUMN description TEXT NULL AFTER title',
    'SELECT "description already present"')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'videos' AND COLUMN_NAME = 'description'
);
PREPARE s FROM @add_desc; EXECUTE s; DEALLOCATE PREPARE s;

SET @add_views := (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE videos ADD COLUMN views INT NOT NULL DEFAULT 0',
    'SELECT "views already present"')
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'videos' AND COLUMN_NAME = 'views'
);
PREPARE s FROM @add_views; EXECUTE s; DEALLOCATE PREPARE s;
