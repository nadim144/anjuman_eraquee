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

---

## 2026-09-05

### 🔐 User Login & OTP Profile Dashboard — Implemented
- **User Login (`user-login.php`):** Form where registered members enter their mobile number to receive a 6-digit OTP.
- **OTP Verification (`user-verify-otp.php`):** Validates entered OTP code (with Dev Mode OTP display & fallback code `123456`).
- **Member Dashboard (`user-dashboard.php`):** Displays all registered member details (Personal info, Address, Contact details, Qualifications, Occupation) fetched from MySQL DB.
- **User Logout (`user-logout.php`):** Clears user session securely.
- **Header Integration:** Updated "User Login" header links across all HTML files & `js/site-settings.js` to point to `user-login.php`.
- **Files created:** `user-login.php`, `user-verify-otp.php`, `user-dashboard.php`, `user-logout.php`.

### 🛠️ Database Connection Error Resolution & Port Reconfiguration
- **Diagnosed Issue:** Resolved "Database connection error. Please try again later." across `user-login.php`, `registerdata.php`, `user-dashboard.php`, `admin/index.php`, and `admin/members.php`.
- **Root Cause:** A standalone Windows Service `MySQL80` (MySQL 8.0) was occupying default port `3306`, preventing XAMPP MariaDB (holding the `codecxss_anjuman` database) from serving queries.
- **XAMPP MySQL Port Change:** Configured `c:\xampp\mysql\bin\my.ini` to set default client and mysqld port to `3307`, allowing MariaDB to run smoothly without port conflicts.
- **Centralized Database Connection Helper (`db.php`):** Created `db.php` with robust connection fallback supporting port `3307` (XAMPP MariaDB) and `3306` across multiple credentials/hosts.
- **Refactored PHP Database Connections:** Updated all PHP endpoints (`user-login.php`, `registerdata.php`, `user-dashboard.php`, `admin/index.php`, `admin/members.php`) to use `get_db_connection()`.
- **Verification:** Verified end-to-end connection to `10.4.32-MariaDB` on port `3307` and confirmed `codecxss_anjuman` database query execution.

### 📱 Real-Time OTP API Integration Analysis
- Researched real-time OTP SMS delivery architecture and cURL integration patterns for Indian SMS gateways (Fast2SMS, MSG91, Twilio).
- Summarized pricing structures, free developer trial credits, and step-by-step cURL helper functions for production deployment.

### 🚀 Registration, Password Authentication, Admin CRUD & PDF Certificate Upgrade
- **Registration Form Improvements (`registration.html`):**
  - Renamed input label to "Enter your Full Name *" and updated attribute to `name="FullName"` (with backward compatibility for `username`).
  - Replaced Age text input with a Date of Birth picker (`<input type="date" name="dob">`) and real-time auto-calculated Age field.
  - Added Password & Confirm Password inputs with real-time match validation and minimum length check.
- **Backend Registration Processor (`registerdata.php`):**
  - Added extraction of `FullName`, `dob`, and server-side calculated `age`.
  - Securely hashed passwords using PHP `password_hash($password, PASSWORD_BCRYPT)`.
  - Added duplicate check for email and mobile number, redirecting to `user-login.php` on success.
- **Dual Authentication & Password Reset Flow (`user-login.php`, `user-reset-password.php`):**
  - Upgraded login page with tabbed interface: Login with Password (via Mobile OR Email) or Login with OTP.
  - Added "Forgot Password?" request workflow allowing users to notify Admin.
  - Added `user-reset-password.php` forcing users who log in with a temporary password to create their permanent password.
- **Official Membership Certificate Download (`download-certificate.php`, `fpdf/`):**
  - Integrated lightweight, standalone FPDF library.
  - Built official Certificate of Membership PDF with Anjuman Eraquee branding, member details, welcome declaration, and authorized signatory.
  - Added "Download Membership Certificate (PDF)" action to `user-dashboard.php`.
- **Admin Panel Full Member Management (`admin/members.php`, `admin/index.php`):**
  - Full CRUD: View all member details (modal with 22+ fields), edit member data, and delete member with confirmation.
  - Temporary Password generation: Admin can issue temporary passwords (`Temp@xxxx`) to members who requested a reset.
  - Filter by Password Reset Requests and live count badges on Admin dashboard.

