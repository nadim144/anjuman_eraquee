# System Architecture Document

## Anjuman Eraquee INDIA — Engineering Architecture & System Design

| Metadata | Details |
| :--- | :--- |
| **Document Version** | 1.0.0 |
| **Date** | September 20, 2026 |
| **Status** | Active / Production Blueprint |
| **Project Repository** | `nadim144/anjuman_eraquee` |
| **Runtime Environment** | Apache / PHP 7.4+ / MySQL 5.7+ / MariaDB |

---

## 1. High-Level Architecture

The platform follows a **Modular Layered Architecture** combining a responsive presentation layer, a decoupled JSON-driven configuration store, a procedural service backend, and an ACID-compliant relational MySQL database.

```mermaid
flowchart TD
    subgraph Client["Client Tier (Browsers & Mobile Devices)"]
        Guest["Public Visitor"]
        Member["Registered Member"]
        Admin["Administrator / Super Admin"]
    end

    subgraph WebServer["Web Server Tier (Apache / Nginx)"]
        Router["HTTP Router & Static File Handler (.htaccess)"]
        StaticAssets["Static HTML Pages (17 Pages), CSS, JS, Images"]
    end

    subgraph AppLayer["Application Tier (PHP 7.4+)"]
        AuthModule["Auth & Session Guard (admin/auth.php, user-login.php)"]
        RegModule["Registration Wizard (registration.php, registerdata.php)"]
        CertEngine["PDF Generation Engine (fpdf/, download-certificate.php)"]
        AdminModule["RBAC Admin Console (admin/index.php, admins.php, members.php, matrimonial.php)"]
        MatrimonialModule["Matrimonial System (matrimonial.php, matrimonial-create.php, matrimonial-manage.php, matrimonial-profile-view.php)"]
        APILayer["Settings API (api/settings.php)"]
    end

    subgraph DataTier["Data & Storage Tier"]
        DB[(MySQL Database: user_registrtion, admin_users, matrimonial_profiles, matrimonial_photos, matrimonial_interests, matrimonial_access_requests)]
        JSONStore[("File Store: data/settings.json")]
        FileStorage[("Media Storage: uploads/profile_pictures, uploads/certificates, uploads/matrimonial")]
    end

    Guest -->|Browse Pages & Directory| Router
    Member -->|Login / Dashboard / Certificate / Matrimonial| Router
    Admin -->|Manage Members / Settings / Matrimonial Moderation| Router

    Router --> StaticAssets
    Router --> AppLayer

    StaticAssets -.->|Fetch Settings AJAX| APILayer
    APILayer --> JSONStore

    RegModule --> DB
    RegModule --> FileStorage
    AuthModule --> DB
    CertEngine --> DB
    CertEngine --> FileStorage
    AdminModule --> DB
    AdminModule --> JSONStore
    MatrimonialModule --> DB
    MatrimonialModule --> FileStorage
```

---

## 2. Technology Stack

| Layer | Technology | Version | Purpose |
| :--- | :--- | :--- | :--- |
| **Frontend UI** | HTML5 / CSS3 / SCSS | Latest standards | Semantic markup and custom modern styles |
| **CSS Framework** | Bootstrap (Mosque Theme) | 3.3.7 / 4.x compatible | Responsive 12-column grid and mobile UI |
| **Client Scripting** | JavaScript (Vanilla) + jQuery | ES6+ / jQuery 3.x | DOM manipulation, multi-step validation, live preview |
| **Iconography** | FontAwesome | 4.7.0 / 5.x | High-contrast visual icons across UI |
| **Backend Engine** | PHP | 7.4 - 8.2 | Application routing, sessions, data processing |
| **Database Engine** | MySQL / MariaDB | 5.7+ / 10.3+ | Persistent relational storage for members & admins |
| **PDF Generation** | FPDF | 1.8.x | Dynamic vector PDF rendering for membership certificates |
| **Configuration** | JSON | RFC 8259 | File-based fast configuration (`data/settings.json`) |
| **Development Web Server** | Apache via XAMPP | 2.4.x | Local development environment on port `8080` |
| **Production Hosting** | Apache Shared Host | Linux / cPanel | Remote production hosting (InfinityFree / Live Host) |

