# Project Tasks & Development Roadmap

## Anjuman Eraquee INDIA — Task Breakdown, Implementation Tracking & Milestones

| Metadata | Details |
| :--- | :--- |
| **Document Version** | 1.2.0 |
| **Date** | September 20, 2026 |
| **Status** | Active / Sprint Tracking |
| **Total Tracked Tasks** | 44 |
| **Completed Tasks** | 39 (89%) |
| **In Progress** | 1 (2%) |
| **Pending / Planned** | 4 (9%) |

---

## 📊 High-Level Status Dashboard

```
┌─────────────────┬─────────────────┬─────────────────┬─────────────────┐
│   TOTAL TASKS   │    COMPLETED    │   IN PROGRESS   │   NOT STARTED   │
│       44        │       39        │        1        │        4        │
│                 │     [ 89% ]     │     [ 2% ]      │     [ 9% ]      │
└─────────────────┴─────────────────┴─────────────────┴─────────────────┘
```

---

## Phase 1: Foundation & Public Marketing Website
*Establish responsive multi-page marketing portal and layout architecture.*

| # | Task Description | Priority | Status | Verification & Notes |
| :---: | :--- | :---: | :---: | :--- |
| **1.1** | Setup 17 public marketing HTML pages (Home, About, Causes, Events, Executive, Gallery, Blog, Contact) | High | Completed | Clean markup, all navigation links active |
| **1.2** | Integrate Bootstrap responsive layout and mobile hamburger menu | High | Completed | Standardized in `js/custom.js` without redundant submenus |
| **1.3** | Implement dynamic phone & email synchronization via `data/settings.json` and `js/site-settings.js` | Medium | Completed | Topbar and footer phone numbers dynamic across all 17 pages |
| **1.4** | Configure XAMPP local environment on Port 8080 | Medium | Completed | Resolved IIS port conflict on localhost |
| **1.5** | Update slider highlight color from orange to light blue (`#38bdf8`) | Low | Completed | Verified in `style.css` |

---

## Phase 2: Database Layer & Member Registration Engine
*Build robust MySQL persistence, centralized connection helper, and multi-step intake wizard.*

| # | Task Description | Priority | Status | Verification & Notes |
| :---: | :--- | :---: | :---: | :--- |
| **2.1** | Create centralized `db.php` helper with connection pooling and multi-port fallback (3307, 3306) | High | Completed | Connects seamlessly on both local and production hosts |
| **2.2** | Implement runtime non-destructive database auto-migrator for `user_registrtion` table | High | Completed | Checks `SHOW COLUMNS FROM` before executing additive `ALTER TABLE` |
| **2.3** | Build 3-part multi-step registration wizard (`registration.php`) | High | Completed | Step 1 (Personal) -> Step 2 (Address) -> Step 3 (Education/Password) |
| **2.4** | Add automatic real-time age calculation from Date of Birth picker | Medium | Completed | Real-time JavaScript calculation + server verification |
| **2.5** | Add Community Cast dropdown control with 8 standard subdivisions | Medium | Completed | `Kalal`, `Kalwar`, `Kalar`, `Eraquee(Iraqi)`, `Kalal Lari`, etc. |
| **2.6** | Implement Profile Picture upload with instant client-side preview | Medium | Completed | Replaced broken inline SVG with dedicated `images/dummy-avatar.svg` |
| **2.7** | Introduce and validate 12-digit Aadhaar number and alternate mobile number | Medium | Completed | Stored in database, sanitized, and displayed in Member Dossier |

---

## Phase 3: Member Authentication & Self-Service Dashboard
*Secure member login, password self-recovery, and profile management.*

| # | Task Description | Priority | Status | Verification & Notes |
| :---: | :--- | :---: | :---: | :--- |
| **3.1** | Implement member login supporting Mobile or Email + Password (`user-login.php`) | High | Completed | Password authentication via `password_verify` (Bcrypt) |
| **3.2** | Build Member Self-Service Dashboard (`user-dashboard.php`) | High | Completed | Shows verification status, profile overview, and certificate CTA |
| **3.3** | Implement Member Profile Editing modal and controller | Medium | Completed | Allows updating contact, addresses, education, and profession |
| **3.4** | Build password reset request system (`reset_requested` flag) | Medium | Completed | Allows members to notify administrators when locked out |
| **3.5** | Create interactive password visibility eye-toggle icon | Low | Completed | Embedded in Registration, Member Login, and Admin Login |
| **3.6** | Refine Member Dashboard mobile header and action button pills | Low | Completed | Unified "Home", "Dashboard", "Logout" pills for small viewports |

---

## Phase 4: Automated PDF Membership Certificate System
*Dynamic vector certificate generation, official styling, and persistent storage.*

| # | Task Description | Priority | Status | Verification & Notes |
| :---: | :--- | :---: | :---: | :--- |
| **4.1** | Integrate FPDF library into backend engine (`fpdf/`) | High | Completed | Lightweight vector PDF engine with zero external dependencies |
| **4.2** | Design official Anjuman Eraquee Membership Certificate layout | High | Completed | Double border, green/gold branding, seals, and official signature |
| **4.3** | Render circular profile photograph on certificate header | High | Completed | Uses FPDF image masking and precise coordinate positioning |
| **4.4** | Implement persistent server-side caching (`uploads/certificates/`) | High | Completed | Saves generated certificate to disk, records path in DB to avoid re-rendering |
| **4.5** | Add dynamic certificate download controller (`download-certificate.php`) | High | Completed | Secure session check before streaming PDF to browser |