---

## 2026-09-13

### 📱 Mobile Hamburger Menu — Added Membership & Login Links & Consistent Headers
- **Issues Addressed:**
  1. On mobile viewport (`<= 767px`), the topbar (`.header-social`) is hidden via `hidden-sm-down`. The mobile MeanMenu hamburger drawer did not display "Join Membership", "User Login", and "Admin Login".
  2. `user-login.php` rendered without the site header and had no mobile hamburger menu.
  3. `admin/login.php` rendered without the site header and had no mobile hamburger menu.
  4. On smaller screens, the mobile menu drawer did not scroll and cut off items at the bottom.
- **Solution:**
  - Added `max-height: calc(100vh - 70px)` and `overflow-y: auto` to `.mean-container .mean-nav` in `css/meanmenu.min.css` so the mobile menu smoothly scrolls on all mobile screens and all items are visible.
  - Added the full site header (topbar, desktop menu, and mobile MeanMenu) to `user-login.php`.
  - Added the full site header (topbar, desktop menu, and mobile MeanMenu) to `admin/login.php`.
  - Removed the `Membership` accordion dropdown completely as requested, retaining the clean direct top-level menu items: `Join Membership`, `User Login`, and `Admin Login` directly below `Contact Us`.
  - Standardized the `<nav id="dropdown">` structure across all 17 HTML pages, `user-login.php`, and `admin/login.php` so the mobile hamburger menu behavior and options are 100% consistent across every single page.
  - Added `.htaccess` to emit `Cache-Control: no-cache, no-store, must-revalidate` and `Pragma: no-cache` so browsers (especially mobile/DevTools) always serve fresh content and do not retain stale cached menus.
- **Login UI Enhancement & Visual Consistency:**
  - Standardized `user-login.php` layout and card structure to match `admin/login.php` using `.user-login-wrapper` and `.login-card`.
  - Added proper margin-top and padding (`50px 15px`, with `@media (max-width: 767px)` margin-top and padding) with `clear: both;` to ensure the login card is cleanly separated from the mobile header and never clipped or pressed against the top bar.
  - Added official Anjuman Eraquee INDIA logo (`images/logo/logo.png`) above the headings on both `user-login.php` and `admin/login.php` for enhanced appearance.
  - Removed input prepend icons (`fa fa-user`, `fa fa-lock`, and `fa fa-phone`) from `user-login.php` to achieve a clean input field presentation matching `admin/login.php`.
- **Files updated:**
  - `user-login.php`, `admin/login.php`
  - `css/meanmenu.min.css`
  - `index.html`, `about.html`, `registration.html`, `contact.html`, `blocklevel.html`, `blog-single.html`, `blog.html`, `causes-list.html`, `causes-single.html`, `coreexecutive.html`, `districtlevel.html`, `event.html`, `gallery-col-2.html`, `gallery-col-3.html`, `gallery-col-4.html`, `matrimonialregistration.html`, `statelevel.html`
  - `js/site-settings.js`
  - `.htaccess`

### 🚀 Progressive Multi-Part Registration & Join Membership Sign-Up System
- **Responsive Join Membership Sign-Up Dialog (`js/site-settings.js`, `signup.php`):**
  - Integrated a global responsive Sign-Up Modal dialog across desktop and mobile devices triggered by clicking "Join Membership" anywhere on the site.
  - Collects Mobile Number, Email Address, Password, and Confirm Password with eye icon visibility toggles.
  - Checks for existing accounts, hashes password via `password_hash`, starts session, and redirects to `registration.php`.