---

## 3. Directory & Folder Structure

```
c:\xampp\htdocs\anjuman_eraquee/
├── Architecture/                   # Architectural & Engineering Documentation Suite
│   ├── PRD.md                      # Product Requirements Document
│   ├── ARCHITECTURE.md             # System Architecture & Tech Specs (This Document)
│   ├── DESIGN.md                   # UI/UX Design System & Theme Specs
│   ├── RULES.md                    # Engineering Rules & Coding Standards
│   ├── TASKS.md                    # Project Tasks Breakdown & Development Roadmap
│   ├── TEST_PLAN.md                # Quality Assurance & Testing Plan
│   ├── SECURITY.md                 # Security Architecture & Hardening Guide
│   ├── DECISIONS.md                # Architecture Decision Records (ADRs)
│   └── MEMORY.md                   # Project Context & AI Agent Reference
├── admin/                          # Administrative Portal (RBAC Protected)
│   ├── admins.php                  # Super Admin Exclusive Management Console
│   ├── auth.php                    # Admin Authentication & RBAC Guard
│   ├── index.php                   # Administrative Dashboard & Overview Stats
│   ├── login.php                   # Dynamic Member-Credential Admin Login
│   ├── logout.php                  # Admin Session Termination
│   ├── matrimonial.php            # Matrimonial Moderation Console & Contact Request Review
│   ├── members.php                 # Registered Member Directory, CRUD, CSV Export
│   ├── settings.php                # Site Content & Contact Phone Editor
│   └── css/                        # Dedicated Admin Panel Stylesheet
│       └── admin.css
├── api/                            # Internal REST Endpoints
│   └── settings.php                # Serves data/settings.json as JSON to Frontend
├── css/                            # Public Site Stylesheets
├── data/                           # Data Store (JSON Configuration)
│   └── settings.json               # Dynamic Phone Numbers, Emails, Video URLs
├── fpdf/                           # FPDF PDF Generation Engine
│   └── fpdf.php
├── images/                         # Static Assets, Logos, and Dummy Avatars
│   ├── dummy-avatar.svg            # Fallback Vector Profile Silhouette
│   └── icon/tabicon.jpeg           # Favicon Icon
├── js/                             # Frontend JavaScript
│   ├── custom.js                   # Navigation, Hamburger, Slider Controls
│   └── site-settings.js            # Dynamic Topbar/Footer Phone Number Injector
├── uploads/                        # Dynamic User Media & Artifact Storage
│   ├── certificates/               # Cached Persistent PDF Membership Certificates
│   ├── matrimonial/                # Matrimonial Candidate Photographs
│   └── profile_pictures/           # Member Profile Photos
├── db.php                          # Central DB Connection & Non-Destructive Auto-Migrator
├── db_config.php                   # Optional Local/Remote DB Overrides (Not Committed)
├── download-certificate.php        # Dynamic PDF Certificate Generator Controller
├── index.html                      # Homepage (17 Total HTML Marketing Pages)
├── matrimonial.php                 # Matrimonial Directory & Search Portal (Tier 1 & 2 Enforced)
├── matrimonial-create.php          # Candidate Profile Creation Wizard (Self / Family)
├── matrimonial-manage.php          # Member Matrimonial Hub (Profiles, Proposals, Unlocked Contacts)
├── matrimonial-profile-view.php    # Candidate Biodata View (Tier 1/2/3 Reveal & Modals)
├── registration.php                # Multi-Step Member Registration Wizard
├── registerdata.php                # Legacy/Direct Member Registration Processor
├── user-dashboard.php              # Member Self-Service Dashboard & Profile Editor
├── user-login.php                  # Member Authentication (Mobile/Email + Password)
├── user-logout.php                 # Member Session Termination
├── user-reset-password.php         # Password Reset & Update Processor
├── user-verify-otp.php             # OTP / Verification Flow
├── work.md                         # Historical Development Work Log
└── style.css                       # Primary Application Master Stylesheet
```

