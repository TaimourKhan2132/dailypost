-- ===============================================================
-- DailyPost migration 006
-- YouTube videos for the homepage "Watch" slideshow.
--
-- Only the YouTube id is stored, never the video itself. YouTube
-- serves both the thumbnail and the video, so this costs the host
-- almost nothing: a page carries a few hundred bytes per video and
-- the player is not loaded at all until a visitor taps play.
--
-- Safe to run more than once.
-- ===============================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS videos (
  id          INT AUTO_INCREMENT PRIMARY KEY,

  -- 11 characters in practice; the column is wider in case YouTube
  -- ever lengthens them.
  youtube_id  VARCHAR(20)  NOT NULL,

  -- Read from YouTube when the video is added. Nullable, because a
  -- title lookup can fail and a missing title must not stop a video
  -- being saved.
  title       VARCHAR(200) DEFAULT NULL,

  status      ENUM('published','hidden') NOT NULL DEFAULT 'published',

  -- Lower numbers appear first. New videos go to the end.
  sort_order  INT          NOT NULL DEFAULT 0,

  created_at  DATETIME     NOT NULL,

  -- Pasting the same link twice updates the existing row rather
  -- than creating a duplicate slide.
  UNIQUE KEY uniq_youtube_id (youtube_id),
  INDEX idx_live (status, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