- **Progressive Multi-Part Registration Wizard (`registration.php`, `api/save-profile-step.php`):**
  - Designed responsive 3-step wizard with real-time step progress indicator:
    - **Part 1: Personal Details:** Full Name, Father's Name, Mother's Name, Grandfather's Name, Native Place, Date of Birth with auto-age calculation, Gender, and Marital Status. Features asynchronous **Save** and **Next** buttons.
    - **Part 2: Address Details:** Current Address (House/Street, Village/Post, District, Pincode, State, Country) and Permanent Address with a *"Permanent address is the same as Present address"* toggle that synchronizes values in real-time. Features **Previous**, **Save**, and **Next** buttons.
    - **Part 3: Education & Professional Details:** Highest Qualification, Qualification Details, Current Occupation, Occupation Details, WhatsApp Number, and Suggestions/Feedback. Features **Previous**, **Save**, and final **Submit Registration** button that marks profile completed and redirects to `user-dashboard.php`.
  - Automatically loads and pre-populates existing data for logged-in users so they can review, continue, or edit their details.
  - For unauthenticated direct visits, renders a clean Sign-Up card directly on the page.
  - Updated `registration.html` to seamlessly redirect to `registration.php`.
- **Password Eye Icon Toggle:**
  - Added interactive show/hide password toggle (`fa fa-eye` / `fa fa-eye-slash`) across the Sign-Up modal, `registration.php`, `user-login.php`, and `admin/login.php`.
- **User Dashboard Profile Editing (`user-dashboard.php`):**
  - Added **"Edit Profile Details"** button in the dashboard welcome banner and edit links on each section card (Personal, Contact & Address, Education & Profession), directing users to `registration.php` to correct any data.
  - All edits immediately reflect on `user-dashboard.php` and in the downloadable official PDF certificate (`download-certificate.php`).
- **Files created/updated:**
  - `signup.php` (created)
  - `registration.php` (created)
  - `api/save-profile-step.php` (created)
  - `js/site-settings.js` (updated)
  - `user-dashboard.php` (updated)
  - `user-login.php` (updated)
  - `admin/login.php` (updated)
  - `registration.html` (updated)
  - `download-certificate.php` (updated)

### 📸 Registration Enhancements & Personal Details Upgrades
- **Profile Picture Upload:**
  - Added profile picture upload component in Part 1 (Personal Details) with circular preview, change photo camera badge, and file selection.
  - Validates file formats (JPG, PNG, WEBP) and maximum file size (5MB).
  - Automatically uploads to `uploads/profile_pictures/` via `api/save-profile-step.php` and updates `user_registrtion.profile_picture`.
  - Added profile picture display on `user-dashboard.php` welcome header and on the official Membership Certificate (`download-certificate.php`).
- **Aadhaar Card Number Field with Validation:**
  - Introduced 12-digit Indian Aadhaar Number field with live space formatting (`XXXX XXXX XXXX`).
  - Added strict client-side and server-side validation: must be exactly 12 digits, cannot start with 0 or 1, cannot consist of identical repeating numbers (`111111111111`, etc.).
  - Shows real-time visual indicator (green checkmark or red validation message).
  - Saved in database and printed on Member Dashboard and Membership Certificate.
- **Removed Age (Auto) Field:**
  - Removed `Age (Auto)` input field from the UI as Date of Birth is sufficient. Date of Birth now occupies full column width.
- **Removed "Other" from Gender:**
  - Simplified Gender options to **Male** and **Female**.
- **Removed Redundant Header Links on `registration.php`:**
  - Removed `Join Membership`, `User Login`, and `Admin Login` links from the topbar, main desktop navigation, and mobile MeanMenu drawer on `registration.php` for a cleaner registration interface.

### 🔄 Personal Details Layout Optimization & Additional Mobile Field
- **Dropdown Controls for Gender & Marital Status:**
  - Converted **Gender \*** and **Marital Status \*** from radio buttons to clean `<select>` dropdown controls.
- **3-Column Grid Layout:**
  - Arranged **Date of Birth \***, **Gender \***, and **Marital Status \*** side-by-side in a balanced 3-column row (`col-sm-4 col-xs-12` each) directly after the family details, maintaining a compact and elegant form layout.
  - Followed immediately by **Aadhaar Card Number \*** (`col-sm-6`) and **Additional Mobile Number** (`col-sm-6`).
  - Removed **Native Place \*** from Part 1 (Personal Details) as requested.