---

## 4. Key Data Flows

### A. Member Registration & Profile Activation Flow
```mermaid
sequenceDiagram
    autonumber
    actor Member as New Member
    participant Form as registration.php (Wizard)
    participant Server as PHP Backend
    participant Storage as uploads/profile_pictures/
    participant DB as MySQL (user_registrtion)

    Member->>Form: Completes Step 1 (Personal & Photo)
    Member->>Form: Completes Step 2 (Address & Contact)
    Member->>Form: Completes Step 3 (Education & Password)
    Form->>Server: POST Form Data & Multipart Image
    Server->>Server: Validate Inputs & Hash Password (BCRYPT)
    Server->>Storage: Store Sanitized Image File
    Server->>DB: INSERT into user_registrtion
    DB-->>Server: Return Insert ID (Member ID)
    Server-->>Member: Set Session & Redirect to user-dashboard.php
```

### B. Dynamic Member-Credential Admin Login & RBAC Flow
```mermaid
sequenceDiagram
    autonumber
    actor Admin as Admin / Super Admin
    participant Login as admin/login.php
    participant DB as MySQL (admin_users + user_registrtion)
    participant Console as admin/admins.php

    Admin->>Login: Enter Mobile/Email + Password
    Login->>DB: JOIN admin_users & user_registrtion ON user_id
    DB-->>Login: Return User Record & Admin Role
    alt Password Valid & admin_users.status = 'active'
        Login->>Login: Initialize Session (role: super_admin / admin)
        Login->>DB: UPDATE last_login = NOW()
        Login-->>Admin: Redirect to admin/index.php
    else Invalid Credentials or Inactive
        Login-->>Admin: Show Error: "Invalid credentials or account suspended"
    end

    opt Super Admin Accesses Console
        Admin->>Console: Request admin/admins.php
        Console->>Console: require_super_admin()
        alt Role is super_admin
            Console-->>Admin: Render Admin Management Console
        else Role is standard admin
            Console-->>Admin: Redirect index.php?error=unauthorized
        end
    end
```

### C. Membership Certificate Generation & Caching Flow
```mermaid
sequenceDiagram
    autonumber
    actor Member as Logged-In Member
    participant Controller as download-certificate.php
    participant Storage as uploads/certificates/
    participant DB as MySQL (user_registrtion)

    Member->>Controller: Click "Download Certificate"
    Controller->>Storage: Check if certificate_path exists on disk
    alt Cached File Exists
        Controller-->>Member: Stream Cached PDF File (Fast Output)
    else File Missing or First Run
        Controller->>DB: Fetch Member Name, ID, DOB, Cast, Photo
        Controller->>Controller: Instantiate FPDF, Draw Borders, Stamps, Circular Photo
        Controller->>Storage: Save Generated PDF to uploads/certificates/
        Controller->>DB: UPDATE certificate_path & certificate_generated_at
        Controller-->>Member: Stream Newly Minted PDF File
    end
```

---

## 5. Database Architecture & Schema

The database consists of two core tables designed with relational integrity:

