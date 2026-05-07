# Tap and Track – Software Requirements Specification (SRS)

**Version:** 1.1  
**Date:** May 6, 2026  
**Application:** Tap and Track – Attendance Management System

---

## Table of Contents

1. Introduction
2. Overall Description
3. Stakeholders & User Classes
4. Functional Requirements
5. Non-Functional Requirements
6. System Architecture
7. Database Design
8. External Interface Requirements
9. Constraints & Assumptions
10. Appendix A – Use Case Descriptions

---

## 1. Introduction

### 1.1 Purpose
This Software Requirements Specification (SRS) document describes the functional and non-functional requirements for the Tap and Track attendance management system. It is intended to guide developers, testers, and stakeholders throughout the software development lifecycle.

### 1.2 Scope
Tap and Track is a web-based application that automates employee attendance tracking, clock-in/out verification, manager approvals, and vehicle mileage logging. The system replaces manual attendance records and provides real-time visibility to supervisors and administrators.

### 1.3 Definitions, Acronyms, and Abbreviations

| Term | Definition |
|---|---|
| SRS | Software Requirements Specification |
| UI | User Interface |
| IP | Internet Protocol address |
| GPS | Global Positioning System |
| RBAC | Role-Based Access Control |
| ORM | Object-Relational Mapping |
| MVC | Model-View-Controller |
| CSV | Comma-Separated Values export format |
| FR | Functional Requirement |
| NFR | Non-Functional Requirement |

### 1.4 References
- Laravel 12 Documentation – https://laravel.com/docs
- Filament 3.2 Documentation – https://filamentphp.com/docs
- Tap and Track README.md

### 1.5 Overview
Section 2 provides a high-level description of the system. Sections 3–5 detail the stakeholders and requirements. Sections 6–7 describe the architecture and data design. Section 8 covers external interfaces. Section 9 lists constraints, and the Appendix provides use-case descriptions.

---

## 2. Overall Description

### 2.1 Product Perspective
Tap and Track is a standalone web application accessed via a browser. It does not integrate with third-party HRIS or payroll systems in its initial version. It relies on the host network infrastructure (IP addresses) for location verification and optionally on GPS coordinates stored in the browser.

### 2.2 Product Functions (Summary)
- Employee clock-in and clock-out with IP/GPS verification
- Automatic clock-out after 16-hour shift (Safety Net, configurable via `ATTENDANCE_MAX_SHIFT_HOURS`)
- Three-strike warning escalation for missed clock-outs (`incomplete_clock_out_count`)
- Manager review and approval/rejection of attendance records
- Work site management with IP whitelisting and GPS radius
- Company vehicle registration and mileage log tracking
- Role-based access: Admin (1), Manager (2), Staff (3)
- Reporting dashboard with statistics and CSV export

### 2.3 Operating Environment

| Component | Specification |
|---|---|
| Backend Framework | Laravel 12 (PHP 8.2+) |
| Admin UI Framework | Filament 3.2 |
| Frontend Build | Vite 7, Tailwind CSS 4 |
| Database | SQLite (default) |
| Caching | File-based (Laravel Cache) |
| Web Server | PHP built-in / Apache / Nginx |
| Browser Support | Chrome 100+, Firefox 100+, Edge 100+, Safari 15+ |

### 2.4 Design and Implementation Constraints
- Authentication uses phone number instead of email.
- All role access is enforced via the `canAccessPanel()` method in the User model.
- IP verification is server-side; GPS coordinates are optional metadata.
- The application is not designed for offline use.

### 2.5 Assumptions and Dependencies
- All users have a unique phone number.
- The server is accessible via HTTPS in production.
- The administrator has pre-configured at least one site before staff can clock in.
- Network IP addresses at each site are static and known.

---

## 3. Stakeholders & User Classes

| User Class | Role Code | Description | Portal |
|---|---|---|---|
| Administrator | 1 | Full system control; manages users (all roles), sites, and all data | `/admin` |
| Manager | 2 | Reviews and approves attendance; creates and manages Staff users; views team records | `/staff` |
| Staff (Employee) | 3 | Clocks in/out; views own attendance history | `/staff` |

### 3.1 Roles & Permissions Matrix

