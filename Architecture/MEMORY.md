# Project Memory & AI Assistant Context

## Anjuman Eraquee INDIA — Context, State, Decisions & Living Memory

> **Purpose**: This document maintains continuous state, deep architectural context, active milestones, safety constraints, and quick reference material across development sessions, whether collaborating with AI assistants (Antigravity) or onboarding new human developers.

| Metadata | Details |
| :--- | :--- |
| **Last Updated** | September 20, 2026 |
| **Current Phase** | Phase 6 (Architecture Documentation & Live Hosting Deployment) |
| **Overall Progress** | 85% Complete |
| **Project Status** | Active Development / Production Ready |
| **Repository** | `nadim144/anjuman_eraquee` |
| **Active Git Branch** | `implementing-of-Supar-Admin-and-Admin-remove-hard-coded-Userid-and-password` |

---

## 📌 1. Project Context & Mission

**Anjuman Eraquee India** is a community empowerment organization dedicated to unifying the Eraquee (Iraqi) community throughout India. The platform replaces scattered paper records with:
1. A digital member registration wizard (with real-time age calculation, caste selection, photo upload, and Aadhaar collection).
2. Instant downloadable, verifiable PDF membership certificates with member photos and seals.
3. Self-service member dashboard and profile management.
4. Dynamic contact and helpline synchronization across all public marketing pages.
5. Role-Based Access Control (RBAC) administrative portal with a dedicated Super Admin console.

---

## 🔒 2. Critical Safety & Architectural Rules (NEVER VIOLATE)

1. **Primary Super Admin Safety Lock**:
   - The primary Super Administrator account (**`ahmad.nadim144@gmail.com`**, Admin ID: 1, User ID: 3, Md Nadim Ahmad) must **NEVER** be deleted, demoted, suspended, or locked out.
   - All controller and action routes (`admin/admins.php`, `admin/members.php`) enforce this rule at the PHP level.
2. **Non-Destructive Database Migrations**:
   - Never execute `DROP TABLE`, `TRUNCATE`, or wide `DELETE` statements.
   - Schema modifications are managed via runtime auto-migrations in [db.php](file:///c:/xampp/htdocs/anjuman_eraquee/db.php) using `CREATE TABLE IF NOT EXISTS` and `SHOW COLUMNS FROM`.
3. **Single Source of Truth for Passwords**:
   - Passwords exist ONLY in `user_registrtion.password`. The `admin_users` table links via `user_id` and does not store redundant passwords.
4. **No Unescaped `innerHTML` with Dynamic User Data**:
   - Always use `textContent` or `document.createElement()` in JavaScript and `htmlspecialchars()` in PHP to prevent Cross-Site Scripting (XSS).
5. **Windows Shell / PowerShell Escaping**:
   - Avoid executing complex inline `php -r "..."` snippets with unescaped `$` in PowerShell. Always create temporary test scripts in the scratch directory or use single-quoted expressions.
6. **No Terminal Deletion Commands**:
   - Never run `Remove-Item` or `rm` in terminal prompts as they trigger security approval stops.

---

## 🏛️ 3. Core Database Entities Quick Reference

### Table: `user_registrtion` (Member Records)
- **Primary Key**: `id` (Auto-increment integer, serves as Member ID)
- **Core Columns**: `username`, `fathername`, `mothername`, `dob`, `age`, `gender`, `maritalstatus`, `cast`, `aadhaar_number`, `profile_picture`, `phonenumber`, `additional_mobile`, `whatsappnumber`, `email`, `presentaddress`, `presentdistrict`, `presentstate`, `presentpincode`, `permanentaddress`, `qulification`, `occupation`, `password`, `is_temp_password`, `reset_requested`, `certificate_path`, `certificate_generated_at`, `created_at`.

### Table: `admin_users` (Role-Based Access Control)
- **Primary Key**: `id`
- **Foreign Key**: `user_id` (`INT(11)` **UNIQUE**, references `user_registrtion.id`)
- **Role**: `ENUM('super_admin', 'admin')`
- **Status**: `ENUM('active', 'inactive')`
- **Audit Columns**: `created_by`, `created_at`, `last_login`

---

## 🌐 4. Environments & Port Mappings

| Service | Local Development | Remote Production (InfinityFree) |
| :--- | :--- | :--- |
| **Apache Web Server** | `http://localhost:8080/anjuman_eraquee/` | `https://anjumaneraquee.org` (or temporary domain) |
| **MySQL Database** | Ports `3307` or `3306` (Database: `codecxss_anjuman`) | Standard port `3306` (Remote user/database) |
| **Admin Login** | `http://localhost:8080/anjuman_eraquee/admin/login.php` | `https://<live-domain>/admin/login.php` |
| **Member Login** | `http://localhost:8080/anjuman_eraquee/user-login.php` | `https://<live-domain>/user-login.php` |
| **Settings API** | `http://localhost:8080/anjuman_eraquee/api/settings.php` | `https://<live-domain>/api/settings.php` |

---

## 🚀 5. Command Cheat Sheet

### Syntax Linting (Always run before commits):
```powershell
C:\xampp\php\php.exe -l db.php; C:\xampp\php\php.exe -l admin/auth.php; C:\xampp\php\php.exe -l admin/login.php; C:\xampp\php\php.exe -l admin/admins.php; C:\xampp\php\php.exe -l admin/members.php; C:\xampp\php\php.exe -l admin/index.php; C:\xampp\php\php.exe -l admin/settings.php
```

### Git Workflow:
```powershell
git status
git add .
git commit -m "feat(scope): descriptive action message"
git push origin <branch-name>
```

---

## 🗺️ 6. Documentation Suite Sitemap

All formal engineering blueprints and specifications live inside `Architecture/`:
- [PRD.md](file:///c:/xampp/htdocs/anjuman_eraquee/Architecture/PRD.md) — Product Requirements Document (Goals, Personas, Features, Roadmap)
- [ARCHITECTURE.md](file:///c:/xampp/htdocs/anjuman_eraquee/Architecture/ARCHITECTURE.md) — System Architecture, Tech Stack, Data Flow & Database Schema
- [DESIGN.md](file:///c:/xampp/htdocs/anjuman_eraquee/Architecture/DESIGN.md) — UI/UX Design System, Typography, Colors & Components
- [RULES.md](file:///c:/xampp/htdocs/anjuman_eraquee/Architecture/RULES.md) — Engineering Rules, Coding Standards & Git Conventions
- [TASKS.md](file:///c:/xampp/htdocs/anjuman_eraquee/Architecture/TASKS.md) — Phased Task Breakdown & Development Roadmap
- [TEST_PLAN.md](file:///c:/xampp/htdocs/anjuman_eraquee/Architecture/TEST_PLAN.md) — Testing Strategy & Comprehensive Test Cases
- [SECURITY.md](file:///c:/xampp/htdocs/anjuman_eraquee/Architecture/SECURITY.md) — Threat Modeling, Security Architecture & Hardening
- [DECISIONS.md](file:///c:/xampp/htdocs/anjuman_eraquee/Architecture/DECISIONS.md) — Architecture Decision Records (ADRs)
- [MEMORY.md](file:///c:/xampp/htdocs/anjuman_eraquee/Architecture/MEMORY.md) — Living Context, Quick Reference & AI Assistant Guide (This File)

