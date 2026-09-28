# DailyPost — Project Briefing
*As of 18 Sep 2026. Contains no passwords, usernames or secrets.*

## What it is
**DailyPost** is a small community publication for a Pakistani audience: anyone can submit a story, an editor approves it, and it's then permanently public. Owner is **Sheraz** (non-technical); built and maintained by **Taimour**.

- **Live site:** https://dailypostpk.page.gd
- **Intended domain:** `dailypost.com.pk` (not yet registered)
- **Repo:** github.com/TaimourKhan2132/dailypost (private)

## Origin
Sheraz arrived with three ChatGPT-generated folders: a static prototype that saved stories only in the visitor's own browser, and two near-identical PHP + MySQL versions. The PHP backend was a usable skeleton but had serious flaws: the admin password hash was fabricated (so login could never work), publish/reject were plain GET links open to CSRF, the database dump could be downloaded, and there was no spam protection. It was hardened, redesigned to match a reference screenshot, and deployed.

## Stack & hosting
- **PHP 8.2 + MySQL/MariaDB**, no framework, no build step, no dependencies
- **Hosting:** InfinityFree (free) — PHP, MySQL, FTP, free SSL
- **Local dev:** XAMPP at `C:\xampp`; `htdocs\dailypost` is a junction to the project folder
- **Deploy:** files staged in `_upload_to_infinityfree/`, pushed over FTP; database changes applied by hand in phpMyAdmin (the host has no command line)

## Features

### For readers
- Homepage: 5-slide featured carousel, Latest Stories (paginated), ranked Most Read, Editor's Picks, Daily Digest and Newsletter panels
- Readable story URLs (`/story/why-local-stories-matter`); old `?id=` links 301-redirect to them
- Search by keyword and category; About page with live stats
- Share buttons on every story: WhatsApp, Facebook, X, copy link
- Dark mode, fully mobile-responsive, floating green Write button on phones
- **Installable as a web app (PWA):** manifest, service worker, offline page, maskable icons. Chrome/Edge get an install prompt; iPhone gets Share → Add to Home Screen instructions
- 11 categories, each with 3 hand-verified fallback photos
- Submission form: Title · Name or Email · Category · Story. An email is kept private and only the part before the @ is shown as the byline

### For the editor (admin panel)
- Pending / Published / Rejected queue with publish, reject, unpublish, delete
- Toggle stories into the carousel or Editor's Picks; set a cover image on approval
- Read-count controls: set exact number, +1/+10/+100/−10, reset, reset all
- Bulk **CSV import/export** for editing in Excel (the id column makes it round-trip; "Replace everything" mode for wholesale swaps)
- Plain-text export of all stories
- Editor sign-in link sits quietly in the footer (no public accounts exist)

### For search engines
- Dynamic `sitemap.xml` and `robots.txt`, canonical tags, Open Graph tags, favicon set, custom 404 page
- Google Search Console verified; a URL live test returned "URL is available to Google"

## Code layout (`DailyPost_PHP_MySQL_Demo_100/`)
```text
config/          db.php, credentials.php (gitignored), .htaccess blocking the folder
includes/        helpers.php, header.php, footer.php
admin/           login, auth, dashboard, import, export_csv, export
migrations/      001–005, applied in order
deploy/          production SQL, seed files, DEPLOY.md
tools/           CLI-only scripts (password hash, slug backfill). NEVER upload
*.php            index, story, write, submit, search, about, 404,
                 sitemap, robots, manifest, offline, pwa-check
sw.js            service worker
```

Also in the repo: the original prototype (`DailyPost/`), Sheraz's unused variant (`DailyPost_PHP_Ready/`), an Android WebView wrapper (`DailyPost_Android_App_Project/`, never built — shelved in favour of the PWA), and a one-page status PDF for Sheraz.

## Database
Tables: `stories` (with slug, category, image_url, featured, editors_pick, views, status), `categories`, `category_images`, `admins`, `subscribers`, `submission_log`, `login_attempts`.

## Security in place
- CSRF tokens on every form; all admin actions are POST
- Prepared statements with emulation **off**
- Server-side validation of everything
- Honeypot + 3 submissions/hour per IP; admin lockout after 5 failed logins (IPs stored as salted hashes)
- bcrypt passwords, session regeneration on login, hardened cookies
- `.htaccess` blocks `.sql`/`.md` files and the config folder; security headers; `debug` off on live

## Hard-won gotchas
- **Windows PowerShell writes UTF-8 with a BOM.** It broke a SQL import, and in a PHP file it breaks every redirect.
- **InfinityFree shows bots a JavaScript challenge**, so the live site can't be tested by script — only in a real browser.
- **Pretty URLs break relative links.** Everything goes through `base_path()`.
- **MySQL `rowCount()` after UPDATE counts rows changed, not matched** — this once made CSV re-imports duplicate every story.
- **`[hidden]` loses to more specific CSS**; an explicit `!important` rule exists for this reason.
- **Verify images by eye.** An HTTP 200 once passed a neon nightclub photo filed under "Pakistan".
- **CSS is cache-busted** via `?v=<filemtime>`.

## Incident — 31 Aug 2026
Sheraz uploaded an unrelated project ("AIBO 2026", a photo slideshow) into DailyPost's folder, which overwrote `index.php`. All 22 other files were checked byte for byte and were untouched. `index.php` was restored from git on 1 Sep and the stray README removed. The database was never affected. The overwritten files were saved to `_rescue_2026-09-01/`.

## Git status
- **Branch:** `main`, working tree **clean**
- **History:** 33 commits, 2 Aug → 1 Sep 2026
- **⚠️ 1 commit ahead of GitHub:** `13a4f85 Ignore incident rescue folders` is unpushed. A fetch failed on 18 Sep because github.com was unreachable from this machine.
- **Gitignored:** `config/credentials.php`, `_upload_to_infinityfree/`, `_rescue_*/`
- An audit of the full history found no credentials committed.

## Open items

### Urgent
1. **No database backup exists.** A month of Sheraz's articles live only on free hosting. Export via phpMyAdmin now, then monthly.
2. **Give Sheraz a separate hosting account** for side projects so this can't happen again.
3. **Push the unpushed commit** once GitHub is reachable.

### Security
4. Rotate the hosting (vPanel) password — it appeared in an earlier chat log.
5. Change the admin password; it was set identical to the username (not confirmed whether that's been done).

### Pending feature request (not yet built)
6. A random view assigner that gives each story 5k–20k views, shown publicly to readers, with the editor able to see the number before approving and set it to zero.

### Housekeeping
7. Register `dailypost.com.pk` (~Rs 3,600 for two years via PKNIC; price not verified). Sitemap, robots and manifest adapt to a new domain automatically.
8. Resubmit the sitemap in Search Console (an indexing request hit the daily quota).
9. Confirm migration 004 (category photos) was imported on live. Migration 005 and the slug backfill evidently were, since story URLs work.
10. Check whether the 100 placeholder demo stories, with their invented bylines and read counts, are still live alongside the real articles.
11. The status PDF for Sheraz is outdated (it still says five editorial articles).
12. `pwa-check.php`, a diagnostic page, is still on the live server. It's unlinked and noindexed but can be removed.