- **Additional Mobile Number:**
  - Added dedicated **Additional Mobile Number** field with real-time input formatting and validation.
  - Validates 10 digits starting with 6, 7, 8, or 9 (`^[6-9]\d{9}$`).
  - Ensures the additional mobile number cannot be identical to the primary registered mobile.
  - Saved to `user_registrtion.additional_mobile` and displayed in the Member Dashboard and PDF Certificate.

### 📜 Certificate Enhancements & Centered Circular Profile Picture
- **Centered Circular Vector Profile Picture:**
  - Implemented custom vector clipping algorithm (`ClippedCircleImage`) in FPDF to render the member's profile picture in an elegant round/circle shape with dual green (`#009146`) and gold (`#d97706`) concentric borders.
  - Positioned **centered horizontally** directly below **"This is to certify that"** and directly above the member's **Full Name**, creating a formal and prestigious visual certificate hierarchy.
  - Added automatic aspect-ratio center cropping so any uploaded image (portrait or landscape) renders inside the circle without stretching or distortion.
- **Certificate Details Table Updated:**
  - Replaced the removed Native Place column with **Alternate Mobile** (`additional_mobile`).

### 💾 Persistent File & Database Storage
- **Can Profile Pictures & Generated Certificates be stored in the database?**
  - **Yes, absolutely:**
    1. **Method 1 (Industry Standard & Implemented): Server File Storage + Database Path:**
       - Files are stored on the server disk (`uploads/profile_pictures/` and `uploads/certificates/`).
       - The database table `user_registrtion` stores the file path (`profile_picture` and `certificate_path`) along with generation timestamp (`certificate_generated_at`).
       - *Advantages:* Keeps the MySQL database lightweight, fast queries, rapid `mysqldump` backups, doesn't exceed MySQL `max_allowed_packet` limits, and allows web servers (Apache) to stream downloads efficiently.
    2. **Method 2 (Direct MySQL Binary BLOB):**
       - Table columns use `LONGBLOB` (up to 4GB) or `MEDIUMBLOB` (up to 16MB) storing raw binary byte streams (`file_get_contents`).
       - *Trade-off:* Rapidly balloons the MySQL database file size (1,000 members = several hundred MBs to GBs of database dump size).
  - **Implemented Workflow:** Every time a member downloads their certificate (or an administrator generates one), the generated PDF is saved to disk as `uploads/certificates/certificate_member_{id}.pdf` and the database `certificate_path` and `certificate_generated_at` columns are automatically updated.

### 🗄️ Database Schema Updates (`user_registrtion`)
The following columns have been added to `user_registrtion`:
1. **`profile_picture`** (`VARCHAR(255) NULL`) — Relative path to uploaded member profile photo.
2. **`aadhaar_number`** (`VARCHAR(20) NULL`) — 12-digit Aadhaar number.
3. **`additional_mobile`** (`VARCHAR(20) NULL`) — Alternate 10-digit mobile contact number.
4. **`certificate_path`** (`VARCHAR(255) NULL`) — Relative path to the generated PDF certificate.
5. **`certificate_generated_at`** (`DATETIME NULL`) — Timestamp of when the certificate was generated.
6. **`registration_step`** (`TINYINT(1) DEFAULT 1`) — Current wizard step progress (`1`, `2`, `3`).
7. **`is_profile_completed`** (`TINYINT(1) DEFAULT 0`) — Flag indicating completed registration.

**Production / InfinityFree SQL Migration Command:**
```sql
ALTER TABLE user_registrtion 
ADD COLUMN profile_picture VARCHAR(255) NULL AFTER username,
ADD COLUMN `cast` VARCHAR(100) NULL AFTER maritalstatus,
ADD COLUMN aadhaar_number VARCHAR(20) NULL AFTER dob,
ADD COLUMN additional_mobile VARCHAR(20) NULL AFTER phonenumber,
ADD COLUMN certificate_path VARCHAR(255) NULL,
ADD COLUMN certificate_generated_at DATETIME NULL,
ADD COLUMN registration_step TINYINT(1) DEFAULT 1,
ADD COLUMN is_profile_completed TINYINT(1) DEFAULT 0;
```

If your live database already had previous columns added, simply run:
```sql
ALTER TABLE `user_registrtion` ADD COLUMN `cast` VARCHAR(100) NULL AFTER `maritalstatus`;
```

