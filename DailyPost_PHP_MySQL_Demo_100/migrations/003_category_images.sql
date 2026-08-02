-- ---------------------------------------------------------------
-- DailyPost migration 003
-- Fallback photo for each category.
--
-- Used when a writer leaves the image box blank, or pastes a link
-- that fails validation. A dead link that passes validation is
-- caught in the browser instead, by the onerror handler on the img
-- tag, which swaps in the same picture.
--
-- Every URL here was checked by eye, not just for an HTTP 200.
-- That matters: an earlier batch all returned valid JPEGs and the
-- "Pakistan" one turned out to be a neon-lit tunnel.
--
-- Source: Unsplash. Free for commercial use, no attribution
-- required. The query string asks their CDN for a 1200px wide
-- version at 80% quality, cropped to fill.
-- ---------------------------------------------------------------

-- Set one by one rather than with a clever single statement: it is
-- easier to swap a single picture later without decoding a query.

UPDATE categories SET default_image = 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=1200&q=80&auto=format&fit=crop' WHERE slug = 'tech';         -- circuit board
UPDATE categories SET default_image = 'https://images.unsplash.com/photo-1758714144057-aae0194dfde5?w=1200&q=80&auto=format&fit=crop' WHERE slug = 'pakistan';     -- Badshahi Mosque, Lahore, at sunset
UPDATE categories SET default_image = 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=1200&q=80&auto=format&fit=crop' WHERE slug = 'business';     -- two people working over laptops
UPDATE categories SET default_image = 'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?w=1200&q=80&auto=format&fit=crop' WHERE slug = 'culture';      -- concert crowd with confetti
UPDATE categories SET default_image = 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?w=1200&q=80&auto=format&fit=crop' WHERE slug = 'sports';       -- sprinter on starting blocks
UPDATE categories SET default_image = 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=1200&q=80&auto=format&fit=crop' WHERE slug = 'travel';       -- wooden boat on a mountain lake
UPDATE categories SET default_image = 'https://images.unsplash.com/photo-1633945984522-a19268cc75ad?w=1200&q=80&auto=format&fit=crop' WHERE slug = 'lifestyle';    -- tea on books by a window
UPDATE categories SET default_image = 'https://images.unsplash.com/photo-1519681393784-d120267933ba?w=1200&q=80&auto=format&fit=crop' WHERE slug = 'inspiration';  -- Milky Way over mountains
UPDATE categories SET default_image = 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=1200&q=80&auto=format&fit=crop' WHERE slug = 'science';      -- Earth from orbit at night
UPDATE categories SET default_image = 'https://images.unsplash.com/photo-1490645935967-10de6ba17061?w=1200&q=80&auto=format&fit=crop' WHERE slug = 'health';       -- fresh food bowl
UPDATE categories SET default_image = 'https://images.unsplash.com/photo-1495020689067-958852a7765e?w=1200&q=80&auto=format&fit=crop' WHERE slug = 'general';      -- person reading a newspaper
