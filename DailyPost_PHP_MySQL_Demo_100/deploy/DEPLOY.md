# Deploying DailyPost

Everything here assumes InfinityFree. Any host with PHP 8 and MySQL
works the same way; only the control panel looks different.

---

## Before you start

You need an InfinityFree account. **Sign up yourself** at
infinityfree.com — account creation needs your email and a password,
and that is not something to hand to anyone else, including me.

Once you are in, create:

1. A **hosting account** — this gives you a free subdomain such as
   `dailypost.rf.gd`, plus FTP details.
2. A **MySQL database** — note down all four values it shows you:
   host, database name, username, password. They will not look like
   your local ones; InfinityFree prefixes everything.

---

## 1. Load the database

In the control panel open **phpMyAdmin**, select your new database,
then **Import** → choose `deploy/dailypost_production.sql` → Go.

You should end up with six tables, eleven categories, and no
stories. That is correct — the site starts empty.

---

## 2. Create your admin login

InfinityFree gives you no command line, so generate the login here
and paste the result over there.

On your own machine:

```
C:\xampp\php\php.exe tools/hash_password.php
```

Pick a real password — at least 12 characters, and not the local
one. The script prints an `INSERT INTO admins ...` statement and
checks the hash actually verifies before handing it to you.

Copy that statement into phpMyAdmin → **SQL** tab → Go.

---

## 3. Point the code at the live database

Copy `config/credentials.example.php` to `config/credentials.php`
and fill in the four values from step 0. Two things must change
from your local copy:

```php
'debug' => false,          // never true on a public server
'app_secret' => '...'      // generate a NEW one, see below
```

Generate a fresh secret:

```
C:\xampp\php\php.exe -r "echo bin2hex(random_bytes(32));"
```

Use a different secret from your local one. It is what hashes
visitor IP addresses for rate limiting.

---

## 4. Upload

Connect over FTP with the details from the control panel. FileZilla
is the usual client.

Upload the **contents** of the project folder into `htdocs`, so that
`index.php` sits directly inside `htdocs` — not inside another
folder.

**Upload:**

```
index.php  story.php  write.php  submit.php
search.php  about.php  subscribe.php
.htaccess
admin/  assets/  config/  includes/
```

**Do NOT upload:**

```
tools/          - can create admin accounts
migrations/     - already applied
deploy/         - these notes and the SQL dump
database_demo.sql
```

Those last four have no business being on a public server. The
`.htaccess` blocks `.sql` and `.md` files as a second line of
defence, but the real defence is not putting them there.

---

## 5. Turn on HTTPS

In the control panel, **SSL/TLS** → issue a free certificate for
your domain. It takes a few minutes to activate.

Once it works, force it by adding this to the top of `.htaccess`:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>
```

This matters for more than tidiness. The session cookie is set with
the `secure` flag only when the connection is HTTPS — so without a
certificate, an admin login can be read off an open wi-fi network.

---

## 6. Check it end to end

Work through all of it, in this order:

- [ ] Homepage loads and is styled
- [ ] Dark mode toggle works and survives a page reload
- [ ] Submit a story from `/write.php`
- [ ] It does **not** appear on the homepage
- [ ] Sign in at `/admin/login.php` with your new password
- [ ] The story is in the Pending tab
- [ ] Publish it — now it appears on the homepage
- [ ] Open it, refresh five times, confirm views only went up by one
- [ ] Search finds it
- [ ] `yoursite.com/config/credentials.php` returns **403**
- [ ] `yoursite.com/deploy/dailypost_production.sql` returns **403 or 404**

If the last two return anything else, stop and fix it before telling
anyone the address.

---

## Known InfinityFree limits

- **No outbound email.** Nothing in DailyPost sends mail, so nothing
  breaks — but it does mean you cannot add password reset later
  without moving host.
- **Daily traffic caps.** Fine for a new site; something to watch if
  it takes off.
- **Inactivity suspension.** Log into the control panel occasionally.
- **No command line.** Hence step 2.