### 1. Table: `user_registrtion` (Member Records)
| Column | Type | Nullable | Default | Description |
| :--- | :--- | :---: | :---: | :--- |
| `id` | `INT(11)` | NO | `AUTO_INCREMENT` | Primary Key, unique Member Registration Number |
| `username` | `VARCHAR(255)` | YES | `NULL` | Member's Full Name |
| `fathername` | `VARCHAR(255)` | YES | `NULL` | Father's Full Name |
| `mothername` | `VARCHAR(255)` | YES | `NULL` | Mother's Full Name |
| `grandfathername` | `VARCHAR(255)` | YES | `NULL` | Grandfather's Full Name |
| `nativeplace` | `VARCHAR(255)` | YES | `NULL` | Ancestral village / Native town |
| `age` | `VARCHAR(50)` | YES | `NULL` | Computed Age in years |
| `dob` | `DATE` | YES | `NULL` | Date of Birth |
| `gender` | `VARCHAR(50)` | YES | `NULL` | Gender (`Male`, `Female`, `Other`) |
| `maritalstatus` | `VARCHAR(50)` | YES | `NULL` | Marital Status (`Single`, `Married`, etc.) |
| `cast` | `VARCHAR(100)` | YES | `NULL` | Community Sub-caste / Category |
| `aadhaar_number`| `VARCHAR(20)` | YES | `NULL` | 12-digit Indian Aadhaar Card Number |
| `profile_picture`| `VARCHAR(255)`| YES | `NULL` | Relative path to profile photograph |
| `phonenumber` | `VARCHAR(100)` | YES | `NULL` | Primary 10-digit mobile number |
| `additional_mobile`| `VARCHAR(20)`| YES | `NULL` | Alternate / Secondary mobile number |
| `whatsappnumber` | `VARCHAR(100)`| YES | `NULL` | WhatsApp contact number |
| `email` | `VARCHAR(255)` | YES | `NULL` | Email address (Unique contact identifier) |
| `presentaddress` | `TEXT` | YES | `NULL` | Present residential address |
| `presentdistrict`| `VARCHAR(255)`| YES | `NULL` | Current District |
| `presentstate` | `VARCHAR(255)` | YES | `NULL` | Current State |
| `presentpincode` | `VARCHAR(50)` | YES | `NULL` | PIN Code |
| `permanentaddress`| `TEXT` | YES | `NULL` | Permanent address |
| `qulification` | `VARCHAR(255)` | YES | `NULL` | Educational Qualification |
| `occupation` | `VARCHAR(255)` | YES | `NULL` | Profession / Employment |
| `password` | `VARCHAR(255)` | YES | `NULL` | Bcrypt-hashed password |
| `is_temp_password`| `TINYINT(1)` | YES | `0` | Flag: 1 if user must reset password on login |
| `reset_requested`| `TINYINT(1)` | YES | `0` | Flag: 1 if user requested admin password assistance |
| `certificate_path`| `VARCHAR(255)`| YES | `NULL` | Cached path to generated PDF certificate |
| `certificate_generated_at`| `DATETIME`| YES | `NULL` | Timestamp of certificate generation |
| `created_at` | `TIMESTAMP` | NO | `CURRENT_TIMESTAMP` | Member registration date |

---

### 2. Table: `admin_users` (Role-Based Access Control)
| Column | Type | Nullable | Default | Description |
| :--- | :--- | :---: | :---: | :--- |
| `id` | `INT(11)` | NO | `AUTO_INCREMENT` | Primary Key, Admin record ID |
| `user_id` | `INT(11)` | NO | `None` | **UNIQUE** Foreign Key linking to `user_registrtion(id)` |
| `role` | `ENUM('super_admin','admin')` | NO | `'admin'` | RBAC Role designation |
| `status` | `ENUM('active','inactive')` | NO | `'active'` | Account status (Active or Suspended) |
| `created_by` | `INT(11)` | YES | `NULL` | Admin ID of the promoter who granted admin rights |
| `created_at` | `TIMESTAMP` | NO | `CURRENT_TIMESTAMP` | Timestamp when promoted to admin |
| `last_login` | `DATETIME` | YES | `NULL` | Timestamp of last successful administrative login |

---