| Feature | Admin | Manager | Staff |
|---|---|---|---|
| Clock In / Out | – | ✓ | ✓ |
| View Own Attendance | ✓ | ✓ | ✓ |
| View All Attendance | ✓ | ✓ (staff only) | – |
| Approve / Reject Attendance | ✓ | ✓ | – |
| Create Staff Users | ✓ | ✓ | – |
| Create Manager Users | ✓ | – | – |
| Manage Sites | ✓ | View only | – |
| Manage Vehicles | – | ✓ | View only |
| Log Mileage | – | ✓ | ✓ |
| Export CSV | ✓ | ✓ | – |

---

## 4. Functional Requirements

### 4.1 Authentication & Access Control

| ID | Requirement |
|---|---|
| FR-01 | The system shall authenticate users using their phone number and password. |
| FR-02 | The system shall enforce role-based access control (RBAC) with three roles: Admin (1), Manager (2), Staff (3). |
| FR-03 | Admin users shall only access the `/admin` panel; Managers and Staff shall only access the `/staff` panel. |
| FR-04 | The system shall redirect unauthorised users to a login page. |

### 4.2 Attendance Tracking

| ID | Requirement |
|---|---|
| FR-05 | The system shall allow Managers and Staff to record a clock-in event with a timestamp and associated site. |
| FR-06 | The system shall allow Managers and Staff to record a clock-out event for their active shift. |
| FR-07 | The system shall prevent a second clock-in if an active (unclosed) attendance record exists. |
| FR-08 | The system shall evaluate the client IP address at clock-in for verification and write verification outcomes to `verification_notes`. |
| FR-09 | Attendance records shall store: `user_id`, `site_name`, `latitude`, `longitude`, `clock_in_time`, `clock_out_time`, `status`, `verification_notes`, `approval_notes`, `approved_by`, `approved_at`. |
| FR-10 | The system shall support the following attendance statuses: `pending`, `approved`, `rejected`, `temporary`, `completed`. |

**Status definitions:**

| Status | Meaning |
|---|---|
| `pending` | Clocked in; IP/GPS not verified; awaiting manager approval |
| `temporary` | Clocked out but not yet approved by a manager |
| `approved` | Shift approved by a manager (or auto-verified at clock-in) |
| `completed` | Shift was already approved at clock-in and employee has now clocked out |
| `rejected` | Attendance record rejected by a manager |

### 4.3 Manager Approvals

| ID | Requirement |
|---|---|
| FR-11 | Managers shall be able to view all `pending` and `temporary` attendance records except their own records. |
| FR-12 | Managers shall be able to approve or reject an attendance record with optional/required notes. |
| FR-13 | The system shall record the approver's `user_id` (`approved_by`), approval timestamp (`approved_at`), and optional notes (`approval_notes`) on each reviewed record. |

### 4.4 Safety Net System

| ID | Requirement |
|---|---|
| FR-14 | The system shall automatically clock out any employee whose shift has been open for 16 hours or longer (configurable via `ATTENDANCE_MAX_SHIFT_HOURS`). |
| FR-15 | Each auto clock-out shall increment the employee's `incomplete_clock_out_count` by 1 and create an `AttendanceInfraction` record with `infraction_type = 'forgot_clock_out'`. |
| FR-16 | At `incomplete_clock_out_count = 1`: the dashboard warning widget shows the first warning. |
| FR-17 | At `incomplete_clock_out_count = 2`: the dashboard warning widget shows the final warning. |
| FR-18 | At `incomplete_clock_out_count >= 3`: the dashboard warning widget shows an escalation alert indicating manager review is needed. |

### 4.5 Site Management

| ID | Requirement |
|---|---|
| FR-19 | Administrators shall be able to create, edit, and deactivate work sites. |
| FR-20 | Each site shall store a name, IP address, GPS coordinates (`latitude`, `longitude`), `radius_meters`, and `is_active` flag. |
| FR-21 | The system shall compare the employee's IP at clock-in to the site's registered `ip_address` for verification. |

### 4.6 Vehicle & Mileage Tracking

| ID | Requirement |
|---|---|
| FR-22 | The system shall support registration of company vehicles with `name`, `numberplate`, `current_mileage`, `next_service_mileage`, `is_active`, and optional `notes`. |
| FR-23 | Users shall be able to log a mileage entry recording the **current odometer reading** (`mileage_reading`) at the time of the trip, along with the vehicle, date/time (`recorded_at`), and optional notes. |
| FR-24 | The system shall calculate and display service status: OK, Due Soon (≤ 500 km), or Overdue. |

### 4.7 Reporting & Export

