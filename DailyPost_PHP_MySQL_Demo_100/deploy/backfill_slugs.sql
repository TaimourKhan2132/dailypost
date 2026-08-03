-- ===============================================================
-- DailyPost - fill in slugs for the stories already on the site.
--
-- Import AFTER migration 005, which adds the column.
--
-- Each statement is guarded with "AND (slug IS NULL OR slug = '')",
-- so running this twice cannot overwrite a slug that has since been
-- set by hand. Ids match the seeded content.
--
-- Stories added later - submitted, imported from a spreadsheet -
-- get their slug automatically and need nothing here.
-- ===============================================================

SET NAMES utf8mb4;
UPDATE stories SET slug = 'a-quiet-morning-can-change-the-whole-day' WHERE id = 1 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'small-ideas-can-travel-far' WHERE id = 2 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'the-value-of-learning-something-new' WHERE id = 3 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'why-local-stories-matter' WHERE id = 4 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'a-better-way-to-use-technology' WHERE id = 5 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'the-power-of-a-short-walk' WHERE id = 6 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'what-makes-a-good-conversation' WHERE id = 7 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'a-notebook-full-of-possibilities' WHERE id = 8 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'finding-beauty-in-ordinary-places' WHERE id = 9 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'one-small-habit-worth-keeping' WHERE id = 10 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'a-quiet-morning-can-change-the-whole-day-2' WHERE id = 11 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'small-ideas-can-travel-far-2' WHERE id = 12 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'the-value-of-learning-something-new-2' WHERE id = 13 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'why-local-stories-matter-2' WHERE id = 14 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'a-better-way-to-use-technology-2' WHERE id = 15 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'the-power-of-a-short-walk-2' WHERE id = 16 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'what-makes-a-good-conversation-2' WHERE id = 17 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'a-notebook-full-of-possibilities-2' WHERE id = 18 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'finding-beauty-in-ordinary-places-2' WHERE id = 19 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'one-small-habit-worth-keeping-2' WHERE id = 20 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'a-quiet-morning-can-change-the-whole-day-3' WHERE id = 21 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'small-ideas-can-travel-far-3' WHERE id = 22 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'the-value-of-learning-something-new-3' WHERE id = 23 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'why-local-stories-matter-3' WHERE id = 24 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'a-better-way-to-use-technology-3' WHERE id = 25 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'the-power-of-a-short-walk-3' WHERE id = 26 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'what-makes-a-good-conversation-3' WHERE id = 27 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'a-notebook-full-of-possibilities-3' WHERE id = 28 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'finding-beauty-in-ordinary-places-3' WHERE id = 29 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'one-small-habit-worth-keeping-3' WHERE id = 30 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'a-quiet-morning-can-change-the-whole-day-4' WHERE id = 31 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'small-ideas-can-travel-far-4' WHERE id = 32 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'the-value-of-learning-something-new-4' WHERE id = 33 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'why-local-stories-matter-4' WHERE id = 34 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'a-better-way-to-use-technology-4' WHERE id = 35 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'the-power-of-a-short-walk-4' WHERE id = 36 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'what-makes-a-good-conversation-4' WHERE id = 37 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'a-notebook-full-of-possibilities-4' WHERE id = 38 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'finding-beauty-in-ordinary-places-4' WHERE id = 39 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'one-small-habit-worth-keeping-4' WHERE id = 40 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'a-quiet-morning-can-change-the-whole-day-5' WHERE id = 41 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'small-ideas-can-travel-far-5' WHERE id = 42 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'the-value-of-learning-something-new-5' WHERE id = 43 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'why-local-stories-matter-5' WHERE id = 44 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'a-better-way-to-use-technology-5' WHERE id = 45 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'the-power-of-a-short-walk-5' WHERE id = 46 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'what-makes-a-good-conversation-5' WHERE id = 47 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'a-notebook-full-of-possibilities-5' WHERE id = 48 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'finding-beauty-in-ordinary-places-5' WHERE id = 49 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'one-small-habit-worth-keeping-5' WHERE id = 50 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'a-quiet-morning-can-change-the-whole-day-6' WHERE id = 51 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'small-ideas-can-travel-far-6' WHERE id = 52 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'the-value-of-learning-something-new-6' WHERE id = 53 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'why-local-stories-matter-6' WHERE id = 54 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'a-better-way-to-use-technology-6' WHERE id = 55 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'the-power-of-a-short-walk-6' WHERE id = 56 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'what-makes-a-good-conversation-6' WHERE id = 57 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'a-notebook-full-of-possibilities-6' WHERE id = 58 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'finding-beauty-in-ordinary-places-6' WHERE id = 59 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'one-small-habit-worth-keeping-6' WHERE id = 60 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'a-quiet-morning-can-change-the-whole-day-7' WHERE id = 61 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'small-ideas-can-travel-far-7' WHERE id = 62 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'the-value-of-learning-something-new-7' WHERE id = 63 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'why-local-stories-matter-7' WHERE id = 64 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'a-better-way-to-use-technology-7' WHERE id = 65 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'the-power-of-a-short-walk-7' WHERE id = 66 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'what-makes-a-good-conversation-7' WHERE id = 67 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'a-notebook-full-of-possibilities-7' WHERE id = 68 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'finding-beauty-in-ordinary-places-7' WHERE id = 69 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'one-small-habit-worth-keeping-7' WHERE id = 70 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'a-quiet-morning-can-change-the-whole-day-8' WHERE id = 71 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'small-ideas-can-travel-far-8' WHERE id = 72 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'the-value-of-learning-something-new-8' WHERE id = 73 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'why-local-stories-matter-8' WHERE id = 74 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'a-better-way-to-use-technology-8' WHERE id = 75 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'the-power-of-a-short-walk-8' WHERE id = 76 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'what-makes-a-good-conversation-8' WHERE id = 77 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'a-notebook-full-of-possibilities-8' WHERE id = 78 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'finding-beauty-in-ordinary-places-8' WHERE id = 79 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'one-small-habit-worth-keeping-8' WHERE id = 80 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'a-quiet-morning-can-change-the-whole-day-9' WHERE id = 81 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'small-ideas-can-travel-far-9' WHERE id = 82 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'the-value-of-learning-something-new-9' WHERE id = 83 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'why-local-stories-matter-9' WHERE id = 84 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'a-better-way-to-use-technology-9' WHERE id = 85 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'the-power-of-a-short-walk-9' WHERE id = 86 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'what-makes-a-good-conversation-9' WHERE id = 87 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'a-notebook-full-of-possibilities-9' WHERE id = 88 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'finding-beauty-in-ordinary-places-9' WHERE id = 89 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'one-small-habit-worth-keeping-9' WHERE id = 90 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'a-quiet-morning-can-change-the-whole-day-10' WHERE id = 91 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'small-ideas-can-travel-far-10' WHERE id = 92 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'the-value-of-learning-something-new-10' WHERE id = 93 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'why-local-stories-matter-10' WHERE id = 94 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'a-better-way-to-use-technology-10' WHERE id = 95 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'the-power-of-a-short-walk-10' WHERE id = 96 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'what-makes-a-good-conversation-10' WHERE id = 97 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'a-notebook-full-of-possibilities-10' WHERE id = 98 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'finding-beauty-in-ordinary-places-10' WHERE id = 99 AND (slug IS NULL OR slug = '');
UPDATE stories SET slug = 'one-small-habit-worth-keeping-10' WHERE id = 100 AND (slug IS NULL OR slug = '');
