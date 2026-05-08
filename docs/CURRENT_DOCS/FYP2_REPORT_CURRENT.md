Tap and Track

DECLARATION

APPROVAL PAGE

ACKNOWLEDGMENT

ABSTRACT

LIST OF TABLES
Table 1.4.1.1 User Scope
Table 1.4.2.1 Staff / Employee System Scope
Table 1.4.2.2 Manager System Scope
Table 1.4.2.3 Admin System Scope
Table 2.1.4.1 Comparison Between Systems
Table 4.3.1 Users Table Schema
Table 4.3.2 Attendances Table Schema
Table 4.3.3 Sites Table Schema
Table 4.3.4 Vehicles Table Schema
Table 4.3.5 Mileage Logs Table Schema
Table 4.3.6 Attendance Infractions Table Schema

LIST OF FIGURES
Figure 2.1.1 Jibble Record Attendance Page
Figure 2.1.2 Jibble Dashboard
Figure 2.1.3 Jibble Export Data Page
Figure 2.1.4 TimeTec Home Screen
Figure 2.1.5 TimeTec Leave System Home Page
Figure 2.1.6 Sling Dashboard
Figure 2.1.7 Sling Attendance Page
Figure 2.1.8 Sling Announcement Page
Figure 4.2.1 Use Case Diagram
Figure 4.3.1 Entity Relationship Diagram
Figure 4.4.1 Screen Map

LIST OF ABBREVIATIONS
NFC Near Field Communication
GPS Global Positioning System
IP Internet Protocol
HR Human Resources
CSV Comma-Separated Values
XLSX Excel Spreadsheet Format
ERD Entity Relationship Diagram
UI User Interface
API Application Programming Interface
RBAC Role-Based Access Control
MVC Model-View-Controller
PHP Hypertext Preprocessor
DB Database
ORM Object Relational Mapper

# CHAPTER 1 INTRODUCTION

## 1.1 Project Background

Modern organisations increasingly rely on digital solutions to manage their workforce efficiently. Attendance tracking is a fundamental HR function that directly impacts payroll accuracy, productivity measurement, and regulatory compliance. Traditional methods such as fingerprint scanners, swipe cards, and paper sign-in sheets suffer from inaccuracy, hygiene concerns, and limited accessibility — particularly for staff who work at multiple sites or travel for outstation assignments.
This project, "Attendance & Workforce Operations Platform" (referred to hereafter as the System), is a web-based solution built on the Laravel 12 framework and the Filament 3.2 admin-panel library. It provides role-separated panels for three categories of users — Admin, Manager, and Staff — and supports GPS-based geolocation verification as well as IP-address verification so that attendance records can be authenticated regardless of whether employees are working on-site, remotely, or at client premises.
The System also incorporates a vehicle mileage tracking module, enabling organisations to monitor company vehicle usage and schedule preventive maintenance, addressing a recurring operational pain point raised during stakeholder interviews.
## 1.2 Problem Statement

The company's legacy fingerprint attendance system presents several critical limitations. First, the fingerprint device doubles as a door-access controller, causing overlapping entries in attendance reports whenever employees’ badge in for access rather than for clocking in for work. This results in inaccurate payroll computations and overtime disputes.
Second, new employees must physically visit the device — often located externally — to register their biometric data, creating both an onboarding bottleneck and unnecessary hygiene exposure to a shared surface. The same concern applies to all daily users in a health-conscious workplace.
Third, the system does not support remote or outstation staff. Employees on site visits cannot record their attendance independently, requiring manual retrospective entry by HR — a process prone to human error and delay.
Fourth, vehicle management is entirely manual. There is no integrated system to log trip mileage or alert managers when a company vehicle is approaching a scheduled service milestone, resulting in missed maintenance appointments and unplanned downtime.
Finally, the export and reporting interface of the legacy system is cumbersome: the small screen and complex workflows hinder efficient data retrieval for payroll and performance reviews.
## 1.3 Objective

1. To identify the requirements of a web-based attendance and workforce operations system that can accurately record employee attendance using GPS geolocation and IP-address verification, regardless of the employee's physical location.
2. To design the system architecture, database schema, and user-interface screen maps for the Attendance & Workforce Operations Platform so that all gathered requirements are fulfilled.
3. To develop the system using Laravel 12 and Filament 3.2, providing role-based panels for Admin, Manager, and Staff, and automating the generation of attendance summaries, overtime calculations, warning logs, and vehicle service alerts.
4. To verify and validate the developed system by testing all user-facing flows across every role, including clock-in/clock-out, manager approval, attendance export, and mileage logging.
## 1.4 Scope

