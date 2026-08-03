-- ===============================================================
-- DailyPost migration 004
-- Several fallback photos per category instead of one.
--
-- With a single photo per category, ten health stories on one page
-- all showed the same picture. Each story now picks from its
-- category's set based on its id - so the choice is stable (a story
-- always looks the same) but neighbours differ.
--
-- categories.default_image is kept as a last resort in case this
-- table is ever empty.
--
-- Every URL below was checked by eye, cropped to the 16:10 shape
-- the cards actually use. An HTTP 200 is not evidence that a photo
-- shows what its search term claimed.
--
-- Safe to run more than once.
-- ===============================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS category_images (
  id       INT AUTO_INCREMENT PRIMARY KEY,
  category VARCHAR(40)  NOT NULL,
  url      VARCHAR(500) NOT NULL,
  UNIQUE KEY uniq_cat_url (category, url(180)),
  INDEX (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO category_images (category, url) VALUES

-- TECH
('tech','https://images.unsplash.com/photo-1518770660439-4636190af475?w=1200&q=80&auto=format&fit=crop'),  -- circuit board
('tech','https://images.unsplash.com/photo-1461749280684-dccba630e2f6?w=1200&q=80&auto=format&fit=crop'),  -- code on screen
('tech','https://images.unsplash.com/photo-1518773553398-650c184e0bb3?w=1200&q=80&auto=format&fit=crop'),  -- editor with markup

-- PAKISTAN
('pakistan','https://images.unsplash.com/photo-1758714144057-aae0194dfde5?w=1200&q=80&auto=format&fit=crop'),  -- Badshahi Mosque at sunset
('pakistan','https://images.unsplash.com/photo-1622546758596-f1f06ba11f58?w=1200&q=80&auto=format&fit=crop'),  -- Minar-e-Pakistan over Lahore
('pakistan','https://images.unsplash.com/photo-1595426496987-37c7113b24a6?w=1200&q=80&auto=format&fit=crop'),  -- Minar-e-Pakistan close up

-- BUSINESS
('business','https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=1200&q=80&auto=format&fit=crop'),
('business','https://images.unsplash.com/photo-1517048676732-d65bc937f952?w=1200&q=80&auto=format&fit=crop'),
('business','https://images.unsplash.com/photo-1542744173-8e7e53415bb0?w=1200&q=80&auto=format&fit=crop'),

-- CULTURE
('culture','https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?w=1200&q=80&auto=format&fit=crop'),  -- crowd with confetti
('culture','https://images.unsplash.com/photo-1763733593683-1b296c5fc039?w=1200&q=80&auto=format&fit=crop'),  -- traditional dress, celebration
('culture','https://images.unsplash.com/photo-1761124739538-587cd3e3f72a?w=1200&q=80&auto=format&fit=crop'),  -- dhol drummers

-- SPORTS
('sports','https://images.unsplash.com/photo-1461896836934-ffe607ba8211?w=1200&q=80&auto=format&fit=crop'),  -- sprinter on the blocks
('sports','https://images.unsplash.com/photo-1512719994953-eabf50895df7?w=1200&q=80&auto=format&fit=crop'),  -- cricket ground at dusk
('sports','https://images.unsplash.com/photo-1531415074968-036ba1b575da?w=1200&q=80&auto=format&fit=crop'),  -- cricket ball on grass

-- TRAVEL
('travel','https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=1200&q=80&auto=format&fit=crop'),
('travel','https://images.unsplash.com/photo-1500964757637-c85e8a162699?w=1200&q=80&auto=format&fit=crop'),
('travel','https://images.unsplash.com/photo-1494806812796-244fe51b774d?w=1200&q=80&auto=format&fit=crop'),

-- LIFESTYLE
('lifestyle','https://images.unsplash.com/photo-1633945984522-a19268cc75ad?w=1200&q=80&auto=format&fit=crop'),
('lifestyle','https://images.unsplash.com/photo-1414124488080-0188dcbb8834?w=1200&q=80&auto=format&fit=crop'),
('lifestyle','https://images.unsplash.com/photo-1519645480282-2bcc997ba3b0?w=1200&q=80&auto=format&fit=crop'),

-- INSPIRATION
('inspiration','https://images.unsplash.com/photo-1519681393784-d120267933ba?w=1200&q=80&auto=format&fit=crop'),
('inspiration','https://images.unsplash.com/photo-1500534623283-312aade485b7?w=1200&q=80&auto=format&fit=crop'),
('inspiration','https://images.unsplash.com/photo-1470252649378-9c29740c9fa8?w=1200&q=80&auto=format&fit=crop'),

-- SCIENCE
('science','https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=1200&q=80&auto=format&fit=crop'),
('science','https://images.unsplash.com/photo-1628595351029-c2bf17511435?w=1200&q=80&auto=format&fit=crop'),
('science','https://images.unsplash.com/photo-1518152006812-edab29b069ac?w=1200&q=80&auto=format&fit=crop'),

-- HEALTH
('health','https://images.unsplash.com/photo-1490645935967-10de6ba17061?w=1200&q=80&auto=format&fit=crop'),
('health','https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=1200&q=80&auto=format&fit=crop'),
('health','https://images.unsplash.com/photo-1579113800032-c38bd7635818?w=1200&q=80&auto=format&fit=crop'),

-- GENERAL
('general','https://images.unsplash.com/photo-1495020689067-958852a7765e?w=1200&q=80&auto=format&fit=crop'),
('general','https://images.unsplash.com/photo-1630343710506-89f8b9f21d31?w=1200&q=80&auto=format&fit=crop'),
('general','https://images.unsplash.com/photo-1588243291559-c4a0c36bc2dc?w=1200&q=80&auto=format&fit=crop');
