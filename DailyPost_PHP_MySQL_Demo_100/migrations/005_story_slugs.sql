-- ===============================================================
-- DailyPost migration 005
-- Readable story addresses.
--
--   before:  /story.php?id=42
--   after:   /story/why-local-stories-matter
--
-- The column is nullable on purpose. Any story without a slug still
-- works on the old ?id= address, so nothing breaks between running
-- this and backfilling the existing rows.
--
-- New stories get a slug automatically - submitted through the form,
-- imported from a spreadsheet, or created in the admin panel.
--
-- After importing this, run deploy/backfill_slugs.sql to fill in the
-- stories that are already on the site.
-- ===============================================================

SET NAMES utf8mb4;

ALTER TABLE stories
  ADD COLUMN slug VARCHAR(200) NULL AFTER title,
  ADD UNIQUE KEY uniq_slug (slug);