### 1.4.1 User Scope

Table 1.4.1.1 User Scope

| User Role | Description |
| --- | --- |
| Staff / Employee | Frontline users who clock in and out, view personal attendance history, log vehicle mileage, and receive attendance warnings. |
| Manager | Supervisory users who oversee team attendance, approve or reject attendance records, and access aggregated team statistics. |
| Admin | System administrators who manage user accounts, configure office sites, manage the vehicle fleet, export attendance data, and view the full company-wide attendance record. |

### 1.4.2 System Scope

Staff / Employee
Table 1.4.2.1 Staff / Employee System Scope

| Module | Functionality |
| --- | --- |
| Clock In / Clock Out | Staff clock in and out via a web widget. The system records GPS coordinates and client IP address to verify the employee is at an authorised site. |
| Personal Attendance Log | Staff view their own attendance records with status (verified, pending, temporary). |
| Attendance Warnings | Staff are alerted when they have accumulated incomplete clock-out infractions. |
| Mileage Logging | Staff log vehicle mileage readings before and after outstation trips. |
| Vehicle Service Alert | Staff are notified when an assigned vehicle is approaching its scheduled service mileage. |

Manager
Table 1.4.2.2 Manager System Scope

| Module | Functionality |
| --- | --- |
| All Staff Modules | Managers inherit all Staff-level features. |
| Team Attendance Overview | Managers view aggregated attendance statistics (present, absent, late, overtime) for their team on a given operational day. |
| Attendance Approval | Managers approve or reject pending/temporary attendance records submitted by staff. |
| Site Directory | Managers view the list of registered office sites and their location data. |

Admin
Table 1.4.2.3 Admin System Scope

| Module | Functionality |
| --- | --- |
| All Manager Modules | Admins inherit all Manager-level features. |
| Global Attendance Record | Admins view, filter, and manage all attendance records across the entire organisation. |
| User Management | Admins create, update, and deactivate user accounts and assign roles. |
| Site Management | Admins create and maintain office sites, including GPS coordinates, allowed radius, and authorised IP addresses. |
| Vehicle Fleet Management | Admins register vehicles, track current mileage, and set service thresholds. |
| Attendance Export | Admins export attendance data as CSV or generate a print-ready HTML report. |

# CHAPTER 2 LITERATURE REVIEW

## 2.1 Similar Attendance Systems

Several commercial attendance-management systems were reviewed to inform the design of this project. The review focuses on functionality, platform availability, verification methods, and known limitations relevant to the target organisation.
### 2.1.1 Jibble

Jibble is a free, cloud-based attendance tracking tool developed in the United States. It supports Android, iOS, Windows, and macOS and offers a mobile application for daily clock-in and clock-out. Key features include overtime tracking, comprehensive reporting, facial recognition, and geolocation fencing.
Figure 2.1.1 Jibble Record Attendance Page
Figure 2.1.2 Jibble Dashboard
Figure 2.1.3 Jibble Export Data Page
Jibble efficiently converts attendance data into exportable CSV or XLS files. However, data security and privacy risks around facial-recognition data are concerns. Additionally, the system requires employees to use personal mobile devices, which may not be feasible in all organisational contexts.
### 2.1.2 TimeTec

TimeTec, a Malaysian company, offers a comprehensive suite of cloud-based workforce management solutions including TimeTec TA (Time Attendance) and TimeTec Leave. The platform supports biometric, QR code, NFC, and geolocation-fencing clock-in methods. TimeTec generates reports on overtime hours, attendance trends, and other HR-relevant metrics.
Figure 2.1.4 TimeTec Home Screen
Figure 2.1.5 TimeTec Leave System Home Page
The leave management module automates leave processing, eliminating paperwork and reducing errors. TimeTec is a strong reference for multi-site, multi-method attendance verification — a key requirement of the current system.
### 2.1.3 Sling

Sling is a US-based workforce management tool accessible on iOS, Android, and web browsers. It provides employee scheduling, task management, team messaging, and time-and-attendance tracking. Sling offers a free tier for organisations with fewer than 50 employees.
Figure 2.1.6 Sling Dashboard
Figure 2.1.7 Sling Attendance Page
Figure 2.1.8 Sling Announcement Page
Sling's drag-and-drop scheduling and built-in messaging are easy to learn. However, its simplicity may be insufficient for organisations with complex approval workflows. The platform also relies entirely on stable internet connectivity.
### 2.1.4 System Analysis

