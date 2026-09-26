# Architecture Decision Records (ADRs)

## Anjuman Eraquee INDIA — Log of Architectural Decisions, Rationale & Trade-offs

| Metadata | Details |
| :--- | :--- |
| **Document Version** | 1.0.0 |
| **Date** | September 20, 2026 |
| **Status** | Active Architecture History |
| **Format Standard** | Michael Nygard ADR Template |

---

## Index of Architectural Decisions

- [ADR-001: Hybrid Static HTML with Modular PHP Endpoints](#adr-001-hybrid-static-html-with-modular-php-endpoints)
- [ADR-002: Centralized Database Helper & Non-Destructive Auto-Migrations](#adr-002-centralized-database-helper--non-destructive-auto-migrations)
- [ADR-003: Dynamic Content System via File-Based JSON Storage](#adr-003-dynamic-content-system-via-file-based-json-storage)
- [ADR-004: FPDF Engine for Vector PDF Membership Certificates](#adr-004-fpdf-engine-for-vector-pdf-membership-certificates)
- [ADR-005: Unified Member Credential Authentication for RBAC](#adr-005-unified-member-credential-authentication-for-rbac)
- [ADR-006: Temporary Password Issuance for Admin-Assisted Account Recovery](#adr-006-temporary-password-issuance-for-admin-assisted-account-recovery)
- [ADR-007: Persistent Server-Side Certificate Caching](#adr-007-persistent-server-side-certificate-caching)
- [ADR-008: Local Apache Port 8080 Configuration](#adr-008-local-apache-port-8080-configuration)

---

### ADR-001: Hybrid Static HTML with Modular PHP Endpoints
* **Status**: Accepted
* **Context**: The existing marketing portal was built as 17 standalone HTML pages using a Bootstrap Mosque theme. Rebuilding the entire site in a heavy framework (e.g., Laravel, React, Next.js) would require massive migration effort, server overhead, and high hosting costs on basic shared plans.
* **Decision**: Maintain static HTML pages for general marketing content and bolt on lightweight, modular PHP endpoints (`user-login.php`, `registration.php`, `user-dashboard.php`, `admin/`) for dynamic database-backed features.
* **Consequences**:
  * *Pros*: Lightning-fast page loads for public visitors, zero compile step, works out-of-the-box on inexpensive shared hosting (InfinityFree).
  * *Cons*: Header/footer markup duplicated across 17 HTML files (mitigated by `js/site-settings.js` for dynamic phone/email injection).

---

### ADR-002: Centralized Database Helper & Non-Destructive Auto-Migrations
* **Status**: Accepted
* **Context**: The platform is deployed on shared hosts (InfinityFree) lacking SSH command-line access or Composer/migration CLI runners. Manually executing complex SQL queries in phpMyAdmin introduces human error and risks breaking production data.
* **Decision**: Implement a centralized `db.php` that establishes connection pooling across multiple port configurations (`3307`, `3306`) and automatically runs non-destructive schema migrations on runtime. It checks `SHOW COLUMNS FROM user_registrtion` before appending missing columns and creates `admin_users` if absent.
* **Consequences**:
  * *Pros*: Zero-configuration deployment — simply uploading files automatically migrates the database on the first page visit. 100% non-destructive with zero data loss.
  * *Cons*: Minor overhead on the first database call per script execution (negligible in PHP/MySQL environments with cached schema).

---

### ADR-003: Dynamic Content System via File-Based JSON Storage
* **Status**: Accepted
* **Context**: Community leaders frequently update contact phone numbers, convenor details, and live YouTube broadcasts. Querying the MySQL database on every public static page visit would add unnecessary load and database connection limits on shared hosting.
* **Decision**: Store dynamic site settings in `data/settings.json`, served via `api/settings.php` and injected client-side via `js/site-settings.js`.
* **Consequences**:
  * *Pros*: Blazing fast reads with zero database connections consumed for public visitors; easy to backup and inspect.
  * *Cons*: Requires write permissions on `data/settings.json`.

---

### ADR-004: FPDF Engine for Vector PDF Membership Certificates
* **Status**: Accepted
* **Context**: Members require an official, printable membership certificate. Heavy headless Chrome generators (Puppeteer, Dompdf) require substantial memory and PHP extensions often restricted or unavailable on shared hosts.
* **Decision**: Embed the lightweight, pure-PHP **FPDF** library directly in `fpdf/`.
* **Consequences**:
  * *Pros*: Zero external system dependencies, executes in under 150 milliseconds, extremely low RAM footprint (< 4MB).
  * *Cons*: Requires procedural coordinate-based layout logic rather than standard CSS styling.

---

### ADR-005: Unified Member Credential Authentication for RBAC
* **Status**: Accepted
* **Context**: Initially, the admin portal used hardcoded credentials (`Admin/Admin`). Creating a separate admin credential table with independent passwords would force administrators to maintain two separate accounts (one as a registered community member, one as an admin).
* **Decision**: Create an `admin_users` table with a `user_id` foreign key pointing to `user_registrtion(id)`. Administrators log in using their registered community member mobile/email and personal password.
* **Consequences**:
  * *Pros*: Single source of truth. No password duplication. When a member changes their password in their dashboard, their admin login reflects it immediately. Instant promotion and revocation by linking/unlinking `user_id`.
  * *Cons*: If an admin account's member profile password is forgotten, it must be reset through the member password reset flow.

---

### ADR-006: Temporary Password Issuance for Admin-Assisted Account Recovery
* **Status**: Accepted
* **Context**: Many elderly community members lack digital password management tools and frequently forget their passwords. Without automated SMS delivery, members cannot complete self-service OTP verification.
* **Decision**: Provide administrators with a "Issue Temporary Password" tool in `admin/members.php`. Setting a temporary password flags `is_temp_password = 1` in the database. When the member logs in with this temporary password, the system forces them to enter a new personal permanent password before granting dashboard access.
* **Consequences**:
  * *Pros*: Completely resolves lockout issues without requiring SMS API costs; ensures members retain personal privacy by forcing a permanent password change.
  * *Cons*: Requires offline communication (phone call / WhatsApp) between admin and member to convey the temporary password.

---

### ADR-007: Persistent Server-Side Certificate Caching
* **Status**: Accepted
* **Context**: Generating a PDF on every download request consumes CPU cycles and risks rate limits on shared servers when thousands of members download certificates during national conventions.
* **Decision**: Save the generated PDF file to `uploads/certificates/cert_{id}.pdf` on first creation and update `certificate_path` in `user_registrtion`. Subsequent download requests stream the pre-generated file directly from disk.
* **Consequences**:
  * *Pros*: Eliminates redundant PDF rendering; near-instantaneous download speed for members.
  * *Cons*: Consumes server disk storage; profile photo edits require invalidating or regenerating the cached certificate.

---

### ADR-008: Local Apache Port 8080 Configuration
* **Status**: Accepted
* **Context**: Windows IIS and World Wide Web Publishing Service frequently occupy Port 80, causing XAMPP Apache startup failure (`Port 80 in use by PID 4`).
* **Decision**: Configure local Apache to listen on Port `8080` (`http://localhost:8080/anjuman_eraquee/`).
* **Consequences**:
  * *Pros*: Reliable, conflict-free local development on Windows.
  * *Cons*: Local development URLs must specify `:8080`.