---

## 2026-09-20

### 🏛️ Comprehensive Architecture Documentation Suite (`Architecture/`)
Established a complete 9-document engineering architecture suite based on modern AI-assisted software engineering and architecture frameworks:
- **`Architecture/PRD.md`**: Product Requirements Document detailing platform vision, problem statement, user personas, complete feature inventory (Public Portal, Multi-Step Registration, PDF Certificates, RBAC, Matrimonial), KPIs, and 10-phase roadmap.
- **`Architecture/ARCHITECTURE.md`**: System Architecture Document containing Mermaid system diagrams, full technology stack inventory, visual folder tree, sequence flows (Registration, RBAC Login, Certificate Minting, Matrimonial Proposals), and complete database schema dictionaries.
- **`Architecture/DESIGN.md`**: UI/UX Design System detailing Islamic cultural design principles, color tokens (Forest Green, Emerald, Warm Gold, Slate, Light Gray), typography scales, form control standards, avatar styles, and responsive breakpoints.
- **`Architecture/RULES.md`**: Core Engineering Guidelines & Standards (zero regressions, defensive SQL with `mysqli_real_escape_string` / `intval`, XSS prevention via `htmlspecialchars`, non-destructive auto-migrations, and Super Admin immutable safety rules).
- **`Architecture/TASKS.md`**: Project Task Breakdown & Sprint Tracker covering 44 tracked tasks across 8 development phases with real-time status dashboards (89% complete).
- **`Architecture/TEST_PLAN.md`**: Quality Assurance Plan with detailed test suites: `TC-AUTH`, `TC-REG`, `TC-CERT`, `TC-RBAC`, `TC-MATRI`, `TC-RESP`.
- **`Architecture/SECURITY.md`**: Security Architecture, Threat Modeling, Session Management, Upload Hardening, and Bcrypt Cryptographic Standards.
- **`Architecture/DECISIONS.md`**: 9 Architecture Decision Records (ADRs 001 through 009) capturing key architectural rationales.
- **`Architecture/MEMORY.md`**: Project Context & Living Memory Reference for developers and AI agents.

### 🛡️ Role-Based Access Control (RBAC) Admin Portal & Dynamic Login
- **Removed Hardcoded Credentials**: Eliminated legacy hardcoded `Admin/Admin` credentials from `admin/login.php`.
- **Dynamic Member-Credential Authentication**: Admins now authenticate using their registered community member credentials (Email or Phone + Password) against `admin_users` JOIN `user_registrtion`.
- **Dedicated `admin_users` Table**: Added runtime auto-migration in `db.php` creating `admin_users` with unique foreign key `user_id`, `role` (`super_admin`, `admin`), `status` (`active`, `inactive`), and audit timestamps.
- **Primary Super Admin Seeding & Safety Lock**: Initialized `ahmad.nadim144@gmail.com` (Member ID 3) as immutable primary Super Admin. Added code-level safety locks in `admin/admins.php` and `admin/auth.php` preventing demotion, deletion, or suspension.
- **Dedicated Super Admin Console (`admin/admins.php`)**: Exclusive management portal for Super Admin to appoint registered members to Admin, activate/suspend accounts, and revoke administrative powers.
- **Member Directory Integration (`admin/members.php`)**: Added direct `[ ⭐️ Make Admin ]` and `[ 🚫 Remove Admin ]` action buttons in table rows with dynamic role badges.
- **RBAC Authentication Helpers (`admin/auth.php`)**: Implemented `is_super_admin()`, `is_admin()`, `require_super_admin()`, and `check_admin_auth()`.

### 💍 Kalal Eraquee Community Matrimonial System
- **Database Schema Auto-Migrations (`db.php`)**: Non-destructive, self-healing creation of 4 relational tables inside `codecxss_anjuman`:
  1. `matrimonial_profiles`: Candidate attributes, family background, partner expectations, contact person details, relationship to managing member, and privacy flags.
  2. `matrimonial_photos`: Photo gallery with primary photo flag.
  3. `matrimonial_interests`: Bilateral proposal exchange system (`pending`, `accepted`, `declined`).
  4. `matrimonial_access_requests`: Tier 3 guardian contact release requests awaiting Admin adjudication.
  - Automatically verifies and provisions `uploads/matrimonial/` with `0755` permissions.