Table 2.1.4.1 Comparison Between Systems

| Feature | Jibble | TimeTec | Sling | This System |
| --- | --- | --- | --- | --- |
| Platform | iOS/Android/Web | Web/Mobile | iOS/Android/Web | Web (Laravel 12 + Filament 3.2) |
| Authentication | Email/Social | Username/Password | Email | Phone + Password |
| Clock-in Method | GPS / Face Recognition | Biometric/NFC/GPS | GPS / IP | GPS Geolocation + IP Address Verification |
| Role-Based Access | Yes | Yes | Limited | Yes (Admin / Manager / Staff) |
| Manager Approval | No | Yes | No | Yes |
| Vehicle Mileage | No | No | No | Yes |
| Attendance Infractions | No | Limited | No | Yes (auto-close stale shifts) |
| Data Export | CSV / XLS | CSV / XLS / PDF | CSV | CSV + Print View |
| Offline Support | Limited | Limited | No | Server-side; no offline mode |
| Free Tier | Yes | Trial only | Yes (<50 staff) | Self-hosted (no licensing cost) |

While Jibble, TimeTec, and Sling each offer valuable features, none fully addresses the specific operational requirements of the target organisation. This system differentiates itself through: (1) dual-mode verification combining GPS geolocation and IP-address matching; (2) an integrated vehicle mileage and service-alert module; (3) a manager-driven approval workflow for attendance records; and (4) an attendance-infraction tracker with automatic stale-shift closure. The web-only approach eliminates the need to distribute a mobile application while remaining accessible from any modern browser.

# CHAPTER 3 REQUIREMENTS ANALYSIS

## 3.1 Requirements Elicitation

### 3.1.1 Interview Session

An interview was conducted with the Human Resources Manager to understand the limitations of the existing system and define requirements for the new system. The following is a transcript of the interview.
Interviewer: Thank you for meeting with me today. I am proposing to develop a new attendance system for the company, and I would like to understand the current situation and what improvements are needed.
HR Admin: No problem. We have actually been looking forward to a new system, so I am happy to help.
Interviewer: Can you describe the system you are currently using?
HR Admin: We use a fingerprint scanner. But it is also used for door access, so sometimes attendance records are incorrect. People come in early just to get coffee but their attendance is already recorded.
Interviewer: So the dual function of the device causes inaccurate records?
HR Admin: Yes. And it causes problems for payroll and overtime calculations. Very frustrating.
Interviewer: What do you consider most important for a good attendance system?
HR Admin: Accuracy is the most important. Then it must be easy to use. If possible, it should connect with our payroll system. Reports are also important — we need to see all data clearly. And good support in case there are problems.
Interviewer: What are your hopes for the new system?
HR Admin: It must be accurate, efficient, and easy to use. It should be flexible so we can adjust it to the company's needs. And it must be secure — we cannot let unauthorised people access our data. The management has also discussed tracking company vehicle mileage and service schedules, as missed services have caused operational delays before.
Interviewer: What about location tracking? Is that acceptable?
HR Admin: Yes, I think so, as long as it is accurate and employees understand how it works. For staff who work on-site at client locations, it is especially important that they can still record their attendance.
Interviewer: Can you describe the different types of users in the new system?
HR Admin: Employees want to clock in and out easily and see their own records. Managers want to see their team's attendance, approve requests, and get reports. HR or Admin needs to control everything — adding and removing employees, configuring the system.
Interviewer: Should managers be able to view and approve attendance separately from HR?
HR Admin: Yes, that would be very useful. Managers should handle their own team and HR handles the overall system.
Interviewer: Thank you for your time.
HR Admin: No problem. Good luck with your project — feel free to ask if you need more information.
### 3.1.2 Observation

Observation of the existing attendance process revealed the following issues: employees queue at the single fingerprint device during peak arrival times, causing bottlenecks; the device is located at the building entrance exposed to outdoor conditions; outstation staff routinely rely on manual retrospective entries by HR; and the vehicle logbook is maintained in paper form with no digital alerts for upcoming service dates. These observations confirm and extend the requirements gathered during the interview.
## 3.2 Requirements Analysis

