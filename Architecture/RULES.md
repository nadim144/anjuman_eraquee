# Engineering Rules & Coding Standards

## Anjuman Eraquee INDIA — Architectural Directives & Guidelines

| Metadata | Details |
| :--- | :--- |
| **Document Version** | 1.0.0 |
| **Date** | September 20, 2026 |
| **Status** | Mandatory Engineering Standard |
| **Applicability** | All Developers, Maintainers & AI Coding Agents |

---

## 1. Core Principles

1. **Zero Downtime & Zero Regression**: Every change must maintain complete backwards compatibility with existing community member accounts, uploaded photographs, and generated certificates.
2. **Defensive Programming**: Assume all external inputs are untrusted. Validate on the client, sanitize strictly on the server, and escape on every render.
3. **Non-Destructive Database Evolution**: Never execute irreversible SQL (`DROP`, `TRUNCATE`, or wide unconstrained `DELETE`). All schema migrations must be additive and self-healing.

---

## 2. PHP Backend Standards

### A. Database Queries & Sanitization
- **Strict Escaping**: Every string parameter passed into a `mysqli_query` MUST be escaped using `mysqli_real_escape_string($conn, $val)` or parameterized.
- **Integer Casting**: Numeric values (`id`, `user_id`, `step`, `age`) MUST be explicitly cast using `intval($_POST['id'])` or `(int)` before interpolation.
- **Error Handling**: Suppress raw database driver errors in production. Never echo `mysqli_error($conn)` directly to user-facing pages where sensitive table names or column names could be exposed.

### B. Session Management
- **Safe Initialization**: Always verify that headers have not been sent before calling `session_start()`:
  ```php
  if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
      session_start();
  }
  ```
- **Strict Role Verification**: Never rely solely on client-side cookies or query parameters for authorization. Always check `$_SESSION['admin_role']` on the server before executing administrative tasks.

### C. Password Security
- **Modern Hashing**: All passwords must be hashed using `password_hash($password, PASSWORD_BCRYPT)`.
- **Verification**: Verify exclusively via `password_verify($password, $hashedFromDb)`. Maintain fallback check for legacy records during transition if required, but immediately re-hash on next successful login.

---

## 3. Database Migration Rules

1. **Additive Only**: You may add new columns or tables using `CREATE TABLE IF NOT EXISTS` and dynamic column presence checks (`SHOW COLUMNS FROM`).
2. **Never Drop Existing Columns**: Legacy code and live InfinityFree databases rely on existing fields. Never rename or drop existing columns.
3. **The `db.php` Auto-Migrator**: Any new column or table required by the platform MUST be registered in `run_db_migrations()` inside [db.php](file:///c:/xampp/htdocs/anjuman_eraquee/db.php). This ensures that simply uploading the files to a remote host automatically migrates the database on first visit without requiring terminal access.

---

## 4. Frontend & JavaScript Rules

### A. Cross-Site Scripting (XSS) Prevention
- **Avoid Unescaped `innerHTML`**: Do not concatenate user-supplied input (e.g. member names, addresses, phone numbers) directly into `innerHTML`.
- **DOM API Preferred**: Use `element.textContent`, or safely construct nodes using `document.createElement()` and `appendChild()`:
  ```javascript
  // SAFE:
  const item = document.createElement('div');
  item.textContent = memberData.username;
  container.appendChild(item);
  ```
- **Server-Side Escaping**: In PHP templates, all dynamic values must be escaped using `htmlspecialchars($val, ENT_QUOTES, 'UTF-8')`.

### B. Mobile First & UI Consistency
- **Touch Targets**: All clickable buttons and navigation links must maintain a minimum target area of `44px` height on mobile screens.
- **No Text Truncation Without Tooltips**: Vital data such as phone numbers, member IDs, and names must remain visible or wrap cleanly without breaking table rows.

---

## 5. Security & RBAC Protection Rules

1. **Super Admin Safety Lock**:
   - The primary Super Administrator account (**`ahmad.nadim144@gmail.com`**, Admin ID: 1, Member ID: 3) CANNOT be deleted, suspended, or demoted under any circumstance.
   - Any attempt to revoke or delete this account must be blocked at both the PHP controller level and UI button level.
2. **Privilege Demarcation**:
   - Standard Admins (`role = 'admin'`) are strictly forbidden from viewing or triggering actions on `admin/admins.php`.
   - Only the Super Admin can promote a registered member or revoke admin privileges.

---

## 6. File Upload & Media Storage Rules

- **Allowed Formats**: Profile pictures must be restricted to valid image types (`image/jpeg`, `image/png`, `image/webp`).
- **File Renaming**: Uploaded files must be renamed with unique non-predictable identifiers (e.g. `member_{id}_{timestamp}_{rand}.jpg`) to prevent directory traversal and file overwrite collisions.
- **Execution Prevention**: Store uploaded files in `uploads/profile_pictures/` or `uploads/certificates/`. Never store executable `.php` scripts in media directories.

---

## 7. Git & Version Control Protocol

### A. Conventional Commit Messages
Commit messages must follow the standard format:
- `feat(scope): ...` for new features (e.g., `feat(admin): implement RBAC console`)
- `fix(scope): ...` for bug fixes (e.g., `fix(auth): prevent session warning on CLI`)
- `docs(scope): ...` for documentation (e.g., `docs(arch): add system architecture`)
- `refactor(scope): ...` for code refactoring without feature changes

### B. Zero-Secret Policy
- **Never commit live production passwords** (FTP passwords, cPanel credentials, live MySQL passwords) into Git repositories.
- Local configuration overrides must reside in `db_config.php` and be excluded via `.gitignore`.