### 3. Table: `matrimonial_profiles` (Candidate Biodatas)
| Column | Type | Nullable | Default | Description |
| :--- | :--- | :---: | :---: | :--- |
| `id` | `INT(11)` | NO | `AUTO_INCREMENT` | Primary Key, Profile ID |
| `profile_code` | `VARCHAR(50)` | YES | `NULL` | Unique code (e.g., `ERQ-G-0001`, `ERQ-B-0002`) |
| `created_by_user_id` | `INT(11)` | NO | `None` | Foreign Key to `user_registrtion(id)` (Managing Member) |
| `profile_for` | `ENUM(...)` | NO | `'self'` | Relationship (`self`, `son`, `daughter`, `brother`, `sister`, `relative`) |
| `gender` | `ENUM('male','female')` | NO | `None` | Candidate gender |
| `full_name` | `VARCHAR(255)` | NO | `None` | Candidate full legal name |
| `dob` | `DATE` | YES | `NULL` | Date of Birth |
| `age` | `INT(11)` | YES | `NULL` | Candidate age in years |
| `height` | `VARCHAR(50)` | YES | `NULL` | Height (e.g., `5 ft 8 in`) |
| `marital_status` | `ENUM(...)` | NO | `'unmarried'` | `unmarried`, `divorced` (Talaq Shuda), `khula_shuda`, `widowed` (Bewa) |
| `cast` | `VARCHAR(100)` | YES | `NULL` | Caste denomination (`Eraquee(Iraqi)`, etc.) |
| `complexion` | `VARCHAR(50)` | YES | `NULL` | Complexion description |
| `physical_status`| `VARCHAR(100)` | YES | `'Normal'` | Physical disability / status |
| `qualification` | `VARCHAR(255)` | YES | `NULL` | Highest educational degree |
| `occupation` | `VARCHAR(255)` | YES | `NULL` | Profession / Job title |
| `employed_in` | `VARCHAR(100)` | YES | `NULL` | Private, Government, Business, Self-Employed |
| `annual_income` | `VARCHAR(100)` | YES | `NULL` | Income bracket |
| `father_name` | `VARCHAR(255)` | YES | `NULL` | Father's name |
| `father_occupation`| `VARCHAR(255)`| YES | `NULL` | Father's occupation |
| `mother_name` | `VARCHAR(255)` | YES | `NULL` | Mother's name |
| `brothers_count`| `INT(11)` | YES | `0` | Number of brothers |
| `sisters_count` | `INT(11)` | YES | `0` | Number of sisters |
| `family_type` | `VARCHAR(50)` | YES | `'Nuclear'` | Nuclear / Joint family |
| `family_values` | `VARCHAR(50)` | YES | `'Moderate'` | Orthodox, Traditional, Moderate, Liberal |
| `native_place` | `VARCHAR(255)` | YES | `NULL` | Ancestral village / city |
| `present_city` | `VARCHAR(100)` | YES | `NULL` | Current residential city |
| `present_state` | `VARCHAR(100)` | YES | `NULL` | Current residential state |
| `about_candidate`| `TEXT` | YES | `NULL` | Personal bio & hobbies |
| `partner_expectations`| `TEXT` | YES | `NULL` | Expectations from spouse |
| `contact_person_name`| `VARCHAR(255)`| YES | `NULL` | Guardian / Contact person |
| `contact_relation`| `VARCHAR(100)` | YES | `'Self'` | Relation to candidate |
| `contact_phone` | `VARCHAR(50)` | YES | `NULL` | Primary guardian contact (Tier 3 protected) |
| `contact_whatsapp`| `VARCHAR(50)` | YES | `NULL` | WhatsApp number (Tier 3 protected) |
| `contact_address`| `TEXT` | YES | `NULL` | Full residential address (Tier 3 protected) |
| `photo_privacy` | `ENUM(...)` | YES | `'reciprocal_only'` | `visible_all`, `reciprocal_only`, `request_only` |
| `status` | `ENUM(...)` | YES | `'active'` | `active`, `paused`, `married`, `deleted` |
| `created_at` | `TIMESTAMP` | NO | `CURRENT_TIMESTAMP` | Profile creation timestamp |