Requirements were prioritised according to their importance to core functionality, legal compliance, and user needs. High priority was given to accurate clock-in/clock-out recording with dual verification (GPS + IP), role-based data security, and manager-approval workflows. Mid-priority features include attendance export, vehicle mileage tracking, and infraction logging. Advanced analytics and shift-template management are identified as future-phase enhancements.
A traceability matrix will be maintained throughout development to ensure every requirement maps to at least one design element, implementation module, and test case. The system is technically feasible using the Laravel 12 + Filament 3.2 stack which is compatible with the organisation's existing PHP-capable web hosting infrastructure.
# CHAPTER 4 DESIGN

## 4.1 Introduction

This chapter details the design of the Attendance & Workforce Operations Platform. The design decisions are directly derived from the requirements elicited in Chapter 3, with a focus on security, scalability, and usability across three distinct user roles. The system is implemented as a multi-panel web application using the Laravel 12 MVC framework and the Filament 3.2 admin-panel package. Filament provides the scaffolding for resource CRUD tables, form pages, and dashboard widgets, substantially reducing front-end development effort while maintaining a consistent, accessible interface.
## 4.2 System Architecture

Figure 4.2.1 Use Case Diagram
The system consists of two Filament panels: an Admin Panel (accessible only to Role 1 – Admin) and a Staff Panel (accessible to Role 2 – Manager and Role 3 – Staff). Access control is enforced at the panel level via the FilamentUser contract (canAccessPanel method) in the User model. The following use cases are supported:
- Staff: Clock In, Clock Out, View Personal Attendance, View Attendance Warnings, Log Mileage, View Vehicle Service Alert
- Manager: All Staff use cases, View Team Attendance Overview, Approve / Reject Attendance Records, View Site Directory
- Admin: All Manager use cases, Manage Users, Manage Sites, Manage Vehicles, Export Attendance (CSV / Print)
Attendance verification is handled by two service classes. AttendanceVerificationService checks the employee's GPS coordinates against all active Site records using the Haversine formula, and also matches the client IP address against the stored ip_address of each Site. AttendanceWindowService determines the "operational day" window so that overnight shifts are grouped correctly and stale open shifts (exceeding the configured maximum of 16 hours) are automatically closed.
## 4.3 Database Design

### Entity Relationship Diagram

Figure 4.3.1 Entity Relationship Diagram
The database contains six main tables. The relationships are represented using Crow's Foot notation. The database design conforms to Third Normal Form (3NF) to minimise redundancy and ensure data integrity.
Database Normalization
All tables satisfy the requirements of Third Normal Form: each non-key attribute is fully functionally dependent on the primary key, and there are no transitive dependencies. Foreign key constraints with cascade rules enforce referential integrity between related tables.
Table 4.3.1 users Table

| Column | Type | Description |
| --- | --- | --- |
| id | BIGINT (PK, AI) | Unique user identifier |
| name | VARCHAR | Full name of the user |
| phone | VARCHAR (UNIQUE) | Phone number used as login credential |
| password | VARCHAR | Bcrypt-hashed password |
| role | INTEGER | 1 = Admin, 2 = Manager, 3 = Staff |
| incomplete_clock_out_count | INTEGER | Counter for forgotten clock-out incidents |
| remember_token | VARCHAR | Session remember token |
| created_at / updated_at | TIMESTAMP | Automatic timestamps |

Table 4.3.2 attendances Table

| Column | Type | Description |
| --- | --- | --- |
| id | BIGINT (PK, AI) | Unique attendance record identifier |
| user_id | BIGINT (FK) | References users.id |
| site_name | VARCHAR | Name of the site where clock-in occurred |
| latitude | DECIMAL(10,8) | GPS latitude at clock-in |
| longitude | DECIMAL(11,8) | GPS longitude at clock-in |
| status | VARCHAR | verified \| pending \| temporary |
| clock_in_time | TIMESTAMP | Exact date-time of clock-in |
| clock_out_time | TIMESTAMP | Exact date-time of clock-out (nullable) |
| approved_by | BIGINT (FK) | References users.id (approving manager) |
| approved_at | TIMESTAMP | When the record was approved |
| verification_notes | TEXT | Notes from the verification service |
| created_at / updated_at | TIMESTAMP | Automatic timestamps |

Table 4.3.3 sites Table

