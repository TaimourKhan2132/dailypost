-- ===============================================================
-- DailyPost - launch content
--
-- Five short pieces written in the site's own editorial voice, so
-- the homepage is not bare on day one. Bylined "DailyPost
-- Editorial" because that is genuinely who wrote them - there are
-- no invented authors here.
--
-- Import AFTER dailypost_production.sql.
-- REPLACES whatever is currently in the stories table, so this is
-- also how you undo seed_demo_100.sql.
-- Delete any of these later from the admin panel once real stories
-- come in.
-- ===============================================================

SET NAMES utf8mb4;

DELETE FROM stories;
ALTER TABLE stories AUTO_INCREMENT = 1;

INSERT INTO stories
  (title, author, category, excerpt, body, color, image_url, status, created_at, published_at, views, featured, editors_pick)
VALUES

-- 1 -------------------------------------------------------------
('Welcome to DailyPost',
 'DailyPost Editorial',
 'general',
 'A simple place for good stories. Here is what this site is, and what it is not.',
 'Most people have something worth telling. Very few of them want to build a website to tell it.

That is the whole reason DailyPost exists. There is no account to create, no dashboard to learn, no theme to configure. You write, you submit, and an editor reads it. If it is a real piece of writing, it goes up, and it stays up.

We are deliberately small. There are no follower counts here, no reactions, no algorithm deciding who deserves attention today. Stories are listed newest first, and the ones people actually read rise into Most Read. That is the extent of the machinery.

What we are looking for is simple enough: something you wrote yourself, about something you actually noticed. It does not need to be polished or important. The best pieces we publish tend to be short, specific, and about something ordinary that the writer looked at more closely than the rest of us did.',
 'coral',
 'https://images.unsplash.com/photo-1495020689067-958852a7765e?w=1200&q=80&auto=format&fit=crop',
 'published', NOW() - INTERVAL 5 DAY, NOW() - INTERVAL 5 DAY, 0, 1, 1),

-- 2 -------------------------------------------------------------
('How publishing works here',
 'DailyPost Editorial',
 'general',
 'Write, submit, wait for a read. What happens to your story between those steps.',
 'When you press submit, your story is saved but not published. It sits in a queue where an editor can see it, and nothing about it is public yet. Not the title, not your name, not a word of the text.

An editor reads it. If it is genuine writing, it gets published and appears on the homepage straight away. If it is spam, or an advertisement wearing a story as a costume, it is rejected and never appears anywhere.

We ask for an email address, but it is optional and it is never published. It exists so an editor can come back to you if something needs checking. Leave it blank if you would rather.

One thing to know before you submit: published stories cannot be edited by their authors afterwards. This is not us being difficult. It is what stops a piece being approved as one thing and quietly rewritten into another. So read it once more before you send it.

You can also paste a link to a cover photo. If you do not, your story gets a picture based on its category, which is the reason every story here has an image without anyone having to hunt for one.',
 'blue',
 'https://images.unsplash.com/photo-1633945984522-a19268cc75ad?w=1200&q=80&auto=format&fit=crop',
 'published', NOW() - INTERVAL 4 DAY, NOW() - INTERVAL 4 DAY, 0, 1, 1),

-- 3 -------------------------------------------------------------
('What makes a story worth reading',
 'DailyPost Editorial',
 'inspiration',
 'The pieces that work here have less in common with news than you would expect.',
 'A good story makes you notice something you had walked past a hundred times.

That is a low bar and an extremely hard one. It rules out most writing that is trying to impress, because impressive writing tends to be about the writer. It also rules out most writing that is trying to inform, because information is already everywhere and nobody needs more of it from us.

What is left is narrower and more useful: someone paying close attention to one small thing, and describing it clearly enough that a stranger can see it too.

The practical version of this is that specific beats general, every time. A piece about how difficult mornings are will be read by nobody. A piece about the particular morning you missed the bus and ended up walking through a part of your city you had never seen on foot has a chance.

Length is not the point either. Some of the best things we publish are four paragraphs. If you have said it, stop.',
 'purple',
 'https://images.unsplash.com/photo-1519681393784-d120267933ba?w=1200&q=80&auto=format&fit=crop',
 'published', NOW() - INTERVAL 3 DAY, NOW() - INTERVAL 3 DAY, 0, 1, 1),

-- 4 -------------------------------------------------------------
('Why local stories matter',
 'DailyPost Editorial',
 'pakistan',
 'The changes that reshape a place are usually described best by the people living through them.',
 'Large events get covered from a long way away. Someone flies in, files a report, and flies out again, and what survives is the shape of the thing without any of its texture.

The texture is the part worth keeping. What the street sounded like. What people were saying to each other in the queue. Which shop closed, and what opened where it had been.

Nobody is better placed to record that than the people who were standing there. Not because they are more objective, but because they are the only ones who noticed.

This is the kind of writing we would most like to publish, and the kind that is hardest to get, because the people who could write it usually assume it is too ordinary to be interesting. It is not. In twenty years it will be the only account anyone has.

If you have been somewhere while it changed, write that down.',
 'green',
 'https://images.unsplash.com/photo-1758714144057-aae0194dfde5?w=1200&q=80&auto=format&fit=crop',
 'published', NOW() - INTERVAL 2 DAY, NOW() - INTERVAL 2 DAY, 0, 1, 1),

-- 5 -------------------------------------------------------------
('A few ground rules',
 'DailyPost Editorial',
 'general',
 'Short list. Mostly common sense, but worth stating plainly.',
 'Write your own work. If you did not write it, do not submit it. This includes text generated for you and passed off as yours.

Do not submit advertising. A story that exists to sell something is an advertisement no matter how it is dressed, and it will be rejected.

Put your real name on it, or a name you are willing to stand behind publicly. Your byline appears on the story permanently.

Do not write about private individuals in ways they have not agreed to. Writing about your own life is fine. Writing about someone else''s, in identifiable detail, is not yours to publish.

Be accurate. If you are stating something as fact, be sure of it. If you are not sure, say so in the piece - honest uncertainty reads better than false confidence, and it is a great deal easier to defend.

That is all of it. Everything else is editorial judgement, and we would rather explain a decision than write another rule.',
 'yellow',
 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=1200&q=80&auto=format&fit=crop',
 'published', NOW() - INTERVAL 1 DAY, NOW() - INTERVAL 1 DAY, 0, 1, 0);