- **Member-Gated Multi-Profile Intake Wizard (`matrimonial-create.php`)**:
  - Gated to registered members; allows single user account to manage multiple candidates for `Self`, `Son`, `Daughter`, `Brother`, `Sister`, `Relative`.
  - Comprehensive marital statuses: `Unmarried` (Never Married), `Divorced` (Talaq Shuda), `Khula Shuda`, and `Widowed` (Bewa).
  - Real-time JavaScript DOB-to-Age calculation with server-side validation.
  - Sequential candidate code generation (`ERQ-G-0001` for Grooms, `ERQ-B-0002` for Brides).
  - Image upload with instant client-side preview and strict privacy mode toggle.
- **Member Matrimonial Hub (`matrimonial-manage.php`)**:
  - 4-tab interactive control center:
    1. *My Candidate Profiles*: Manage active/married/paused statuses, view biodata card, edit biodata.
    2. *Received Interests*: Real-time proposal notifications with one-click **Accept** or **Decline**.
    3. *Sent Interests*: Live status tracking of sent proposals.
    4. *Unlocked Contacts*: Directory of candidates whose guardian contacts have been approved by Admin.
- **Privacy-First Search Directory (`matrimonial.php`)**:
  - Interactive filter bar: Dulha / Dulhan, Marital Status, Caste subdivisions, and City/State.
  - **Enforced 3-Tier Privacy Model**:
    - *Tier 1 (Public / Unrelated)*: Candidate photo is strictly blurred/locked; name and direct contact are hidden.
    - *Tier 2 (Reciprocal Candidate Match)*: Members with active candidate profiles of opposite gender unlock 1 clear photo, first name, and detailed biodata.
    - *Tier 3 (Admin Approved)*: Direct guardian phone, WhatsApp, and full home address remain hidden until vetted and released by an Admin.
  - Integrated "❤️ Show Interest" proposal modal.
- **Candidate Biodata Dossier (`matrimonial-profile-view.php`)**:
  - Displays personal, religious, education, profession, family background, and partner preferences.
  - Privacy-aware contact box: Unlocked green box with direct contact info for Tier 3, status indicator for pending requests, or modal request trigger for unapproved users.
- **Navigation Updates**:
  - Added Matrimonial Spotlight banner, profile counters, and proposal alerts to Member Dashboard (`user-dashboard.php`).
  - Added Matrimonial dropdown links (Dulha, Dulhan, Create Biodata) to public header (`index.html`).

### ✏️ Candidate Profile Edit & Admin Approval Workflow
- **Dedicated Edit Wizard (`matrimonial-edit.php`)**:
  - Access-controlled to profile creator (`created_by_user_id == $_SESSION['user_id']`) or active Administrators.
  - Pre-populates all existing biodata fields across Core Info, Education, Career, Family, Location, Guardian Contacts, and Partner Preferences.
  - Allows replacing candidate photograph with instant preview, preserving existing image if unreplaced.
  - When submitted by regular members: Updates record, sets `status = 'pending_approval'`, and records `updated_at = NOW()`.
- **Public Directory Holdback**:
  - Modified profiles are automatically excluded from public directory searches (`matrimonial.php`) while `status = 'pending_approval'`, ensuring unverified modifications or spam are held back.
- **Member Awareness & Status Banners**:
  - Displayed amber `⏳ Status: Under Admin Review (Pending Approval)` notice banner on `matrimonial-profile-view.php`.
  - Added `⏳ Under Review` status pills and `[ ✏️ Edit Biodata ]` action buttons on `matrimonial-manage.php`.
- **Inline Admin Adjudication Bar**:
  - When an Admin or Super Admin views a pending profile on `matrimonial-profile-view.php?id=3`, an inline Admin Action Bar provides immediate 1-click `[ ✅ Approve & Publish ]` and `[ ❌ Reject / Pause ]` controls.
