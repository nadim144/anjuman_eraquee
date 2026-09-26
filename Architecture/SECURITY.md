# Security Architecture & Hardening Guide

## Anjuman Eraquee INDIA — Security Posture, Threat Modeling & Defensive Controls

| Metadata | Details |
| :--- | :--- |
| **Document Version** | 1.0.0 |
| **Date** | September 20, 2026 |
| **Status** | Active / Security Policy |
| **Target Infrastructure** | Shared Hosting (Apache / PHP / MySQL) & Local Environments |
| **Audience** | Security Auditors, Lead Developers & System Maintainers |

---

## 1. Threat Model & Attack Surface

The Anjuman Eraquee platform is a public-facing community website with self-registration, file uploads, member dashboards, and an administrative control panel. The primary threat vectors include:

1. **Unauthorized Administrative Access**: Brute-force attacks against the admin portal or privilege escalation from a standard member account.
2. **SQL Injection (SQLi)**: Malicious inputs in search forms, registration fields, or login parameters.
3. **Cross-Site Scripting (XSS)**: Malicious scripts embedded in member profile fields (e.g. name, address, feedback message) that execute when viewed by an administrator.
4. **Malicious File Uploads**: Uploading executable `.php` scripts disguised as `.jpg` profile pictures to achieve Remote Code Execution (RCE).
5. **Credential Leakage**: Exposing database passwords or secret keys in public version control repositories.

---

## 2. Authentication & Credential Architecture

### A. Modern Cryptographic Password Hashing
- **Algorithm**: `PASSWORD_BCRYPT` via PHP's native `password_hash()` and `password_verify()`.
- **Salting**: Automatic cryptographically secure per-password salting handled natively by Bcrypt.
- **Cost Factor**: Default adaptive cost factor (cost 10) balancing computational complexity and server response times.

### B. Single Source of Truth for RBAC
- Administrators authenticate using their existing registered member credentials.
- **Why this is secure**: Eliminates the risk of stale, forgotten, or hardcoded passwords (`Admin/Admin`). When an administrator updates their personal password in the Member Dashboard, their administrative login is automatically updated simultaneously.
- **Account Suspension**: When an admin account's status is toggled to `'inactive'` in `admin_users`, login is instantly denied even if the member password is valid.

### C. Primary Super Admin Immutability
- The primary Super Administrator account (**`ahmad.nadim144@gmail.com`**, Admin ID: 1, User ID: 3) is hard-locked at both the database layer logic and UI level.
- Even if another administrator manages to execute a POST request to demote or delete the primary Super Admin, the backend script enforces an explicit check:
  ```php
  if ($targetAdm['role'] === 'super_admin' || strtolower($targetAdm['email']) === 'ahmad.nadim144@gmail.com' || $targetAdm['id'] == 1) {
      $feedbackMsg = "Security Protection: The primary Super Admin account cannot be revoked or demoted.";
      // Execution terminated
  }
  ```

---

## 3. Injection Countermeasures

### A. SQL Injection (SQLi) Defense
1. **Strict Escaping**: Every dynamic user input is sanitized through `mysqli_real_escape_string($conn, $val)` before being interpolated into SQL queries.
2. **Integer Casting**: Numeric query arguments (`id`, `user_id`, `step`, `age`) are strictly cast using `intval()` or `(int)`.
3. **Additive Auto-Migrator Protection**: Database migrations in `db.php` verify column existence using `SHOW COLUMNS FROM` before executing `ALTER TABLE`, preventing malformed syntax or partial query injections.

### B. Cross-Site Scripting (XSS) Defense
1. **Server-Side Output Encoding**: All dynamic values echoed into HTML templates are sanitized using `htmlspecialchars($data, ENT_QUOTES, 'UTF-8')`.
2. **Safe Client-Side DOM Construction**: In JavaScript files (such as `admin/members.php` modals and `js/site-settings.js`), values are injected via `textContent` or `document.createTextNode()`, eliminating DOM-based XSS:
   ```javascript
   // SECURE:
   var item = document.createElement('div');
   item.className = 'detail-value';
   item.textContent = member.username || '-';
   container.appendChild(item);
   ```

---

## 4. Session & Access Control Hardening

### A. Server-Side Privilege Enforcement
- Front-end element hiding (such as hiding the `🛡️ Manage Admins` sidebar link for non-super admins) is strictly reinforced with server-side authorization guards:
  ```php
  function require_super_admin() {
      check_admin_auth();
      if (!is_super_admin()) {
          header('Location: index.php?error=unauthorized');
          exit;
      }
  }
  ```
- Any unauthorized direct request to `admin/admins.php` or `action=promote_to_admin` triggers immediate termination and redirects to `index.php?error=unauthorized`.

### B. Session Security
- Sessions are initialized safely with `session_status()` checks.
- On logout, sessions are destroyed completely via `session_unset()` and `session_destroy()`.

---

## 5. File Upload & Media Hardening

Profile picture uploads pose a severe potential threat on shared hosting environments if unconstrained. The platform implements the following protective layers:

1. **File Extension Whitelisting**: Only `.jpg`, `.jpeg`, `.png`, and `.webp` extensions are permitted.
2. **MIME-Type Verification**: File MIME-types are validated on the server via `mime_content_type()` or `getimagesize()`.
3. **Non-Executable Storage Directory**: All uploads are stored under `uploads/profile_pictures/` or `uploads/certificates/`. An `.htaccess` configuration inside `uploads/` disables script execution:
   ```apache
   # Prevent PHP script execution in upload directory
   <FilesMatch "\.(php|phtml|php5|php7|phps)$">
       Order Deny,Allow
       Deny from all
   </FilesMatch>
   ```
4. **File Renaming**: Uploaded files are renamed using unpredictable timestamps and random tokens (e.g. `user_3_1726589123_a8f9.jpg`).

---

## 6. Repository Hygiene & Secret Management

- **Public Repository Security**: Live database passwords, FTP keys, or API tokens must NEVER be committed to GitHub.
- **Local Overrides**: Use `db_config.php` for local or private server overrides, and ensure this file is listed in `.gitignore`.
- **Safe Defaults**: `db.php` relies on fallback loops that prioritize local standard ports (`8080`, `3307`, `3306`) without hardcoding live production secrets.

