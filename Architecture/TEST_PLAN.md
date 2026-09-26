# Quality Assurance & Testing Plan

## Anjuman Eraquee INDIA — Test Strategy, Test Cases & QA Protocol

| Metadata | Details |
| :--- | :--- |
| **Document Version** | 1.0.0 |
| **Date** | September 20, 2026 |
| **Status** | Active / QA Standard |
| **Scope** | End-to-End Functional, RBAC, Certificate Rendering, Responsive & Security Testing |
| **Primary Test Environments** | Local (XAMPP PHP 8.2 / Apache Port 8080) & Production (InfinityFree / cPanel) |

---

## 1. Quality Objectives

1. **Functional Correctness**: Ensure 100% of user and admin flows (registration, login, profile editing, certificate generation, admin actions) operate without error.
2. **Access Control Integrity**: Confirm that standard administrators can never access or execute Super Admin functions.
3. **Data Protection**: Guarantee that all passwords remain securely hashed, inputs are sanitized against SQL injection, and outputs are protected from Cross-Site Scripting (XSS).
4. **Cross-Device Usability**: Verify seamless mobile simulator and handheld performance across viewports from 320px to 1440px.

---

## 2. Test Environments

| Parameter | Local Development | Remote Production (Hosting) |
| :--- | :--- | :--- |
| **URL** | `http://localhost:8080/anjuman_eraquee/` | `https://anjumaneraquee.org` (or temporary domain) |
| **PHP Version** | PHP 8.2 (Windows CLI & Apache) | PHP 7.4 / 8.x (Linux Apache) |
| **Database** | MySQL (Port 3307 / 3306) | Shared MariaDB / MySQL Host |
| **File Storage** | Local Windows File System | Linux Remote Web Root (`htdocs/`) |

---

## 3. Detailed Test Suites & Test Cases

### Test Suite 1: Member Authentication & Self-Service (`TC-AUTH`)

| Case ID | Test Scenario | Steps | Expected Result | Pass/Fail |
| :---: | :--- | :--- | :--- | :---: |
| **TC-AUTH-01** | Login with valid Email + Password | 1. Navigate to `user-login.php`<br>2. Enter registered email & password<br>3. Submit form | Authenticated, session initialized, redirected to `user-dashboard.php`. | PASS |
| **TC-AUTH-02** | Login with valid Mobile + Password | 1. Navigate to `user-login.php`<br>2. Enter registered 10-digit phone & password<br>3. Submit | Authenticated, redirected to `user-dashboard.php`. | PASS |
| **TC-AUTH-03** | Login with invalid credentials | 1. Enter valid email with incorrect password | Form displays red error alert: "Invalid credentials". Session not started. | PASS |
| **TC-AUTH-04** | Password Reset Request | 1. Click "Forgot Password"<br>2. Enter registered phone number<br>3. Submit request | DB updates `reset_requested = 1`. Success message indicates admin has been notified. | PASS |
| **TC-AUTH-05** | Forced password change for Temp Password | 1. Log in with an admin-issued temporary password | System flags `is_temp_password = 1` and redirects to mandatory password update screen. | PASS |
| **TC-AUTH-06** | Member Logout | 1. Click "Logout" from dashboard | Session destroyed, cookies cleared, redirected to login page. | PASS |

---

### Test Suite 2: Multi-Step Member Registration (`TC-REG`)

| Case ID | Test Scenario | Steps | Expected Result | Pass/Fail |
| :---: | :--- | :--- | :--- | :---: |
| **TC-REG-01** | Step 1 DOB to Age calculation | 1. Open `registration.php`<br>2. Pick a DOB 25 years ago | Age input automatically populates with "25" instantly via JavaScript. | PASS |
| **TC-REG-02** | Cast selection dropdown | 1. Inspect Community Cast selector | Displays all 8 community options: `Kalal`, `Kalwar`, `Kalar`, `Eraquee(Iraqi)`, etc. | PASS |
| **TC-REG-03** | Profile photo upload preview | 1. Choose a valid JPG/PNG file | Client-side circular preview immediately displays selected photo. | PASS |
| **TC-REG-04** | Aadhaar and Mobile validation | 1. Enter 12-digit Aadhaar & 10-digit mobile | Client and server enforce numeric format requirements. | PASS |
| **TC-REG-05** | Wizard Step Navigation | 1. Fill Step 1, click "Save & Next"<br>2. Fill Step 2, click "Save & Next"<br>3. Submit Step 3 | Record inserted into `user_registrtion`, photo stored in `uploads/profile_pictures/`. | PASS |

