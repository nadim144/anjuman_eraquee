# Product Requirements Document (PRD)

## Anjuman Eraquee INDIA — Digital Community & Membership Platform

| Metadata | Details |
| :--- | :--- |
| **Document Version** | 1.0.0 |
| **Date** | September 20, 2026 |
| **Author** | Antigravity AI & Technical Team |
| **Status** | Active / Production Blueprint |
| **Target Organization** | Anjuman Eraquee India (All-India Community Empowerment Body) |
| **Primary URL** | `https://anjumaneraquee.org` (Local: `http://localhost:8080/anjuman_eraquee/`) |

---

## 1. Product Overview

**Anjuman Eraquee India** is a centralized digital community platform built to unite, empower, organize, and serve the Eraquee (Iraqi) community nationwide. The platform modernizes community engagement by replacing fragmented manual paper records with a streamlined digital registration system, providing instant verified digital membership certificates, self-service member profiles, dynamic community communication channels, and a role-based administrative management portal.

---

## 2. Problem Statement

Historically, community organizations faced significant organizational bottlenecks:
1. **Scattered Records**: Member registrations were collected across physical paper forms, loose sheets, or informal WhatsApp groups, leading to lost records, duplicate entries, and zero centralized search capability.
2. **Lack of Verified Identification**: Community members had no official, standardized proof of membership or verifiable credential to participate in formal community initiatives, conventions, or welfare programs.
3. **Communication Friction**: Announcements, executive contact numbers, and community updates required manual code edits to static HTML files, delaying time-sensitive notices.
4. **Administrative Vulnerability**: Administrative actions lacked role separation, audit trails, and secure authentication, risking unauthorized data access or accidental loss.

---

## 3. Goals & Objectives

- **Centralized Member Database**: Establish a single, scalable, relational MySQL database of registered community members across all Indian states and districts.
- **Automated Membership Certification**: Provide instant, downloadable, tamper-resistant PDF membership certificates with unique IDs, dynamic date stamping, and embedded member photographs.
- **Self-Service Experience**: Allow members to log in, view and edit their profile details, request password resets, and re-download their certificate anytime.
- **Dynamic Site Management**: Enable administrators to update topbar and footer emergency contact numbers, convenor details, and YouTube video embeds without touching backend source code.
- **Role-Based Security (RBAC)**: Enforce a strict distinction between the primary Super Administrator (executive control, admin promotions/revocations) and standard Admins (member management, site settings).

---

## 4. Target Users & Personas

### 1. Community Member
- **Demographics**: Eraquee community members residing across India (Bihar, Jharkhand, UP, West Bengal, Delhi, Maharashtra, etc.), spanning students, professionals, business owners, and senior citizens.
- **Tech Savviness**: Diverse (ranging from mobile-first smartphone users to desktop users).
- **Core Needs**: Fast registration (3-step wizard), mobile-friendly interface, instant certificate download, password self-recovery.

### 2. Administrator (Executive Committee Member)
- **Role**: District, state, or national executive committee volunteers tasked with verifying members, updating site notices, and managing local inquiries.
- **Core Needs**: Clean search/filter directory, CSV export for offline meetings, member detail inspection, temporary password issuance for non-tech-savvy members.

### 3. Super Administrator (`ahmad.nadim144@gmail.com`)
- **Role**: Head of Organization & Technical Custodian.
- **Core Needs**: Full administrative privileges, ability to appoint new Admins or revoke access, suspension controls, permanent security lock preventing accidental lockout.

---

## 5. Core Features & Capabilities

### A. Public Community Portal (17 Pages)
- Modern responsive layout showcasing Anjuman history, core executive committee, state/district/block leadership, cause campaigns, charity appeals, photo galleries, and video broadcasts.
- Dynamic phone/email synchronization across headers and footers driven by `data/settings.json`.
- Quick-access "Join Membership" signup modal and "Member Login" portals.

### B. Multi-Step Registration Wizard
- **Step 1: Personal Information**: Full Name, Father's Name, Mother's Name, Grandfather's Name, DOB with automatic real-time age calculation, Gender, Marital Status, Cast dropdown (`Kalal`, `Kalwar`, `Kalar`, `Eraquee(Iraqi)`, `Kalal Lari`, `Kalal Choudhary`, `Araqi`, `Ranki`), Aadhaar Number, Profile Photo upload with real-time preview.
- **Step 2: Contact & Address Information**: Primary Mobile, Alternate Mobile, WhatsApp Number, Email, Native Place, Present & Permanent Address with District, State, and PIN code.
- **Step 3: Education, Profession & Security**: Qualification, Profession, Password creation (minimum 6 characters with show/hide toggle), Terms acceptance.

### C. Member Authentication & Dashboard
- Dual identifier login: Registered Phone Number or Email + Password.
- Temporary Password flow: If an admin issues a temporary password, the member is forced to set a personal permanent password on next login.
- Interactive Dashboard: Profile overview, live circular photo display, verified membership status, inline profile editor, and instant PDF certificate generation.

### D. Automated PDF Membership Certificate Engine
- Built with FPDF: Standardized landscape/portrait layout with official decorative borders, circular avatar positioning, organization seals, and authorized signatures.
- Dual Delivery: Instant browser streaming (`D` flag) + persistent server storage (`uploads/certificates/`) linked to database records.