| ID | Requirement |
|---|---|
| FR-25 | The dashboard shall display attendance statistics widgets. |
| FR-26 | Managers and Admins shall be able to export attendance data as a CSV file via the `/attendance/export` route. |

---

## 5. Non-Functional Requirements

### 5.1 Performance

| ID | Requirement |
|---|---|
| NFR-01 | Dashboard statistics widgets shall load within 3 seconds under normal load. |
| NFR-02 | Frequently queried columns (`user_id`, `clock_in_time`, `status`) shall be indexed in the database. |
| NFR-03 | Statistics queries shall be cached for 1–2 minutes to reduce database load. |
| NFR-04 | CSV exports shall use lazy collection processing to handle large datasets without memory exhaustion. |

### 5.2 Security

| ID | Requirement |
|---|---|
| NFR-05 | All passwords shall be stored as bcrypt hashes. |
| NFR-06 | All web routes shall be protected by CSRF tokens. |
| NFR-07 | Role-based access shall be enforced server-side on every request. |
| NFR-08 | The application shall be deployable behind HTTPS in production. |

### 5.3 Usability

| ID | Requirement |
|---|---|
| NFR-09 | The UI shall be responsive and usable on desktop and mobile browsers. |
| NFR-10 | Role-specific navigation shall only display menu items relevant to the logged-in role. |
| NFR-11 | User feedback notifications (success, error) shall appear within 1 second of action. |

### 5.4 Reliability

| ID | Requirement |
|---|---|
| NFR-12 | The Safety Net auto clock-out check is triggered on every dashboard load for the active user, ensuring stale shifts are closed without requiring a separate scheduled job. |
| NFR-13 | The system shall preserve all attendance records and not delete them unless explicitly done by an admin. |

### 5.5 Maintainability

| ID | Requirement |
|---|---|
| NFR-14 | The codebase shall follow the MVC pattern enforced by the Laravel framework. |
| NFR-15 | Database changes shall be managed exclusively through Laravel migrations. |

---

## 6. System Architecture

### 6.1 Architectural Pattern
Tap and Track follows the MVC (Model-View-Controller) architecture enforced by the Laravel framework, extended by the Filament admin panel package which adds resource classes, widgets, and pages as an abstraction layer over standard controllers and views.

### 6.2 Component Overview

| Layer | Technology | Role |
|---|---|---|
| Presentation | Filament 3.2 + Tailwind CSS 4 | Renders UI panels and widgets |
| Application | Laravel 12 (PHP 8.2+) | Business logic, routing |
| Data Access | Eloquent ORM | Database abstraction and relationships |
| Database | SQLite | Persistent data storage |
| Caching | Laravel File Cache | Query result caching |
| Asset Build | Vite 7 + esbuild | JS/CSS bundling and minification |

### 6.3 Directory Structure (Key Paths)

| Path | Contents |
|---|---|
| `app/Models/` | Eloquent models (User, Attendance, Site, Vehicle, MileageLog, AttendanceInfraction) |
| `app/Filament/Admin/` | Admin panel resources (Users, Sites, Attendances) |
| `app/Filament/Staff/` | Staff panel resources, pages, and widgets |
| `app/Services/` | Business logic services (AttendanceWindowService, AttendanceVerificationService, AttendanceMetricsService) |
| `database/migrations/` | Database schema migrations |
| `routes/web.php` | HTTP route definitions |
| `resources/views/` | Blade templates |
| `config/attendance.php` | Attendance thresholds and shift configuration |

---

## 7. Database Design

### 7.1 Entity Relationship Summary

| Relationship | Type | Description |
|---|---|---|
| User → Attendances | One-to-Many | A user has many attendance records |
| User → AttendanceInfractions | One-to-Many | A user can accumulate multiple infractions |
| User (approver) → Attendances | One-to-Many (via `approved_by`) | A manager approves many records |
| Vehicle → MileageLogs | One-to-Many | A vehicle has many mileage log entries |
| User → MileageLogs | One-to-Many | A user logs many mileage entries |

### 7.2 Table Definitions

**Table: `users`**

| Column | Type | Description |
|---|---|---|
| `id` | INTEGER | Primary key |
| `name` | VARCHAR | Full name |
| `phone` | VARCHAR | Unique phone number (login identifier) |
| `password` | VARCHAR | Bcrypt hash |
| `role` | INTEGER | 1 = Admin, 2 = Manager, 3 = Staff |
| `incomplete_clock_out_count` | INTEGER | Warning counter (0+) |
| `remember_token` | VARCHAR | Auth remember token |
| `created_at` / `updated_at` | TIMESTAMP | Audit timestamps |