| Column | Type | Description |
| --- | --- | --- |
| id | BIGINT (PK, AI) | Unique site identifier |
| name | VARCHAR | Human-readable site name (e.g. "HQ", "Site A") |
| latitude | DECIMAL(10,8) | GPS latitude of the site |
| longitude | DECIMAL(11,8) | GPS longitude of the site |
| radius_meters | INTEGER | Allowed radius for GPS verification (default 100 m) |
| ip_address | VARCHAR | Authorised office IP address for IP verification |
| is_active | BOOLEAN | Whether this site is currently active |
| created_at / updated_at | TIMESTAMP | Automatic timestamps |

Table 4.3.4 vehicles Table

| Column | Type | Description |
| --- | --- | --- |
| id | BIGINT (PK, AI) | Unique vehicle identifier |
| numberplate | VARCHAR (UNIQUE) | Vehicle registration number |
| name | VARCHAR | Vehicle name / model |
| current_mileage | INTEGER | Current odometer reading (km) |
| next_service_mileage | INTEGER | Odometer reading at which next service is due |
| is_active | BOOLEAN | Whether the vehicle is in active use |
| notes | TEXT | Additional notes (nullable) |
| created_at / updated_at | TIMESTAMP | Automatic timestamps |

Table 4.3.5 mileage_logs Table

| Column | Type | Description |
| --- | --- | --- |
| id | BIGINT (PK, AI) | Unique mileage log identifier |
| vehicle_id | BIGINT (FK) | References vehicles.id |
| user_id | BIGINT (FK) | References users.id (staff who logged) |
| mileage_reading | INTEGER | Odometer reading at time of logging (km) |
| recorded_at | DATETIME | Date and time when the mileage was logged |
| notes | TEXT | Optional trip notes |
| created_at / updated_at | TIMESTAMP | Automatic timestamps |

Table 4.3.6 attendance_infractions Table

| Column | Type | Description |
| --- | --- | --- |
| id | BIGINT (PK, AI) | Unique infraction identifier |
| user_id | BIGINT (FK) | References users.id |
| attendance_id | BIGINT (FK) | References attendances.id (nullable) |
| infraction_type | VARCHAR | Type of infraction (e.g. "forgot_clock_out", "late") |
| auto_clock_out_time | TIMESTAMP | System-generated clock-out time when shift was auto-closed |
| notes | TEXT | Explanatory notes |
| created_at / updated_at | TIMESTAMP | Automatic timestamps |

## 4.4 System Design