### E. Role-Based Access Control (RBAC) Admin Portal
- Database-backed authentication against `admin_users` JOIN `user_registrtion` using existing member credentials.
- **Super Admin Console (`admin/admins.php`)**: Active admin count, promote registered member to admin, suspend/activate toggle, revoke admin power, and immutable primary Super Admin safety lock.
- **Member Directory (`admin/members.php`)**: Search by name/email/phone/district, filter password reset requests, export CSV, view full dossier modal, edit records, issue temp passwords, direct promote/demote action buttons.
- **Dynamic Site Settings (`admin/settings.php`)**: Real-time editor for helpline phones, convenor contact, and live video feeds.

### F. Community Matrimonial System
- **Registered Member Constraint**: Only verified registered community members can create matrimonial profiles, browse the matchmaking directory, or submit proposals.
- **Multi-Profile Management**: A registered member can create and manage candidate profiles for themselves (`self`) or for family members (`son`, `daughter`, `brother`, `sister`, `relative`).
- **Inclusive Marital Status Options**: Unmarried (`unmarried`), Divorced / Talaq Shuda (`divorced`), Khula Shuda (`khula_shuda`), and Widowed / Bewa (`widowed`).
- **3-Tier Privacy Framework**:
  - *Tier 1 (Public / Registered Viewer without reciprocal candidate profile)*: Limited summary view; profile photo is strictly locked/blurred; surname, contact numbers, and home addresses are hidden.
  - *Tier 2 (Reciprocal Candidate Match)*: Active candidate profiles of the opposite gender unlock 1 normal photograph, candidate first name, and detailed education/profession/family biodata.
  - *Tier 3 (Admin-Verified Full Access)*: Members can request guardian contact details through the platform; once vetted and approved by an Administrator, direct contact phone numbers, WhatsApp, and complete home address are released.
- **Bilateral Proposal & Interest Engine**: Members can send proposals ("❤️ Show Interest"); receiving families are alerted in their dashboard to "Accept" or "Decline"; acceptance establishes mutual interest.
- **Admin Moderation & Governance (`admin/matrimonial.php`)**: Two-tab administrative console to inspect/approve contact release requests and toggle directory profile visibility.

---

## 6. Out of Scope (Current Phase)

- Payment Gateway integration for mandatory membership fees or donations.
- Native Mobile App (iOS / Android) binaries.
- Automated SMS OTP gateway via third-party providers (currently uses simulated OTP with password fallback).
- Real-time in-app instant chat messaging (communication occurs via approved WhatsApp / phone contact).

---

## 7. Success Metrics & KPIs

| Metric | Target | Verification Method |
| :--- | :--- | :--- |
| **Member Adoption** | 10,000+ members | SQL count on `user_registrtion` |
| **Certificate Generation** | 100% success rate | Zero FPDF generation fatals, verified in `uploads/certificates/` |
| **Matrimonial Matchmaking** | 1,000+ verified biodatas | SQL count on `matrimonial_profiles` |
| **Administrative Security** | 0 unauthorized privilege escalations | RBAC session checks and access log audits |
| **Mobile Usability** | 100% viewport compatibility | Chrome DevTools responsive simulation (320px - 1440px) |

---

## 8. Product Roadmap & Milestones

1. **Phase 1 to Phase 6**: Public Portal, Registration Engine, Member Dashboard, PDF Certificates, RBAC Admin Portal, Production Hardening. [COMPLETED]
2. **Phase 7**: SMS Gateway Integration (Fast2SMS / Twilio) for one-time mobile verification and automated broadcast notices. [UPCOMING]
3. **Phase 8**: Community Matrimonial System (3-Tier Privacy, Biodatas, Mutual Interests, Admin Contact Release). [COMPLETED]
4. **Phase 9**: Job & Career Board with community scholarship distribution tracking. [FUTURE]
5. **Phase 10**: Progressive Web App (PWA) offline caching and push notifications. [FUTURE]

---

## 9. Assumptions & Technical Constraints

- **Hosting Environment**: InfinityFree / Apache Shared Hosting with PHP 7.4+ and MySQL 5.7+ / MariaDB 10.3+.
- **Storage Constraints**: Member photos and generated certificates must be optimized to stay within shared-host disk quotas.
- **Database Migrations**: Because shared hosting lacks CLI shell access, migrations must remain automated and non-destructive within `db.php`.

---

## 10. Stakeholders & Revision History

### Stakeholders
- **Executive Sponsor & Super Admin**: Md Nadim Ahmad (`ahmad.nadim144@gmail.com`)
- **Convenor**: Abul Farah Sb. (`+91 9006297386`)
- **Engineering & Architecture**: Antigravity AI Engineering Team

### Revision History
| Version | Date | Author | Summary of Changes |
| :--- | :--- | :--- | :--- |
| **1.0.0** | 2026-09-20 | Antigravity AI | Initial formal PRD detailing platform features, RBAC architecture, and roadmap. |
| **1.1.0** | 2026-09-20 | Antigravity AI | Added full Community Matrimonial System specifications, 3-tier privacy engine, and bilateral interest workflow. |

