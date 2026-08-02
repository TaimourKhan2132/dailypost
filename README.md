# DailyPost

Live: **https://dailypostpk.page.gd**

A simple publication site. Anyone can submit a story; an editor approves it;
it becomes permanently visible to everyone.

PHP 8.2 + MySQL/MariaDB. No framework, no build step, no dependencies.

---

## Folders

| Folder | What it is |
|---|---|
| `DailyPost_PHP_MySQL_Demo_100/` | **The real project.** All work happens here. |
| `DailyPost/` | The original front-end-only prototype. Kept for reference; not used. |
| `DailyPost_PHP_Ready/` | Sheraz's empty-database variant. Superseded; not used. |
| `_upload_to_infinityfree/` | Build output for FTP. Gitignored — it holds live credentials. |

---

## Running it locally

XAMPP (Apache + MySQL + PHP) is installed at `C:\xampp`. Start Apache and MySQL
from the XAMPP Control Panel, then open http://localhost/dailypost/

`C:\xampp\htdocs\dailypost` is a **junction** pointing at
`DailyPost_PHP_MySQL_Demo_100`, so edits appear immediately with no copying.

Two local databases exist:

- `dailypost` — 100 demo stories. Good for testing pagination and search.
- `dailypost_launch` — exactly what is on the live server. Good for previewing.

Switch between them by changing `db_name` in `config/credentials.php`.

---

## How the code is arranged

```
config/credentials.php   Real DB password. GITIGNORED. Never commit.
config/db.php            PDO connection; loads helpers automatically
config/.htaccess         Blocks this whole folder from the web
includes/helpers.php     CSRF, escaping, rate limiting, image fallback
includes/header.php      Top bar, nav, theme toggle
includes/footer.php      Footer and theme-toggle script
index.php                Homepage: hero carousel, latest, most read, picks
story.php                One story, permalink, view counter, related
write.php  submit.php    Public submission form and its handler
search.php  about.php    Search with filters; about page
subscribe.php            Newsletter signups
admin/                   Login and the approve/reject dashboard
migrations/              Schema changes, applied in order
deploy/                  Production SQL and the deployment guide
tools/                   CLI only. NEVER upload this folder.
```

---

## Security decisions worth not undoing

- **Every form carries a CSRF token.** Admin actions are POST, never GET links.
  The original shipped `?action=publish&id=5` as a plain link, which let any
  page trigger admin actions on a logged-in session.
- **All validation is repeated server-side.** HTML `maxlength` is a courtesy,
  not a control.
- **Submissions are rate limited** to 3 per hour per IP; admin login locks out
  after 5 failures in 15 minutes. Both store a *hash* of the IP, never the
  address.
- **Prepared statements everywhere**, with `ATTR_EMULATE_PREPARES` off. Note
  this means a named placeholder cannot be reused within one query.
- **`debug` must stay `false`** in the live `credentials.php`.
- **Never upload** `tools/`, `migrations/`, `deploy/`, or `database_demo.sql`.
  `tools/` can create admin accounts.

---

## Gotchas that cost time once already

- **Byte order marks.** Windows PowerShell writes UTF-8 with a BOM. In a `.sql`
  file it breaks the leading `--` comment (MySQL error 1064). In a `.php` file
  it is sent to the browser before any header, breaking every redirect. When
  writing files from PowerShell, use
  `[System.IO.File]::WriteAllText($path, $text, (New-Object System.Text.UTF8Encoding($false)))`.
- **The live host's database is not `localhost`.** It is `sql103.infinityfree.com`.
- **InfinityFree serves a JavaScript challenge** to non-browser clients, so the
  live site cannot be tested with scripted HTTP requests. Check it in a browser.
- **Verify images by looking at them.** An HTTP 200 only proves a file exists.
  A batch of category photos once passed every automated check with a neon
  nightclub tunnel filed under "Pakistan".

---

## Common tasks

**Create an admin account for the live site** (InfinityFree has no shell):

```
C:\xampp\php\php.exe tools/hash_password.php
```

Paste the `INSERT` it prints into phpMyAdmin on the host.

**Change a category's fallback photo:** edit the `categories` table directly, or
add a new file under `migrations/`.

**Deploy an update:** copy the changed files into `_upload_to_infinityfree/` and
FTP them to `htdocs`. Never overwrite the live `config/credentials.php` with the
local one — the database settings differ.

Full deployment steps: `DailyPost_PHP_MySQL_Demo_100/deploy/DEPLOY.md`