**Table: `attendances`**

| Column | Type | Description |
|---|---|---|
| `id` | INTEGER | Primary key |
| `user_id` | INTEGER | FK → `users.id` |
| `site_name` | VARCHAR | Name of work site at clock-in |
| `latitude` | DECIMAL | GPS latitude at clock-in |
| `longitude` | DECIMAL | GPS longitude at clock-in |
| `status` | VARCHAR | `pending` \| `approved` \| `rejected` \| `temporary` \| `completed` |
| `clock_in_time` | DATETIME | Timestamp of clock-in |
| `clock_out_time` | DATETIME | Timestamp of clock-out (nullable) |
| `verification_notes` | TEXT | IP/GPS check result notes |
| `approval_notes` | TEXT | Manager approval/rejection remarks |
| `approved_by` | INTEGER | FK → `users.id` of approver |
| `approved_at` | DATETIME | Approval timestamp |

**Table: `sites`**

| Column | Type | Description |
|---|---|---|
| `id` | INTEGER | Primary key |
| `name` | VARCHAR | Site name |
| `latitude` | DECIMAL | Site GPS latitude |
| `longitude` | DECIMAL | Site GPS longitude |
| `ip_address` | VARCHAR | Allowed IP address |
| `radius_meters` | INTEGER | GPS acceptance radius |
| `is_active` | BOOLEAN | Site active flag |

**Table: `vehicles`**

| Column | Type | Description |
|---|---|---|
| `id` | INTEGER | Primary key |
| `name` | VARCHAR | Vehicle name/model description |
| `numberplate` | VARCHAR | Registration plate (unique) |
| `current_mileage` | INTEGER | Current odometer reading |
| `next_service_mileage` | INTEGER | Mileage threshold for next service |
| `is_active` | BOOLEAN | Vehicle active flag |
| `notes` | TEXT | Optional notes |

**Table: `mileage_logs`**

| Column | Type | Description |
|---|---|---|
| `id` | INTEGER | Primary key |
| `vehicle_id` | INTEGER | FK → `vehicles.id` |
| `user_id` | INTEGER | FK → `users.id` |
| `mileage_reading` | INTEGER | Odometer reading recorded at the time of logging (journey start) |
| `notes` | TEXT | Optional notes (trip details, fuel, etc.) |
| `recorded_at` | DATETIME | When the log was recorded |

**Table: `attendance_infractions`**

| Column | Type | Description |
|---|---|---|
| `id` | INTEGER | Primary key |
| `user_id` | INTEGER | FK → `users.id` |
| `attendance_id` | INTEGER | FK → `attendances.id` (nullable) |
| `infraction_type` | VARCHAR | Always `forgot_clock_out` for auto clock-out events |
| `auto_clock_out_time` | DATETIME | The time the system closed the shift |
| `notes` | TEXT | Optional notes |
| `created_at` / `updated_at` | TIMESTAMP | Audit timestamps |

---

## 8. External Interface Requirements

### 8.1 User Interface
The application provides two web portals accessible via HTTP/HTTPS from any modern browser.
- The Admin Panel (`/admin`) uses an orange colour scheme.
- The Staff Portal (`/staff`) uses a green colour scheme.
- Both portals are built with the Filament component library.

### 8.2 Hardware Interfaces
No dedicated hardware interfaces are required. Optional GPS data may be captured via the browser's Geolocation API if the user grants permission.

### 8.3 Software Interfaces

| Interface | Description |
|---|---|
| Laravel Scheduler | Can be used to run additional maintenance commands (optional); the Safety Net check is also triggered on dashboard load |
| Browser Geolocation API | Optionally provides GPS latitude/longitude at clock-in |
| File System | Used for SQLite database file storage and Laravel file cache |
| Vite Dev Server | Used during development for hot module replacement (HMR) |

### 8.4 Communication Interfaces
All client-server communication uses HTTP/1.1 or HTTP/2 over TCP/IP. In production the application must be served over HTTPS (TLS 1.2+).

---

## 9. Constraints & Assumptions

### 9.1 Constraints
- The application requires PHP 8.2 or higher.
- The default database is SQLite; switching to MySQL/PostgreSQL requires updating the `.env` `DATABASE_CONNECTION` variable and re-running migrations.
- Clock-in IP verification requires that each work site has a known, static IP address.
- No email functionality is included in the initial version; notifications are in-app only.

