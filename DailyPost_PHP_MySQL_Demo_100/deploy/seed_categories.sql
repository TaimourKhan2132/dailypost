-- ===============================================================
-- DailyPost - categories only
--
-- Use this when the tables already exist and only the category
-- rows are missing. Importing dailypost_production.sql a second
-- time fails with "Table 'admins' already exists", because that
-- file creates the tables.
--
-- Safe to run repeatedly: existing rows are updated, not
-- duplicated. Touches no stories and no admin accounts.
-- ===============================================================

SET NAMES utf8mb4;
INSERT INTO `categories` (`slug`, `name`, `badge_color`, `default_image`, `sort_order`) VALUES ('business','Business','#7c3aed','https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=1200&q=80&auto=format&fit=crop',3) ON DUPLICATE KEY UPDATE name=VALUES(name), badge_color=VALUES(badge_color), default_image=VALUES(default_image), sort_order=VALUES(sort_order);
INSERT INTO `categories` (`slug`, `name`, `badge_color`, `default_image`, `sort_order`) VALUES ('culture','Culture','#ea580c','https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?w=1200&q=80&auto=format&fit=crop',4) ON DUPLICATE KEY UPDATE name=VALUES(name), badge_color=VALUES(badge_color), default_image=VALUES(default_image), sort_order=VALUES(sort_order);
INSERT INTO `categories` (`slug`, `name`, `badge_color`, `default_image`, `sort_order`) VALUES ('general','General','#6b7280','https://images.unsplash.com/photo-1495020689067-958852a7765e?w=1200&q=80&auto=format&fit=crop',99) ON DUPLICATE KEY UPDATE name=VALUES(name), badge_color=VALUES(badge_color), default_image=VALUES(default_image), sort_order=VALUES(sort_order);
INSERT INTO `categories` (`slug`, `name`, `badge_color`, `default_image`, `sort_order`) VALUES ('health','Health','#0d9488','https://images.unsplash.com/photo-1490645935967-10de6ba17061?w=1200&q=80&auto=format&fit=crop',10) ON DUPLICATE KEY UPDATE name=VALUES(name), badge_color=VALUES(badge_color), default_image=VALUES(default_image), sort_order=VALUES(sort_order);
INSERT INTO `categories` (`slug`, `name`, `badge_color`, `default_image`, `sort_order`) VALUES ('inspiration','Inspiration','#e11d48','https://images.unsplash.com/photo-1519681393784-d120267933ba?w=1200&q=80&auto=format&fit=crop',8) ON DUPLICATE KEY UPDATE name=VALUES(name), badge_color=VALUES(badge_color), default_image=VALUES(default_image), sort_order=VALUES(sort_order);
INSERT INTO `categories` (`slug`, `name`, `badge_color`, `default_image`, `sort_order`) VALUES ('lifestyle','Lifestyle','#db2777','https://images.unsplash.com/photo-1633945984522-a19268cc75ad?w=1200&q=80&auto=format&fit=crop',7) ON DUPLICATE KEY UPDATE name=VALUES(name), badge_color=VALUES(badge_color), default_image=VALUES(default_image), sort_order=VALUES(sort_order);
INSERT INTO `categories` (`slug`, `name`, `badge_color`, `default_image`, `sort_order`) VALUES ('pakistan','Pakistan','#16a34a','https://images.unsplash.com/photo-1758714144057-aae0194dfde5?w=1200&q=80&auto=format&fit=crop',2) ON DUPLICATE KEY UPDATE name=VALUES(name), badge_color=VALUES(badge_color), default_image=VALUES(default_image), sort_order=VALUES(sort_order);
INSERT INTO `categories` (`slug`, `name`, `badge_color`, `default_image`, `sort_order`) VALUES ('science','Science','#059669','https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=1200&q=80&auto=format&fit=crop',9) ON DUPLICATE KEY UPDATE name=VALUES(name), badge_color=VALUES(badge_color), default_image=VALUES(default_image), sort_order=VALUES(sort_order);
INSERT INTO `categories` (`slug`, `name`, `badge_color`, `default_image`, `sort_order`) VALUES ('sports','Sports','#0891b2','https://images.unsplash.com/photo-1461896836934-ffe607ba8211?w=1200&q=80&auto=format&fit=crop',5) ON DUPLICATE KEY UPDATE name=VALUES(name), badge_color=VALUES(badge_color), default_image=VALUES(default_image), sort_order=VALUES(sort_order);
INSERT INTO `categories` (`slug`, `name`, `badge_color`, `default_image`, `sort_order`) VALUES ('tech','Tech','#2563eb','https://images.unsplash.com/photo-1518770660439-4636190af475?w=1200&q=80&auto=format&fit=crop',1) ON DUPLICATE KEY UPDATE name=VALUES(name), badge_color=VALUES(badge_color), default_image=VALUES(default_image), sort_order=VALUES(sort_order);
INSERT INTO `categories` (`slug`, `name`, `badge_color`, `default_image`, `sort_order`) VALUES ('travel','Travel','#dc2626','https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=1200&q=80&auto=format&fit=crop',6) ON DUPLICATE KEY UPDATE name=VALUES(name), badge_color=VALUES(badge_color), default_image=VALUES(default_image), sort_order=VALUES(sort_order);
