# Tap and Track – Software Requirements Specification (SRS)

**Version:** 2.0  
**Date:** September 14, 2026  
**Application:** Tap and Track – Attendance, Leave & Operations Management System  

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
This Software Requirements Specification (SRS) describes the functional and non-functional requirements for the Tap and Track attendance, leave management, and workforce operations system. It guides developers, testers, administrators, and stakeholders throughout the software lifecycle.

### 1.2 Scope
Tap and Track is a web-based enterprise workforce platform that automates:
- Employee clock-in and clock-out with automated GPS geolocation and IP verification.
- Leave management across Annual, Medical (MC), and Hospitalization leave with quota tracking, proof attachment, and smart working-day deductions.
- Malaysian Public Holidays synchronization from official API sources with state-based observance indicators.
- Fleet vehicle tracking, odometer logs, maintenance alerts, and road tax compliance scanners.
- Hierarchical approval governance eliminating peer approvals.
- Real-time in-app notification bells with 30-second Livewire polling.

### 1.3 Definitions, Acronyms, and Abbreviations

| Term | Definition |
|---|---|
| SRS | Software Requirements Specification |
| RBAC | Role-Based Access Control |
| GPS | Global Positioning System |
| IP | Internet Protocol address |
| SLA | Service Level Agreement (Approval turnaround time) |
| MC | Medical Certificate / Medical Leave |
| API | Application Programming Interface |
| FR | Functional Requirement |
| NFR | Non-Functional Requirement |

### 1.4 References
- Laravel 12 Documentation – https://laravel.com/docs
- Filament 3.2 Documentation – https://filamentphp.com/docs
- Malaysia Public Holidays API – https://malaysia-holiday.dydxsoft.my
- Tap and Track README.md

---

## 2. Overall Description

### 2.1 Product Perspective
Tap and Track is a unified workforce management application accessible via modern web browsers. It includes two Filament-powered portals: the **Admin Panel** (`/admin`) for executive management and system configuration, and the **Staff Portal** (`/staff`) for daily shifts, team supervision, and leave applications.

### 2.2 Product Functions (Summary)
- **Hero Shift Single-Card Interface**: One-tap clock in/out with background geolocation capture and friendly off-site location selection.
- **Safety Net System**: Shift auto-closure after 16 hours (`ATTENDANCE_MAX_SHIFT_HOURS`), recording safety infractions and warning counters.
- **Hierarchical Approvals**: Managers review assigned subordinates only; Directors and HR Executives review managers. Peer approvals and self-approvals are blocked.
- **Leave Quota & Balance Engine**: Annual, MC, and Hospitalization quotas tracked per user per calendar year. Smart working-day calculations automatically exclude weekends and Malaysian public holidays.
- **Malaysian Public Holidays Integration**: Synced calendar from Malaysia Public Holiday API with state filters and hover tooltips showing observing states.
- **Fleet & Road Tax Compliance**: Vehicle mileage logging, service interval calculation, and daily compliance scans alerting on road taxes expiring within 14 days.
- **In-App Notification Bell**: Topbar alert dropdown with 30s Livewire polling for leave submissions, approval outcomes, clock-in decisions, and fleet alerts.
- **Operational Analytics**: Managerial trend charts tracking daily workforce attendance and overtime hours, plus total monthly team hours formatted as `Xhrs Y mins`.

### 2.3 Operating Environment

| Component | Specification |
|---|---|
| Backend Framework | Laravel 12 (PHP 8.3+) |
| Admin & Staff UI | Filament 3.2, Livewire 3, Alpine.js |
| Frontend Build | Vite 7, Tailwind CSS 4 |
| Database | SQLite (default), MySQL / PostgreSQL compatible |
| Caching | File-based cache / Redis |
| Web Server | PHP built-in / Apache / Nginx |
| Browser Support | Chrome 100+, Firefox 100+, Edge 100+, Safari 15+ |

---

## 3. Stakeholders & User Classes

| User Class | Role Code | Description | Default Portal |
|---|---|---|---|
| **Director** | 0 | Executive oversight; reviews managers' leaves and shifts; global operations | `/admin` & `/staff` |
| **Administrator** | 1 | Full system configuration, user management, site setup, and raw audit logs | `/admin` |
| **Manager** | 2 | Team supervisor; manages assigned direct subordinates; reviews shifts and leaves | `/staff` |
| **Staff (Employee)** | 3 | Field technician / general employee; clocks in/out, applies for leaves | `/staff` |
| **HR Executive** | 4 | Human resources management; manages quotas, holidays, road tax, and approvals | `/admin` & `/staff` |

