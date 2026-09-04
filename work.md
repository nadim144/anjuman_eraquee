# Anjuman Eraquee INDIA — Work Log

This file tracks all development work done on the **Anjuman Eraquee India** website project, organized date-wise.

---

## 2026-09-01

### 🔧 Hosting Troubleshooting (InfinityFree)
- Diagnosed that the deployed site on InfinityFree was missing CSS, JS, and image files.
- Root cause: folder upload failures via the web-based file manager (filemanager.ai).
- **Solution recommended:** Use FileZilla (FTP client) or Windows File Explorer FTP to upload entire folder structure correctly.
- Provided step-by-step guide to upload files via FileZilla to InfinityFree hosting (`anjumaneraquee.wuaze.com`).

### 🎨 Slider Text Highlight Color Change
- **Issue:** Certain slider caption words were highlighted in **orange** (`#e5ae49`).
- **Change:** Updated highlight color to **light blue** (`#38bdf8`).
- **Files modified:**
  - `d:\Anjuman\style.css`

---

## 2026-09-03

### 🛡️ Admin Panel — Created
- Built a full custom **PHP Admin Panel** under `d:\Anjuman\admin\`.
- **Login credentials:** Username: `Admin` | Password: `Admin`
- **Features:**
  - Session-based authentication.
  - Dashboard with quick stats.
  - Site Settings editor (phone numbers, emails).
  - Registered Members directory (reads from MySQL DB).
- **Files created:**
  - `admin/auth.php` — Session authentication guard.
  - `admin/login.php` — Login form.
  - `admin/logout.php` — Session destroy & redirect.
  - `admin/index.php` — Admin dashboard.
  - `admin/settings.php` — Content editor (phone numbers, etc.).
  - `admin/members.php` — Members listing from DB.
  - `admin/css/admin.css` — Admin panel styling.

### ⚙️ Dynamic Content System — Created
- Created a **JSON-based settings system** so Admin Panel changes reflect on the live site without a database for basic content.
- **Files created:**
  - `data/settings.json` — Stores editable site settings (phone numbers, emails, etc.).
  - `api/settings.php` — PHP API endpoint that serves `settings.json`.
  - `js/site-settings.js` — Frontend JS that fetches settings and injects them into all HTML pages dynamically.
- **Script tag injected** into all `.html` files in the project to load `site-settings.js`.

### 📞 Topbar Phone Numbers — Made Dynamic
- Phone numbers in `.header-event .count-list` (topbar) are now dynamically updated from `data/settings.json`.
- Admin can change phone numbers in Admin Panel → Site Settings → they instantly reflect on all pages.

### 📞 Footer Phone Numbers — Made Dynamic
- Added logic to `js/site-settings.js` to also update phone numbers in the **footer** (`about-foo` section).
- Now, a single change in Admin Panel updates both topbar AND footer phone numbers across all pages.

### 💻 XAMPP Setup Guide
- User installed **XAMPP** for local PHP/MySQL development.
- Resolved Apache startup error (Port 80 conflict).
- **Solution:** Changed Apache to run on port **8080**.
  - `httpd.conf`: Changed `Listen 80` → `Listen 8080`
  - `httpd.conf`: Changed `ServerName localhost:80` → `ServerName localhost:8080`
- Local site accessible at: `http://localhost:8080/Anjuman/index.html`
- Admin Panel accessible at: `http://localhost:8080/Anjuman/admin/login.php`

---

## 2026-09-04

### 🔗 Header Navigation Links — Updated
- Updated `header-social text-right` section across all 30 HTML pages.
- **Format updated to:**
  `Join Membership | User Login | Admin Login`
- **Admin Login** links directly to `admin/login.php`.

### 🎨 Main Navigation Bar Styling — Height & Color Update
- **Target class:** `header .main-header` (`.main-header.hidden-sm-down`)
- **Height reduced to `90px`** across all pages (updated logo, menu links, and action buttons `line-height` to `90px`).
- **Background Color:** Kept original white (`#ffffff`).
- **File modified:** `d:\Anjuman\style.css` (applies to all HTML pages sitewide).

### 📝 Work Log — Created
- Created this `work.md` file in `d:\Anjuman\` to track all development work date-wise.

---

## 📋 Pending / Next Steps

- [ ] Set up **MySQL database** in phpMyAdmin for the Registration/Membership feature.
  - Create database: `anjuman_db` (or match name in `registerdata.php`).
  - Create table: `user_registrtion` with required fields.
- [ ] Test **Registration Form** end-to-end locally (form submit → DB → Admin members list).
- [ ] Implement **User Login** functionality (currently placeholder `#`).
- [ ] Upload updated files to **InfinityFree** hosting via FileZilla.