- **Admin Moderation Console Upgrade (`admin/matrimonial.php`)**:
  - Upgraded to 3 dedicated tabs:
    1. *⏳ Profile Approvals (Count)*: Active by default when profiles await review. Shows candidate details, registered member info, last modified timestamp, view/edit buttons, and 1-click **Approve** and **Reject** actions.
    2. *📩 Contact Access Requests*: Review family contact release requests.
    3. *👥 All Candidate Profiles*: Full registry with status badges, view, edit, and delete options.
  - Added 4-card statistics grid highlighting pending profile approvals in real time.

### 🧪 Quality Assurance & Automated Verification
- **Matrimonial Core Test (`scratch/test_matrimonial.php`)**:
  - Verified creation of Groom (`ERQ-G-0001`) and Bride (`ERQ-B-0002`) profiles.
  - Verified bilateral interest proposal exchange and acceptance (mutual interest established).
  - Verified submission of contact access request and Admin approval.
- **Matrimonial Edit & Approval Test (`scratch/test_matrimonial_edit.php`)**:
  - Verified profile field modifications and status transition to `pending_approval`.
  - Verified exclusion from public directory queries while pending.
  - Verified appearance in Admin's approval queue.
  - Verified 1-click Admin approval restores `active` status and immediately publishes to directory.
- **PHP Syntax Linting**: All 12 PHP files passed `php -l` with 0 syntax errors.

---

## 📋 Recent Updates & Pending / Next Steps

- [x] Set up **MySQL database** for Registration/Membership feature (`codecxss_anjuman` / `user_registrtion` table created & verified).
- [x] Resolve database connection issues across all PHP endpoints (`db.php` implemented).
- [x] Implement & test **User Login** & OTP verification flow.
- [x] Upgrade **Registration form** with FullName, DOB, auto-age, and secure password creation.
- [x] Implement **Password Authentication (Mobile/Email + Password)** with Forgot/Temp password reset flow.
- [x] Implement **Admin Member Management (CRUD, Edit, Delete, View Details, Issue Temp Password)**.
- [x] Implement **Downloadable PDF Membership Certificate** with welcoming message.
- [x] Standardize **Mobile Hamburger Menu** consistently across all site pages without redundant Membership submenus.
- [x] Align **User Login** appearance with Admin Login (proper margin-top, flex centering, and logo integration).
- [x] Implement **Join Membership Sign-Up Modal Dialog** (Desktop & Mobile) with eye toggle icon.
- [x] Implement **Progressive Multi-Part Registration** (Personal -> Address -> Education/Profession) with Save & Next.
- [x] Implement **Profile Editing** from Member Dashboard.
- [x] Reorder Personal Details: Gender and Marital Status before Aadhaar Card Number, remove Native Place.
- [x] Introduce and validate **Additional Mobile Number** field.
- [x] Introduce **Cast dropdown control** in Personal Details before Aadhaar Card Number with options: `Kalal`, `Kalwar`, `Kalar`, `Eraquee(Iraqi)`, `Kalal Lari`, `Kalal Choudhary`, `Araqi`, `Ranki`, saved to database and rendered in Dashboard, Certificate, and Admin portal.
- [x] Fix profile picture upload preview by removing broken inline SVG attribute text and adding dedicated **Dummy Human Avatar** vector graphic (`images/dummy-avatar.svg`) in Registration & User Dashboard.
- [x] Improve **Member Dashboard Mobile Header & Buttons**: Prevented "Member Dashboard" from wrapping awkwardly on mobile, unified Logout button design with Home button using clean inline-flex pills and responsive typography.
- [x] Render **Circular Profile Picture** on the left-hand side of Certificate before "ANJUMAN ERAQUEE INDIA".
- [x] Enable **Persistent Server & Database Storage** for generated membership certificates.
- [x] Implement **Database-Backed Role-Based Access Control (RBAC)** in Admin Portal:
  - Created dedicated `admin_users` table with auto-migration in `db.php`.
  - Replaced hardcoded credentials in `admin/login.php` with dynamic credentials backed by `admin_users` JOIN `user_registrtion` (members log in using registered email/phone + password).
  - Defined roles: `super_admin` (exclusive rights to promote, demote, and deactivate admins) and `admin` (management of community members and site content).
  - Seeded primary Super Admin (`ahmad.nadim144@gmail.com`, Member ID 3).
  - Built dedicated Super Admin Console (`admin/admins.php`) with safety locks preventing deletion/demotion of primary Super Admin.
  - Added direct `[ ⭐️ Make Admin ]` and `[ 🚫 Remove Admin ]` actions inside Member Directory (`admin/members.php`).
