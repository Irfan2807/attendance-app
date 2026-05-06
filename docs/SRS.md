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
| Backend Framework | Laravel 12 (PHP 8.3) |
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
| Clock In / Out | – | – | ✓ |
| View Own Attendance | ✓ | ✓ | ✓ |
| View All Attendance | ✓ | ✓ (staff only) | – |
| Approve / Reject Attendance | ✓ | ✓ | – |
| Create Staff Users | ✓ | ✓ | – |
| Create Manager Users | ✓ | – | – |
| Manage Sites | ✓ | View only | – |
| Manage Vehicles | ✓ | ✓ | View only |
| Log Mileage | ✓ | ✓ | ✓ |
| Export CSV | ✓ | ✓ | – |
| Reset Warning Counter | ✓ | – | – |

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
| FR-05 | The system shall allow Staff to record a clock-in event with a timestamp and associated site. |
| FR-06 | The system shall allow Staff to record a clock-out event for their active shift. |
| FR-07 | The system shall prevent a second clock-in if an active (unclosed) attendance record exists. |
| FR-08 | The system shall record the IP address at clock-in for verification purposes. |
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
| FR-11 | Managers shall be able to view all `pending` and `temporary` attendance records for staff under their supervision. |
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
| FR-26 | Managers and Admins shall be able to export filtered attendance data as a CSV file via the `/attendance/export` route. |

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
| Application | Laravel 12 (PHP 8.3) | Business logic, routing |
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
- The application requires PHP 8.3 or higher.
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
| Preconditions | Employee is logged in. |
| **Main Flow** | 1. Employee navigates to My Attendance. 2. Employee clicks Clock In. 3. System captures current timestamp and IP address. 4. System attempts to match IP to a registered active site. 5. If IP matches → status set to `approved`. If GPS matches → status set to `approved`. If group verification passes → status set to `approved`. Otherwise → status set to `pending` (awaiting manager approval). 6. System creates an attendance record. 7. System displays a success notification. |
| **Alternate / Exception** | 4a. IP does not match: verification_notes records "IP mismatch"; status = `pending`. |

### UC-02: Employee Clock Out

| Field | Detail |
|---|---|
| Use Case ID | UC-02 |
| Title | Employee Clock Out |
| Primary Actor | Staff (Role 3) |
| Preconditions | Employee has an active (open) attendance record. |
| **Main Flow** | 1. Employee navigates to My Attendance. 2. Employee clicks Clock Out. 3. System records the `clock_out_time`. 4. If shift was `approved`, new status = `completed`. If shift was `pending`, new status = `temporary`. 5. System displays a success notification. |
| **Alternate / Exception** | 3a. No active record found: Clock Out button is disabled. |

### UC-03: Manager Approves Attendance

| Field | Detail |
|---|---|
| Use Case ID | UC-03 |
| Title | Manager Approves Attendance |
| Primary Actor | Manager (Role 2) |
| Preconditions | One or more attendance records are in `pending` or `temporary` status. |
| **Main Flow** | 1. Manager navigates to Clock-In Approvals. 2. Manager selects a pending/temporary record. 3. Manager reviews the details (clock-in time, site, verification notes). 4. Manager clicks Approve (or Reject with required notes). 5. System sets status = `approved` or `rejected`, records `approved_by`, `approved_at`, and `approval_notes`. |
| **Alternate / Exception** | 4a. Manager tries to approve their own record: system blocks with an error. |

### UC-04: Safety Net Auto Clock-Out

| Field | Detail |
|---|---|
| Use Case ID | UC-04 |
| Title | Safety Net Auto Clock-Out |
| Primary Actor | System (triggered on dashboard load) |
| Preconditions | An attendance record has been open for 16 or more hours (configurable). |
| **Main Flow** | 1. User loads their dashboard (or any widget rehydration). 2. System finds open attendance records older than `ATTENDANCE_MAX_SHIFT_HOURS`. 3. System sets `clock_out_time = clock_in_time + max_shift_hours`. 4. System sets status = `temporary`. 5. System increments `incomplete_clock_out_count` for the user. 6. System creates an `AttendanceInfraction` record with `infraction_type = 'forgot_clock_out'`. |