---

## Phase 5: Role-Based Access Control (RBAC) Admin Portal
*Database-backed dynamic admin login, role segregation, and dedicated management console.*

| # | Task Description | Priority | Status | Verification & Notes |
| :---: | :--- | :---: | :---: | :--- |
| **5.1** | Auto-create `admin_users` table and seed primary Super Admin | High | Completed | `ahmad.nadim144@gmail.com` initialized as `super_admin` (`active`) |
| **5.2** | Remove hardcoded `Admin/Admin` credentials from `admin/login.php` | High | Completed | Dynamic authentication using registered member credentials |
| **5.3** | Build RBAC authorization helpers (`is_super_admin()`, `require_super_admin()`) | High | Completed | Located in `admin/auth.php` |
| **5.4** | Create dedicated Super Admin Console (`admin/admins.php`) | High | Completed | Active admin stats, member promotion dropdown, suspend/revoke actions |
| **5.5** | Enforce immutable primary Super Admin safety lock | High | Completed | Account cannot be deleted, suspended, or demoted |
| **5.6** | Upgrade Member Directory (`admin/members.php`) with RBAC actions | High | Completed | `⭐️ Make Admin` and `🚫 Remove Admin` buttons in table rows |
| **5.7** | Update Admin Topbar with dynamic role badges and names | Low | Completed | `👑 Super Admin` and `🛡️ Admin` dynamic badge |

---

## Phase 6: Production Hardening & Live Deployment
*Preparation for remote host deployment and ongoing monitoring.*

| # | Task Description | Priority | Status | Verification & Notes |
| :---: | :--- | :---: | :---: | :--- |
| **6.1** | Automated PHP syntax linting across all modified endpoints | High | Completed | 0 syntax errors detected via `php -l` |
| **6.2** | Comprehensive automated RBAC integration testing | High | Completed | Verified via `test_rbac.php` scratch test |
| **6.3** | Create comprehensive 9-document Architecture suite | High | Completed | Full engineering documentation in `Architecture/` |
| **6.4** | Deploy updated files and database migrations to Live Host via FileZilla | High | In Progress | Uploading modified `admin/`, `db.php`, `work.md` |

---

## Phase 7: SMS Gateway & Automated Verification (Upcoming)
*Third-party carrier integration for automated OTP and notices.*

| # | Task Description | Priority | Status | Verification & Notes |
| :---: | :--- | :---: | :---: | :--- |
| **7.1** | Procure and configure Fast2SMS / Twilio API account | High | Not Started | Gateway API Key acquisition |
| **7.2** | Wire real SMS dispatch into `user-verify-otp.php` | High | Not Started | Replace simulated dev mode OTP with real-time SMS |
| **7.3** | Implement rate limiting (maximum 3 OTP requests per 15 minutes per IP) | Medium | Not Started | Brute-force protection |

---

## Phase 8: Kalal Eraquee Community Matrimonial System
*End-to-end matrimonial platform with 3-tier privacy, reciprocal matchmaking, and proposals.*

| # | Task Description | Priority | Status | Verification & Notes |
| :---: | :--- | :---: | :---: | :--- |
| **8.1** | Schema migration for 4 matrimonial tables in `db.php` | High | Completed | `matrimonial_profiles`, `matrimonial_photos`, `matrimonial_interests`, `matrimonial_access_requests` |
| **8.2** | Candidate profile creation wizard (`matrimonial-create.php`) | High | Completed | Supports Self, Son, Daughter, Brother, Sister, Relative; Talaq Shuda, Khula Shuda, Bewa statuses; auto-age & code generation |
| **8.3** | Member Matrimonial Hub (`matrimonial-manage.php`) | High | Completed | 4-tab console: My Candidate Profiles, Received Interests (Accept/Decline), Sent Interests, and Unlocked Contacts |
| **8.4** | Privacy-first matrimonial directory (`matrimonial.php`) | High | Completed | Filter by Dulha/Dulhan, Marital Status, Caste, City; enforces Tier 1 vs Tier 2 blur/lock; direct proposal CTA |
| **8.5** | Candidate Biodata View (`matrimonial-profile-view.php`) | High | Completed | Tier 1/2/3 dynamic reveal, proposal modal, and guardian contact request modal |
| **8.6** | Admin Matrimonial Moderation Console (`admin/matrimonial.php`) | High | Completed | 1-click Contact Access Request approval/rejection and candidate profile management |
| **8.7** | End-to-end automated integration testing | High | Completed | Verified via `test_matrimonial.php` (profile codes, mutual interest, admin contact release) |
| **8.8** | Candidate Profile Edit Wizard (`matrimonial-edit.php`) | High | Completed | Pre-populated inputs, photo replacement, security owner/admin checks |
| **8.9** | Admin / Super Admin Profile Approval Workflow | High | Completed | `pending_approval` lifecycle, inline review bar on `matrimonial-profile-view.php`, and 3-tab approval console in `admin/matrimonial.php` |