### 9.2 Assumptions
- Each employee has a unique phone number used as their login identifier.
- At least one administrator account exists before any employee accounts are created.
- Work sites have a fixed, known IP address range that does not change frequently.
- All employees have access to a network-connected device to perform clock-in/out.

---

## Appendix A – Use Case Descriptions

### UC-01: Employee Clock In

| Field | Detail |
|---|---|
| Use Case ID | UC-01 |
| Title | Employee Clock In |
| Primary Actor | Staff (Role 3) |
| Preconditions | Employee is logged in. At least one active site exists. |
| **Main Flow** | 1. Employee navigates to My Attendance. 2. Employee clicks Clock In. 3. System captures current timestamp and IP address (optionally GPS coordinates via Geolocation API). 4. System attempts to match IP to a registered active site. 5. If IP matches → status set to `approved`. If GPS matches site radius → status set to `approved`. If group verification passes (5+ approved clock-ins within 50 m in last 2 hours) → status set to `approved`. Otherwise → status set to `pending` (awaiting manager approval). 6. System creates an attendance record with `verification_notes` describing the outcome. 7. System displays a success notification. |
| **Alternate / Exception** | 4a. IP does not match and GPS unavailable: `verification_notes` records mismatch details; status = `pending`. 5a. Employee already has an open (unclosed) shift: system blocks with a warning notification. |

### UC-02: Employee Clock Out

| Field | Detail |
|---|---|
| Use Case ID | UC-02 |
| Title | Employee Clock Out |
| Primary Actor | Staff (Role 3) |
| Preconditions | Employee has an active (open) attendance record (no `clock_out_time`). |
| **Main Flow** | 1. Employee navigates to My Attendance. 2. Employee clicks Clock Out. 3. System records `clock_out_time = now()`. 4. If prior shift status was `approved` → new status = `completed`. If prior shift status was `pending` → new status = `temporary`. 5. System displays a success notification. |
| **Alternate / Exception** | 3a. No active record found: Clock Out button is disabled. |

### UC-03: Manager Approves Attendance