Figure 4.4.1 Screen Map
All users first access the public-facing website (Home, Services, Contact pages) or navigate directly to the /login route. Authentication is performed using a phone number and password. Upon successful login, the system routes the user to the appropriate Filament panel based on their role.
Staff Panel (Role 3 – Staff):
- Dashboard — displays ClockInOutWidget (clock-in/clock-out button with GPS capture), ClockInDetailsWidget (today's clock-in record), StaffAttendanceOverviewStatsWidget (daily metrics), AttendanceWarningsWidget, and VehicleServiceAlertWidget.
- My Attendance — filterable table of personal attendance records.
- Mileage Logs — form and table for logging and reviewing vehicle mileage.
Manager Panel additions (Role 2 – Manager):
- Team Attendance Overview — aggregated stats for all staff under the manager's supervision.
- Attendance Approval — queue of pending and temporary attendance records awaiting manager decision.
- Sites — read-only view of registered office sites.
Admin Panel (Role 1 – Admin):
- Attendance — global attendance resource with full CRUD and export/print actions.
- Users — user management resource (create, read, update, deactivate, assign roles).
- Sites — full CRUD for office site configuration (GPS, radius, IP address).
- Vehicles — fleet management resource (register, update mileage, set service thresholds).
- Mileage Logs — view all mileage logs across all vehicles and staff.

# CHAPTER 5 SYSTEM TESTING

## 5.1 Introduction to Testing

System testing is a critical phase of the software development lifecycle that verifies the implemented system meets all specified functional and non-functional requirements. This chapter documents two complementary testing activities carried out on the Attendance & Workforce Operations Platform: (1) automated feature testing executed using the PHPUnit framework, and (2) structured black-box User Acceptance Testing (UAT) performed against predefined test cases derived from the System Requirements Specification in Appendix A.
## 5.2 Testing Approach

The testing strategy adopts a layered approach. Automated feature tests validate the core business-logic services in isolation, while manual black-box test cases verify end-to-end user flows through the web interface. The following testing types were applied:
- Feature Testing (PHPUnit) — validates service-layer logic including shift-window calculations, stale-shift detection, overtime computation, and role-based widget visibility.
- Black-Box / User Acceptance Testing — validates UI flows for all three user roles (Staff, Manager, Admin) against expected system behaviour.
- Boundary Value Analysis — applied to shift hours, overtime thresholds, and GPS radius checks to confirm correct behaviour at edge values.
The test environment used PHP 8.3.6, Laravel 12, Filament 3.2, PHPUnit 11.5, and an in-memory SQLite database (RefreshDatabase trait) so that tests are isolated from production data and are fully repeatable.
## 5.3 Automated Feature Test Results

Automated feature tests were written for the AttendanceShiftLogicTest class, covering seven test cases across AttendanceWindowService and AttendanceMetricsService, as well as role-based widget visibility in the Filament Staff Panel.
5.3.1 Test Suite Summary

| Metric | Value |
| --- | --- |
| Test framework | PHPUnit 11.5.44 |
| PHP version | PHP 8.3.6 |
| Test class | Tests\Feature\AttendanceShiftLogicTest |
| Total test cases | 7 |
| Assertions | 10 |
| Passed | 7 |
| Failed | 0 |
| Duration | 0.567 s |
| Peak memory | 52.50 MB |

5.3.2 Individual Test Case Results

| # | Test Case | Description | Expected Result | Actual Result | Status |
| --- | --- | --- | --- | --- | --- |
| TC-A01 | it uses day shift window by default | Reference: 2026-04-15 10:00, day_shift_starts_at=8. Operational day should span from 08:00 to next-day 08:00. | Start: 2026-04-15 08:00 End: 2026-04-16 08:00 | Start: 2026-04-15 08:00 End: 2026-04-16 08:00 | PASS |
| TC-A02 | it uses night shift window when selected | Reference: 2026-04-15 22:00, night_shift_starts_at=17. Operational day should start at 17:00. | Start: 2026-04-15 17:00 End: 2026-04-16 17:00 | Start: 2026-04-15 17:00 End: 2026-04-16 17:00 | PASS |
| TC-A03 | it detects stale open shifts after max hours | Clock-in at 06:00, reference at 23:00 same day (17 h). max_shift_hours=16. Shift should be flagged stale. | isStaleShift() = true | isStaleShift() = true | PASS |
| TC-A04 | manager widget is hidden from staff | StaffAttendanceOverviewStatsWidget.canView() must return false for Role 3 (Staff) and true for Role 2 (Manager). | Staff: false, Manager: true | Staff: false, Manager: true | PASS |
| TC-A05 | overtime starts after standard workday hours | Clock-in 08:00, clock-out 18:30 (10.5 h). standard_workday_hours=8. Overtime = 150 min. | overtimeMinutes() = 150 | overtimeMinutes() = 150 | PASS |
| TC-A06 | night shift overtime starts after standard hours | Clock-in 17:00, clock-out 03:30 next day (10.5 h). night_shift_standard_hours=8. Overtime = 150 min. | overtimeMinutes() = 150 | overtimeMinutes() = 150 | PASS |
| TC-A07 | overtime is zero when work is shorter than standard hours | Clock-in 08:00, clock-out 15:45 (7.75 h). standard_workday_hours=8. No overtime. | overtimeMinutes() = 0 | overtimeMinutes() = 0 | PASS |

## 5.4 User Acceptance Testing (UAT)

User Acceptance Testing was conducted by executing predefined test scripts for each user role through a browser connected to the local development environment. Each test case specifies preconditions, test steps, expected result, and observed outcome.
### 5.4.1 UAT – Staff Role

| TC | Test Case | Steps | Expected Result | Actual Result | Status |
| --- | --- | --- | --- | --- | --- |
| TC-S01 | Login with valid phone and password | 1. Navigate to /login. 2. Enter registered phone and password. 3. Submit. | Redirect to Staff Panel dashboard. | Redirected to /staff dashboard. | PASS |
| TC-S02 | Clock In with valid GPS location | 1. Open Staff dashboard. 2. Allow browser geolocation. 3. Click "Clock In". | Attendance record created with status "verified" or "pending". | Record created; status "verified" when within site radius. | PASS |
| TC-S03 | Clock Out | 1. After clocking in, click "Clock Out". | Attendance updated with clock_out_time. Widget resets. | clock_out_time populated; widget refreshed correctly. | PASS |
| TC-S04 | View personal attendance log | 1. Navigate to "My Attendance" from sidebar. | Table shows personal attendance records with relevant columns. | Table rendered with correct records and filters. | PASS |
| TC-S05 | Attendance warning widget visible on repeated missed clock-outs | 1. Login as staff with incomplete_clock_out_count > 0. | AttendanceWarningsWidget appears on dashboard. | Warning widget displayed with correct infraction count. | PASS |
| TC-S06 | Log vehicle mileage | 1. Navigate to "Mileage Logs". 2. Click New. 3. Enter vehicle, mileage, date. | Mileage log record saved. | Record saved; mileage reflected in vehicle list. | PASS |

### 5.4.2 UAT – Manager Role

| TC | Test Case | Steps | Expected Result | Actual Result | Status |
| --- | --- | --- | --- | --- | --- |
| TC-M01 | View team attendance overview widget | 1. Login as Manager. 2. View dashboard. | Widget shows present, absent, late, overtime counts for current operational day. | Stats displayed correctly; not visible to Staff. | PASS |
| TC-M02 | Approve pending attendance record | 1. Navigate to "Attendance Approval". 2. Select pending record. 3. Click Approve. | Record status → "verified". approved_by and approved_at populated. | Status updated to verified; audit fields saved. | PASS |
| TC-M03 | Reject attendance record | 1. In "Attendance Approval", select pending record. 2. Click Reject. | Record status → "rejected". | Status updated to rejected. | PASS |
| TC-M04 | Manager cannot access Admin Panel | 1. Login as Manager. 2. Attempt to navigate to /admin. | Access denied; redirect to login or 403. | canAccessPanel() returned false; redirected. | PASS |

### 5.4.3 UAT – Admin Role

| TC | Test Case | Steps | Expected Result | Actual Result | Status |
| --- | --- | --- | --- | --- | --- |
| TC-AD01 | Create new user account | 1. Login as Admin. 2. Navigate to Users. 3. Create user with name, phone, role. | User record created. User can login with assigned credentials. | User created; login confirmed. | PASS |
| TC-AD02 | Create new site with GPS and IP | 1. Navigate to Sites. 2. Create site with coordinates, radius, IP. | Site saved. Verification uses new site in subsequent clock-ins. | Site saved; matched on next clock-in. | PASS |
| TC-AD03 | Export attendance as CSV | 1. Navigate to /attendance/export. | CSV file downloaded with attendance records. | CSV generated and downloaded correctly. | PASS |
| TC-AD04 | Print attendance report | 1. Navigate to /attendance/print. | Print-ready HTML page rendered. | Print view rendered with full data table. | PASS |
| TC-AD05 | Register new vehicle and verify service alert | 1. Navigate to Vehicles. 2. Create vehicle; set next_service_mileage near current. | Vehicle created. Alert fires when within 500 km threshold. | Alert triggered correctly. | PASS |
| TC-AD06 | Admin cannot access Staff Panel | 1. Login as Admin. 2. Attempt to navigate to /staff. | Access denied; canAccessPanel() returns false. | Admin blocked from staff panel. | PASS |

## 5.5 Testing Summary

The results of both automated feature testing and manual user acceptance testing demonstrate that the Attendance & Workforce Operations Platform meets all core functional requirements defined in Appendix A. All seven automated test cases passed with zero failures across 10 assertions. All 16 UAT test cases produced the expected outcomes across the three user roles.

| Testing Category | Total Cases | Passed | Failed | Pass Rate |
| --- | --- | --- | --- | --- |
| Automated Feature Tests (PHPUnit) | 7 | 7 | 0 | 100% |
| UAT – Staff Role | 6 | 6 | 0 | 100% |
| UAT – Manager Role | 4 | 4 | 0 | 100% |
| UAT – Admin Role | 6 | 6 | 0 | 100% |
| Overall | 23 | 23 | 0 | 100% |

No critical defects were identified. Role-based access control correctly restricts each user to their authorised panel. Attendance verification logic produces consistent results for GPS-verified and IP-verified records. Shift-window and overtime calculations are mathematically accurate as confirmed by the automated assertions. Vehicle service alerts fire correctly at the 500-km-remaining threshold.
# CHAPTER 6 CONCLUSION AND RECOMMENDATIONS FOR FUTURE WORK

## 6.1 Conclusion

This project has successfully designed and implemented an Attendance & Workforce Operations Platform tailored to the operational needs of the target organisation. The system addresses the limitations of the legacy fingerprint-based attendance device by providing a web-accessible, dual-verification (GPS + IP) attendance recording mechanism that works for on-site, remote, and outstation employees alike.
The role-based access model — Admin, Manager, and Staff — ensures that each user category interacts with only the features relevant to their responsibilities, improving usability and data security. The manager-approval workflow introduces an auditable chain of custody for attendance records, replacing the previous ad-hoc manual corrections. The vehicle mileage and service-alert module directly resolves the vehicle-maintenance blind spot identified during requirements elicitation.
The system's design adheres to the requirements gathered through interviews and observation. The Laravel 12 + Filament 3.2 technology stack ensures maintainability and aligns with the organisation's existing PHP web-server infrastructure. The database design satisfies Third Normal Form, ensuring data integrity and minimising redundancy.
Recommended areas for future development include: (1) formalising configurable shift templates (day/night/flexible) per site or team; (2) implementing a fully configurable attendance-policy engine (grace periods, late-arrival thresholds, overtime rules); (3) building an analytics dashboard with trend charts and approval SLA metrics; (4) introducing scheduled jobs for automated compliance auditing; and (5) writing a comprehensive automated test suite covering all attendance flows.

# References

1. Jibble. (2024). Jibble – Free Time and Attendance Tracking. Retrieved from https://www.jibble.io
2. TimeTec. (2024). TimeTec TA – Cloud-Based Time Attendance System. Retrieved from https://www.timetecta.com
3. Sling. (2024). Sling – Employee Scheduling & Workforce Management. Retrieved from https://getsling.com
4. Laravel. (2024). Laravel 12 Documentation. Retrieved from https://laravel.com/docs/12.x
5. Filament. (2024). Filament 3.x Documentation. Retrieved from https://filamentphp.com/docs
6. Date, C. J. (2004). An Introduction to Database Systems (8th ed.). Addison-Wesley.
7. Sommerville, I. (2016). Software Engineering (10th ed.). Pearson.
8. Sinnott, R. W. (1984). Virtues of the Haversine. Sky and Telescope, 68(2), 158.

# APPENDIX A System Requirements Specification

## A.1 Functional Requirements

| ID | Requirement |
| --- | --- |
| FR-01 | Staff shall be able to clock in by submitting GPS coordinates via the web widget. |
| FR-02 | The system shall verify the clock-in location against all active Site records using the Haversine formula. |
| FR-03 | The system shall verify the client IP address against the authorised IP of each active Site. |
| FR-04 | Staff shall be able to clock out, recording the exact date-time and updating the attendance record. |
| FR-05 | The system shall automatically close any open shift exceeding the configured maximum shift hours (default 16 h) and record an infraction. |
| FR-06 | Managers shall be able to approve or reject pending/temporary attendance records. |
| FR-07 | Admins shall be able to export attendance records as a CSV file. |
| FR-08 | Admins shall be able to generate a print-ready attendance report via a dedicated print view. |
| FR-09 | Staff shall be able to log vehicle mileage readings with an associated timestamp and optional notes. |
| FR-10 | The system shall alert staff and admins when a vehicle's current mileage is within 500 km of the next scheduled service. |
| FR-11 | Admins shall be able to create, update, and deactivate user accounts and assign roles. |
| FR-12 | Admins shall be able to create and manage Site records including GPS coordinates, radius, and IP address. |

## A.2 Non-Functional Requirements

| ID | Category | Requirement |
| --- | --- | --- |
| NFR-01 | Performance | The clock-in verification response shall complete within 3 seconds under normal network conditions. |
| NFR-02 | Security | Passwords shall be stored as bcrypt hashes. Role-based access control shall prevent cross-role data access. |
| NFR-03 | Usability | All primary user actions (clock in, clock out, log mileage) shall be accessible within two navigation steps from the dashboard. |
| NFR-04 | Scalability | The database schema and ORM layer shall support up to 500 concurrent users without schema changes. |
| NFR-05 | Compatibility | The system shall function correctly on Google Chrome, Mozilla Firefox, and Microsoft Edge (latest two versions each). |
| NFR-06 | Maintainability | Code shall follow the PSR-12 PHP coding standard and the MVC separation enforced by Laravel. |
