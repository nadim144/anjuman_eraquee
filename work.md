# Work Notes — Anjuman Eraquee INDIA

Repo: `nadim144/anjuman_eraquee` · Branch: `arena/01a06d4b-anjuman-eraquee` (from `dev`)

## 1. What this project is

A static HTML/CSS/jQuery website for the Anjuman Eraquee INDIA organisation, with a
small PHP layer for member registration and a lightweight admin panel.

## 2. Structure

| Path | Purpose |
|---|---|
| `index.html`, `about.html`, `contact.html`, `event.html`, `blog*.html`, `causes-*.html`, `gallery-col-*.html` | Public site pages |
| `statelevel.html`, `districtlevel.html`, `blocklevel.html`, `coreexecutive.html` | Committee / leadership listings |
| `registration.html`, `matrimonialregistration.html` | Member & matrimonial forms |
| `registerdata.php` | Form handler — writes submissions to MySQL |
| `admin/` | `login.php`, `auth.php`, `index.php`, `members.php`, `settings.php`, `logout.php` |
| `api/settings.php` | Serves `data/settings.json` as JSON (with hardcoded fallback) |
| `data/settings.json` | Editable site settings (phones, email, convenor, WhatsApp, address, YouTube URL) |
| `js/site-settings.js` | Client-side hydration of those settings into the topbar/contact areas |
| `css/`, `sass/`, `style.scss` → `style.css` | Bootstrap-based theme + custom SCSS |
| `js/` | jQuery plugins: owl carousel, bxslider, magnific-popup, wow, waypoints, isotope, meanmenu |
| `fonts/`, `images/` | Static assets |

## 3. How it works

- Pages are plain HTML; shared config is *not* templated — `js/site-settings.js`
  fetches `api/settings.php` at runtime and rewrites phone numbers, email,
  WhatsApp link, address and the embedded YouTube video.
- `admin/settings.php` edits `data/settings.json`; `admin/members.php` lists
  registrations from MySQL. Auth is a PHP session guard in `admin/auth.php`.
- `registration.html` POSTs to `registerdata.php`, which escapes inputs with
  `mysqli_real_escape_string` and inserts them.

## 4. Running locally

```bash
php -S 0.0.0.0:8080 -t .
# http://localhost:8080/index.html   admin: /admin/login.php
```
Requires PHP with `mysqli` and a MySQL database for registration/members pages.
Settings/admin-settings work without a DB; registration does not.

## 5. Issues found (prioritised)

**Security — must fix before/at deploy**
1. `registerdata.php` line 2: live DB host, user and password committed in plain
   text. Rotate the credential and move it to an untracked config file / env var.
2. `admin/login.php` line 16: credentials hardcoded as `Admin`/`Admin`. Replace
   with a hashed password (`password_hash`/`password_verify`) stored outside the repo.
3. `api/settings.php` sends `Access-Control-Allow-Origin: *` — tighten unless
   cross-origin reads are genuinely needed.
4. Registration uses `$_REQUEST` (accepts GET) with string interpolation instead of
   prepared statements — switch to `mysqli` prepared statements and `$_POST` only.
5. No CSRF token on admin forms; no rate limiting on login.

**Housekeeping**
6. No `.gitignore` — `.vs/` (Visual Studio cache) is tracked and should be ignored.
7. No README, no DB schema file. The `members` table structure only exists implicitly
   in `registerdata.php`; add `schema.sql`.
8. Duplicated markup across ~15 HTML pages (header/footer/nav). Consider PHP
   includes or a simple static-site build to stop drift.
9. Settings defaults are duplicated in `api/settings.php` and `data/settings.json`.
10. `style.css` is committed alongside `style.scss`/`sass/` with no documented build
    step — note the Sass command in the README.

## 6. Suggested next steps

- [ ] Rotate DB password, remove secrets from source, add `config.sample.php`
- [ ] Harden admin login (hashed creds + CSRF)
- [ ] Convert `registerdata.php` to prepared statements, POST-only, with validation
- [ ] Add `.gitignore` (`.vs/`, `.vscode/`, `config.php`, `*.bak`) and untrack `.vs/`
- [ ] Add `README.md` + `schema.sql`
- [ ] Factor shared header/footer into includes