| Field | Detail |
|---|---|
| Use Case ID | UC-03 |
| Title | Manager Approves / Rejects Attendance |
| Primary Actor | Manager (Role 2) |
| Preconditions | One or more attendance records are in `pending` or `temporary` status. |
| **Main Flow** | 1. Manager navigates to Clock-In Approvals. 2. Manager selects a pending/temporary record. 3. Manager reviews the details (clock-in time, site, verification notes). 4. Manager clicks **Approve** (with optional notes) or **Reject** (with required notes). 5. System sets status = `approved` or `rejected`, records `approved_by` (manager's `user_id`), `approved_at` (current timestamp), and `approval_notes`. |
| **Alternate / Exception** | 4a. Manager attempts to approve or reject their own record: system blocks the action with an error notification and leaves the record unchanged. |

### UC-04: Safety Net Auto Clock-Out

The system provides **two complementary mechanisms** that automatically close shifts exceeding the configured maximum duration (`ATTENDANCE_MAX_SHIFT_HOURS`, default 16 h).

#### UC-04a: Dashboard-Triggered Auto Clock-Out (widget)

| Field | Detail |
|---|---|
| Use Case ID | UC-04a |
| Title | Dashboard-Triggered Safety Net Auto Clock-Out |
| Primary Actor | System (triggered on every dashboard widget load / rehydration) |
| Preconditions | The logged-in user has an open shift whose `clock_in_time` is ≥ `ATTENDANCE_MAX_SHIFT_HOURS` hours ago. |
| **Main Flow** | 1. User loads their dashboard or a widget rehydrates. 2. System detects the open shift is stale using `AttendanceWindowService::isStaleShift()`. 3. System sets `clock_out_time = clock_in_time + max_shift_hours` (closes at the calculated limit, not at real time). 4. System sets status = `temporary`. 5. System creates an `AttendanceInfraction` record with `infraction_type = 'forgot_clock_out'`. 6. System increments the user's `incomplete_clock_out_count` by 1. |

#### UC-04b: Artisan Command Auto Clock-Out (`attendance:auto-clock-out`)

| Field | Detail |
|---|---|
| Use Case ID | UC-04b |
| Title | Artisan Command Safety Net Auto Clock-Out |
| Primary Actor | System (run via `php artisan attendance:auto-clock-out` or Laravel Scheduler) |
| Preconditions | One or more open shifts have a `clock_in_time` ≥ `ATTENDANCE_MAX_SHIFT_HOURS` hours in the past. |
| **Main Flow** | 1. Command queries all `Attendance` records where `clock_out_time IS NULL` and `clock_in_time ≤ now() − max_shift_hours`. 2. For each stale record, inside a DB transaction: 3. System sets `clock_out_time = now()` (actual execution time). 4. System sets status = `temporary`. 5. System creates an `AttendanceInfraction` with `infraction_type = 'auto_clock_out_{elapsed_hours}'` (e.g. `auto_clock_out_13`). 6. System increments the user's `incomplete_clock_out_count` by 1. |
| **Alternate / Exception** | No stale shifts found: command exits with no changes. Already-closed shifts (non-null `clock_out_time`) are skipped. |

**Warning escalation (applies to both UC-04a and UC-04b):**

| `incomplete_clock_out_count` | Dashboard Widget Message |
|---|---|
| 1 | First warning |
| 2 | Final warning |
| ≥ 3 | Escalation alert – manager review needed |

### UC-05: Manage Work Sites

| Field | Detail |
|---|---|
| Use Case ID | UC-05 |
| Title | Manage Work Sites |
| Primary Actor | Administrator (Role 1) |
| Preconditions | Admin is logged into the `/admin` panel. |
| **Main Flow** | 1. Admin navigates to the Sites resource. 2. Admin creates a new site by providing: name, IP address, GPS latitude/longitude, radius (metres), and active flag. 3. System saves the site. Active sites are immediately used for clock-in IP/GPS verification. |
| **Alternate / Exception** | 3a. Admin deactivates a site (`is_active = false`): site is excluded from all future clock-in verifications. Manager users can view sites but cannot create or edit them. |

### UC-06: Register Company Vehicle

| Field | Detail |
|---|---|
| Use Case ID | UC-06 |
| Title | Register Company Vehicle |
| Primary Actor | Manager (Role 2) |
| Preconditions | User is logged into the `/staff` panel with Manager role. |
| **Main Flow** | 1. Manager navigates to **Fleet → Vehicles**. 2. Manager clicks **New Vehicle** and provides: number plate (unique), name/model, current mileage, next service mileage, active flag, and optional notes. 3. System saves the vehicle. Service status (OK / Due Soon / Overdue) is calculated from `next_service_mileage − current_mileage`. |
| **Alternate / Exception** | 2a. Duplicate number plate: system rejects with a validation error. Staff (Role 3) can view but not create or edit vehicles. |

### UC-07: Log Vehicle Mileage

| Field | Detail |
|---|---|
| Use Case ID | UC-07 |
| Title | Log Vehicle Mileage |
| Primary Actor | Staff (Role 3), Manager (Role 2) |
| Preconditions | At least one active vehicle exists. |
| **Main Flow** | 1. User navigates to **Fleet → Mileage Logs**. 2. User selects a vehicle and enters the current odometer reading (`mileage_reading`) and optional trip notes. 3. System stores the log entry with `recorded_at = now()` and associates it with the user and vehicle. |
| **Alternate / Exception** | – |

### UC-08: Export Attendance CSV

| Field | Detail |
|---|---|
| Use Case ID | UC-08 |
| Title | Export Attendance Data as CSV |
| Primary Actor | Manager (Role 2), Administrator (Role 1) |
| Preconditions | User is authenticated. |
| **Main Flow** | 1. User navigates to `/attendance/export`. 2. System queries attendance records using lazy-collection processing. 3. System streams a CSV file containing attendance records (ordered by latest clock-in first) to the browser. |
| **Alternate / Exception** | 2a. Staff (Role 3) are not granted access to this endpoint. |

### UC-09: Create User Account

| Field | Detail |
|---|---|
| Use Case ID | UC-09 |
| Title | Create User Account |
| Primary Actor | Administrator (Role 1) for all roles; Manager (Role 2) for Staff accounts |
| Preconditions | Actor is logged into the appropriate panel. |
| **Main Flow** | 1. Actor navigates to the Users resource. 2. Actor creates a new user by providing: full name, unique phone number, password, and role. 3. System hashes the password and saves the user. The user can immediately log in using their phone number and password. |
| **Alternate / Exception** | 2a. Duplicate phone number: system rejects with a validation error. 2b. Manager attempts to create a Manager-or-above account: system blocks the action (role restricted). |