- [x] Establish Comprehensive **Architecture Documentation Suite** (`Architecture/`):
  - Created 9 industry-standard architectural documents inspired by modern AI-assisted engineering frameworks:
    - `PRD.md` — Product Requirements Document (Goals, Personas, Features, KPIs, Roadmap)
    - `ARCHITECTURE.md` — System Architecture (Tech Stack, Folder Tree, Data Flows, DB Schemas)
    - `DESIGN.md` — Design System (Tokens, Color Palette, Typography, Components, Responsive Breakpoints)
    - `RULES.md` — Engineering Guidelines (Coding Standards, Defensive SQL, XSS Prevention, Git Rules)
    - `TASKS.md` — Task Breakdown & Development Plan (42 Tracked Tasks across 8 Phases)
    - `TEST_PLAN.md` — QA & Testing Strategy (Test Cases for Auth, Reg, Certs, RBAC, Matrimonial, Usability)
    - `SECURITY.md` — Threat Modeling, RBAC Enforcement & Hardening Guide
    - `DECISIONS.md` — Architecture Decision Records (ADRs 001 through 009)
    - `MEMORY.md` — Living Context, Quick Reference & AI Assistant Guide
- [x] Implement **Kalal Eraquee Community Matrimonial System**:
  - Auto-created 4 normalized relational tables in `db.php`: `matrimonial_profiles`, `matrimonial_photos`, `matrimonial_interests`, `matrimonial_access_requests`.
  - Built Candidate Profile Creation Wizard (`matrimonial-create.php`) supporting Self, Son, Daughter, Brother, Sister, Relative; marital statuses: Unmarried, Divorced (Talaq Shuda), Khula Shuda, and Widowed (Bewa).
  - Implemented 3-Tier Privacy Architecture: Tier 1 (public/unrelated viewers get blurred/locked photos & basic bio), Tier 2 (reciprocal opposite-gender candidates unlock 1 photo & detailed bio), Tier 3 (Admin approval unlocks guardian phone, WhatsApp, and address).
  - Built Bilateral Proposal Flow: Members can "Show Interest" from Candidate X to Candidate Y; receiving family can Accept or Decline in `matrimonial-manage.php`.
  - Built Public Matrimonial Directory (`matrimonial.php`) with filters (Dulha/Dulhan, Marital Status, Caste, City) and candidate detail view (`matrimonial-profile-view.php`).
  - Added Matrimonial Moderation Console (`admin/matrimonial.php`) for 1-click Contact Access Request approvals and profile oversight.
  - Linked matrimonial navigation across Member Dashboard (`user-dashboard.php`), Public Header (`index.html`), and Admin Sidebars.
- [x] Implement **Matrimonial Candidate Profile Edit & Admin Approval Workflow**:
  - Built Candidate Profile Edit Wizard (`matrimonial-edit.php`) allowing profile owners (or Admins) to update biodata, correct errors, and replace photographs with instant preview.
  - Enforced Moderation Lifecycle: When a member submits corrections, profile transitions to `status = 'pending_approval'`, holding it back from public directory searches until verified.
  - Added "Under Admin Review" status alert and inline 1-click Admin adjudication controls on `matrimonial-profile-view.php`.
  - Added `[ ✏️ Edit Biodata ]` action button and pending approval status badges in Member Matrimonial Hub (`matrimonial-manage.php`).
  - Added dedicated **"⏳ Profile Approvals"** tab in `admin/matrimonial.php` with 1-click `[ ✅ Approve ]` and `[ ❌ Reject ]` actions.
  - Verified with end-to-end integration test (`scratch/test_matrimonial_edit.php`).
- [ ] Connect real SMS Gateway API (Fast2SMS / Twilio) using API Key for real-time mobile SMS delivery.
- [ ] Upload updated files to **InfinityFree** hosting via FileZilla.