### 3.1 Roles & Permissions Matrix

| Feature | Director (0) | Admin (1) | Manager (2) | Staff (3) | HR Executive (4) |
|---|---|---|---|---|---|
| Clock In / Out (Hero Card) | ✓ | – | ✓ | ✓ | ✓ |
| View Own Attendance & Leaves | ✓ | ✓ | ✓ | ✓ | ✓ |
| Approve Subordinate Clock-Ins | ✓ (managers) | ✓ (global) | ✓ (subordinates) | – | ✓ (global) |
| Approve Subordinate Leaves | ✓ (managers) | ✓ (global) | ✓ (subordinates) | – | ✓ (global) |
| Manage Work Sites | ✓ | ✓ | View only | – | View only |
| Manage Public Holidays & Sync | ✓ | ✓ | View only | View only | ✓ |
| Manage Vehicles & Road Tax | ✓ | ✓ | ✓ | View only | ✓ |
| Log Vehicle Mileage | ✓ | – | ✓ | ✓ | ✓ |
| Export Attendance CSV | ✓ | ✓ | ✓ | – | ✓ |
| In-App Notification Bell | ✓ | ✓ | ✓ | ✓ | ✓ |

---

## 4. Functional Requirements

### 4.1 Authentication & Hierarchical RBAC
- **FR-01**: Authenticate users via unique phone number and password.
- **FR-02**: Enforce 5 system roles: Director (0), Admin (1), Manager (2), Staff (3), HR Executive (4).
- **FR-03**: Support explicit reporting hierarchy via `manager_id` foreign key on the `users` table.
- **FR-04**: Direct managers shall only access and approve records belonging to their assigned subordinates. Peer-manager approvals and self-approvals shall be rejected server-side.

### 4.2 Attendance Tracking & Hero Shift Interface
- **FR-05**: Provide a single-card "Hero Shift" interface with one-tap primary action (`Clock In` or `Clock Out`).
- **FR-06**: Capture GPS latitude/longitude automatically in the background on clock-in without forcing manual coordinates.
- **FR-07**: Provide off-site work selection allowing staff to pick an assigned active site or enter a client project name.
- **FR-08**: Evaluate verification: office IP match, GPS radius match, or group verification (5+ staff within 50m in last 2 hours).
- **FR-09**: Support attendance statuses: `pending`, `approved`, `rejected`, `temporary`, `completed`.
- **FR-10**: Support operational day shifts with configurable early arrival buffer (default 2 hours) ensuring early clock-ins map to today's shift.

### 4.3 Leave Management & Quota Deductions
- **FR-11**: Provide employee leave application supporting Annual Leave, Medical Leave (MC), and Hospitalization Leave.
- **FR-12**: Require medical certificate (MC) attachments for Medical and Hospitalization applications.
- **FR-12a**: Restrict medical certificate (MC) and leave attachment access via an authenticated streaming endpoint (`/leaves/{leave}/attachment`) adhering to Malaysia Personal Data Protection Act (PDPA) 2010. Files are kept on private storage (`storage/app/private`), accessible exclusively by the applicant, direct reporting supervisor, HR executives, and directors. Direct public URL access to file paths is strictly blocked.
- **FR-13**: Maintain `leave_quotas` per user per year, tracking allocated, used, and remaining balances.
- **FR-14**: Automatically calculate working days between start and end dates, strictly excluding weekend rest days and recognized Malaysian public holidays.
- **FR-15**: Prevent submission if the requested working days exceed the employee's remaining quota balance.

### 4.4 Malaysian Public Holidays Integration
- **FR-16**: Fetch and persist official Malaysian public holidays via artisan command `holidays:sync` or admin action.
- **FR-17**: Store nationwide and state-specific observances with official state code mapping.
- **FR-18**: Render reactive hover tooltips on holiday tables displaying the specific states observing each holiday.