---

### Test Suite 3: PDF Membership Certificate Engine (`TC-CERT`)

| Case ID | Test Scenario | Steps | Expected Result | Pass/Fail |
| :---: | :--- | :--- | :--- | :---: |
| **TC-CERT-01** | Unauthenticated certificate access | 1. Attempt direct access to `download-certificate.php` without active session | Denied; user redirected to `user-login.php`. | PASS |
| **TC-CERT-02** | Initial Certificate Minting | 1. Log in as member<br>2. Click "Download Certificate" | FPDF generates PDF, draws borders, embeds circular photo, sets headers, streams to browser. | PASS |
| **TC-CERT-03** | Server-Side Disk Caching | 1. Verify `uploads/certificates/`<br>2. Check DB column `certificate_path` | File `cert_{id}.pdf` exists on disk; subsequent downloads serve cached file without re-rendering. | PASS |
| **TC-CERT-04** | Fallback for missing member photo | 1. Generate certificate for member without uploaded photo | FPDF gracefully falls back to vector silhouette without fatal error. | PASS |

---

### Test Suite 4: Role-Based Access Control (RBAC) Admin Portal (`TC-RBAC`)

| Case ID | Test Scenario | Steps | Expected Result | Pass/Fail |
| :---: | :--- | :--- | :--- | :---: |
| **TC-RBAC-01** | Super Admin Login | 1. Login with `ahmad.nadim144@gmail.com` + member password at `admin/login.php` | Access granted; Topbar displays `👑 Super Admin: Md Nadim Ahmad`; sidebar shows `🛡️ Manage Admins`. | PASS |
| **TC-RBAC-02** | Standard Admin Login | 1. Login with regular promoted admin account | Access granted; Topbar displays `🛡️ Admin`; `🛡️ Manage Admins` hidden from sidebar. | PASS |
| **TC-RBAC-03** | Direct URL access to `admin/admins.php` by regular Admin | 1. Log in as standard admin<br>2. Manually enter URL `admin/admins.php` | `require_super_admin()` intercepts, redirects to `index.php?error=unauthorized` with red alert. | PASS |
| **TC-RBAC-04** | Promote Registered Member to Admin | 1. As Super Admin, select a member in `admin/admins.php` or click `⭐️ Make Admin` in `admin/members.php` | Member added to `admin_users` table with `role = 'admin'`, `status = 'active'`. | PASS |
| **TC-RBAC-05** | Revoke Administrator Access | 1. As Super Admin, click `🚫 Remove Admin` on standard admin | Admin record deleted from `admin_users`; regular member account remains intact. | PASS |
| **TC-RBAC-06** | Primary Super Admin Safety Lock | 1. Attempt to delete or revoke `ahmad.nadim144@gmail.com` | Action blocked by server; returns "Security Protection: Cannot delete or demote Super Admin". | PASS |

---

### Test Suite 5: Responsive & Cross-Browser Usability (`TC-RESP`)

| Case ID | Test Scenario | Steps | Expected Result | Pass/Fail |
| :---: | :--- | :--- | :--- | :---: |
| **TC-RESP-01** | Mobile Viewport (360px - 412px) | 1. Open Chrome DevTools in iPhone/Android simulator<br>2. Inspect Member Dashboard header | Action buttons ("Home", "Member Dashboard", "Logout") align cleanly in pill layout without text wrap. | PASS |
| **TC-RESP-03** | Mobile Hamburger Menu | 1. Open mobile menu on public site pages | Menu expands smoothly, links are tap-friendly, no duplicate membership submenus. | PASS |
| **TC-RESP-04** | Admin Members Table Scroll | 1. View `admin/members.php` on small tablet | Table wrapper enables horizontal scroll without breaking sidebar layout. | PASS |

---

## 4. Automated Verification Command Script

The following automated command checks all PHP endpoints for syntax cleanliness before committing or deploying:

```powershell
C:\xampp\php\php.exe -l db.php
C:\xampp\php\php.exe -l admin/auth.php
C:\xampp\php\php.exe -l admin/login.php
C:\xampp\php\php.exe -l admin/admins.php
C:\xampp\php\php.exe -l admin/members.php
C:\xampp\php\php.exe -l admin/index.php
C:\xampp\php\php.exe -l admin/settings.php
```