---

### 4. Table: `matrimonial_photos` (Candidate Gallery)
| Column | Type | Nullable | Default | Description |
| :--- | :--- | :---: | :---: | :--- |
| `id` | `INT(11)` | NO | `AUTO_INCREMENT` | Primary Key, Photo ID |
| `profile_id` | `INT(11)` | NO | `None` | Foreign Key to `matrimonial_profiles(id)` |
| `photo_path` | `VARCHAR(255)` | NO | `None` | Relative path in `uploads/matrimonial/` |
| `is_primary` | `TINYINT(1)` | YES | `1` | Flag: 1 if hero photograph |
| `created_at` | `TIMESTAMP` | NO | `CURRENT_TIMESTAMP` | Upload timestamp |

---

### 5. Table: `matrimonial_interests` (Proposal Exchange)
| Column | Type | Nullable | Default | Description |
| :--- | :--- | :---: | :---: | :--- |
| `id` | `INT(11)` | NO | `AUTO_INCREMENT` | Primary Key, Interest ID |
| `sender_user_id` | `INT(11)` | NO | `None` | Foreign Key to `user_registrtion(id)` (Sender Member) |
| `sender_profile_id`| `INT(11)` | NO | `None` | Foreign Key to `matrimonial_profiles(id)` |
| `receiver_user_id`| `INT(11)` | NO | `None` | Foreign Key to `user_registrtion(id)` (Receiver Member) |
| `receiver_profile_id`| `INT(11)` | NO | `None` | Foreign Key to `matrimonial_profiles(id)` |
| `message` | `VARCHAR(255)` | YES | `NULL` | Optional greeting / proposal message |
| `status` | `ENUM(...)` | NO | `'pending'` | `pending`, `accepted`, `declined`, `withdrawn` |
| `created_at` | `TIMESTAMP` | NO | `CURRENT_TIMESTAMP` | Proposal submission timestamp |
| `responded_at` | `DATETIME` | YES | `NULL` | Timestamp of acceptance or decline |

---

### 6. Table: `matrimonial_access_requests` (Tier 3 Admin Contact Approval)
| Column | Type | Nullable | Default | Description |
| :--- | :--- | :---: | :---: | :--- |
| `id` | `INT(11)` | NO | `AUTO_INCREMENT` | Primary Key, Request ID |
| `requester_user_id`| `INT(11)` | NO | `None` | Member requesting full contact info |
| `target_profile_id`| `INT(11)` | NO | `None` | Target biodata candidate |
| `requester_profile_id`| `INT(11)`| YES | `NULL` | Candidate profile represented by requester |
| `status` | `ENUM(...)` | NO | `'pending'` | `pending`, `approved_by_admin`, `rejected_by_admin` |
| `reviewed_by_admin_id`| `INT(11)`| YES | `NULL` | Admin ID who adjudicated request |
| `admin_notes` | `VARCHAR(255)` | YES | `NULL` | Optional moderation notes |
| `created_at` | `TIMESTAMP` | NO | `CURRENT_TIMESTAMP` | Request submission timestamp |
| `reviewed_at` | `DATETIME` | YES | `NULL` | Admin decision timestamp |

---

## 6. Infrastructure & Deployment Setup

### Local Development Environment
- **Web Server**: Apache running via XAMPP on Windows.
- **Port**: `8080` (configured to prevent standard port 80 conflicts with Windows IIS / World Wide Web Publishing Service).
- **MySQL Ports**: Auto-detects port `3307` or `3306` via connection cascade in `db.php`.

### Production Environment (Shared Host / InfinityFree)
- **Web Server**: Apache Linux with `.htaccess` support.
- **Auto-Migration**: Self-healing `db.php` checks for missing columns and tables on runtime, ensuring zero manual CLI commands are required on shared hosts without SSH.
- **File Upload Perms**: `uploads/` directory with `0755` permissions for secure asset writes.