### 4.5 In-App Notifications & Alerts
- **FR-19**: Provide topbar notification bell with 30-second live polling across Staff and Admin portals.
- **FR-20**: Dispatch alerts to managers when subordinates submit leaves or clock in requiring approval.
- **FR-21**: Dispatch confirmation alerts to staff when leave requests or clock-ins are approved or rejected.
- **FR-22**: Dispatch urgent compliance alerts when vehicles have road taxes expiring within 14 days or service overdue.

### 4.6 Safety Net & Disciplinary Tracking
- **FR-23**: Auto-close stale open shifts exceeding `ATTENDANCE_MAX_SHIFT_HOURS` (16 hours), set status to `temporary`, create an `AttendanceInfraction`, and increment `incomplete_clock_out_count`.
- **FR-24**: Display `Good Standing` indicator on the staff dashboard when an employee has 0 infractions and 0 monthly warnings.

### 4.7 Fleet Management & Vehicle Compliance
- **FR-25**: Track vehicles with registration plate, current mileage, next service threshold, and road tax expiry date.
- **FR-26**: Calculate service status: OK, Due Soon (≤ 500 km remaining), or Overdue.
- **FR-27**: Provide artisan command `fleet:check-alerts` scheduled daily at 08:00 AM to dispatch compliance notices.

### 4.8 Monthly Timesheets & Payroll Reports
- **FR-28**: Aggregate monthly working hours, regular hours (capped at 8-hour workday), overtime hours, and approved leave days for active employees.
- **FR-29**: Provide an on-demand Monthly Payroll & Timesheet Hub (`/attendance/monthly-report`) with month/year selector, department filters, and company-wide KPI metrics.
- **FR-30**: Generate an audit-ready individual A4 Monthly Attendance Slip (`/attendance/monthly-slip/{user}`) featuring daily logs, overtime calculations, and formal signature blocks for employee and HR certification.
- **FR-31**: Generate a multi-page compiled All-Staff Payroll Bundle (`/attendance/monthly-bundle`) formatted with page-breaks for 1-click printing or PDF export.
- **FR-32**: Stream a payroll-ready summary CSV (`/attendance/monthly-summary-csv`) with formula injection sanitization (CWE-1236).

---

## 5. Non-Functional Requirements

### 5.1 Performance & Scalability
- **NFR-01**: Dashboard widgets shall load within 3 seconds using lazy loading (`#[Lazy]`).
- **NFR-02**: Complex analytics and attendance states shall be cached for 1–2 minutes with automatic invalidation upon status transitions.
- **NFR-03**: Large attendance CSV exports shall process lazily in chunks to maintain low memory usage.

### 5.2 Security & Data Integrity
- **NFR-04**: Passwords shall be hashed using bcrypt.
- **NFR-05**: All forms shall be protected by CSRF tokens.
- **NFR-06**: Concurrency write locks (`lockForUpdate()`) and transactions shall prevent duplicate clock-in race conditions.
- **NFR-09**: Medical certificate (MC) files and private documents shall never reside in public web directories. File transfers shall be authenticated, streamed with strict role-based authorization (RBAC), and protected from guessing or scraping attacks.

### 5.3 Usability & Design Consistency
- **NFR-07**: Mobile-first responsive layouts with zero unstyled wireframe collapses.
- **NFR-08**: Color-coded badges and actions adhering to Filament semantic design tokens.

---

## 6. System Architecture

```
┌───────────────────────────────────────────────────────────┐
│                      Presentation                         │
│  Filament Admin (/admin)   │   Filament Staff (/staff)     │
├───────────────────────────────────────────────────────────┤
│                   Application Services                    │
│ AttendanceWindowService   │ AttendanceVerificationService │
│ AttendanceAnalyticsService│ AttendanceMetricsService      │
│ HolidayService            │ AppNotificationService        │
├───────────────────────────────────────────────────────────┤
│                      Data Layer                           │
│ Eloquent ORM (Models, Relationships, Observers, Scopes)   │
├───────────────────────────────────────────────────────────┤
│                   Database & Cache                        │
│ SQLite / MySQL Compatible Database  │ File / Redis Cache │
└───────────────────────────────────────────────────────────┘
```

---

## 7. Database Design

### 7.1 Entity Relationships Summary
- `User` has many `Attendances`, `LeaveRequests`, `LeaveQuotas`, `AttendanceInfractions`, `MileageLogs`.
- `User` (Manager) has many subordinate `Users` via `manager_id`.
- `Vehicle` has many `MileageLogs`.
- `Attendance` belongs to `User` and optional `Site`.

### 7.2 Core Tables

**Table: `users`**
`id`, `name`, `phone`, `password`, `role` (1=Director/SuperAdmin, 2=Manager, 3=Staff, 4=HRExecutive), `manager_id` (FK), `is_active` (boolean), `incomplete_clock_out_count`, timestamps.

**Table: `attendances`**
`id`, `user_id` (FK), `site_name`, `latitude`, `longitude`, `status` (`pending`, `approved`, `rejected`, `temporary`, `completed`), `clock_in_time`, `clock_out_time`, `verification_notes`, `approval_notes`, `approved_by` (FK), `approved_at`, timestamps.

**Table: `leave_requests`**
`id`, `user_id` (FK), `leave_type` (`annual`, `medical`, `hospitalization`), `start_date`, `end_date`, `days_count`, `reason`, `status` (`pending`, `approved`, `rejected`), `attachment_path`, `approved_by` (FK), `approved_at`, `rejection_reason`, timestamps.

**Table: `leave_quotas`**
`id`, `user_id` (FK), `year`, `annual_quota`, `annual_used`, `medical_quota`, `medical_used`, `hospitalization_quota`, `hospitalization_used`, timestamps.

**Table: `public_holidays`**
`id`, `name`, `holiday_date`, `year`, `is_nationwide` (boolean), `states` (json array of state codes), timestamps.

**Table: `notifications`**
`id` (UUID), `type`, `notifiable_type`, `notifiable_id`, `data` (json), `read_at`, timestamps.

**Table: `vehicles`**
`id`, `name`, `numberplate`, `current_mileage`, `next_service_mileage`, `road_tax_expiry_date`, `is_active`, `notes`, timestamps.

**Table: `mileage_logs`**
`id`, `vehicle_id` (FK), `user_id` (FK), `mileage_reading`, `recorded_at`, `notes`, timestamps.

**Table: `attendance_infractions`**
`id`, `user_id` (FK), `attendance_id` (FK), `infraction_type`, `auto_clock_out_time`, `notes`, timestamps.

**Table: `sites`**
`id`, `name`, `latitude`, `longitude`, `ip_address`, `radius_meters`, `is_active`, timestamps.

---

## 8. External Interface Requirements

- **Malaysia Public Holidays API**: HTTP GET `https://malaysia-holiday.dydxsoft.my/api/v1/holidays?year={year}` used to seed and synchronize national/state holidays.
- **Browser Geolocation API**: HTML5 `navigator.geolocation` capturing client latitude and longitude on tap.

---

## 9. Constraints & Assumptions

- Static office IP whitelists configured per active work site.
- Unique phone numbers serve as the primary authentication identifier.
- Server deployed over HTTPS in production to allow HTML5 Geolocation API execution.

---

## 10. Appendix A – Use Case Descriptions

### UC-01: Employee Single-Tap Clock In
- **Actor**: Staff (Role 3), Manager (Role 2)
- **Flow**: User navigates to Staff Dashboard. User taps **Clock In**. System triggers background geolocation capture, matches IP/GPS/Group verifications, and creates an attendance record.

### UC-02: Employee Clock Out
- **Actor**: Staff (Role 3), Manager (Role 2)
- **Flow**: User clicks **Clock Out**. Active shift is closed with current timestamp, and status updates to `completed` (if pre-verified) or `temporary` (if pending manager review).

### UC-03: Subordinate Clock-In Approval (Hierarchical)
- **Actor**: Manager (Role 2), Director / Super Admin (Role 1), HR Executive (Role 4)
- **Flow**: Supervisor reviews pending shift check-ins of assigned subordinates. Self-approval and peer-manager approvals are blocked. Approver confirms or rejects with notes. Staff receives an in-app notification.

### UC-04: Leave Application with Smart Deduction
- **Actor**: Staff, Manager
- **Flow**: User opens **Apply / My Leave**. User selects leave type, start date, and end date. System automatically queries weekend calendars and public holidays, calculates exact working days, verifies remaining quota, and records the request. Assigned supervisor receives a notification.

### UC-05: Fleet Compliance Scanner (`fleet:check-alerts`)
- **Actor**: System (Daily 08:00 AM Cron)
- **Flow**: System iterates all active vehicles, evaluates road tax expiry dates and service mileages, and sends in-app notifications for vehicles requiring attention.
