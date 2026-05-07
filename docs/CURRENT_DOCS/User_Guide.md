# Tap and Track – User Guide

**Version:** 1.1  
**Date:** May 6, 2026  
**Application:** Tap and Track – Attendance Management System

---

## Table of Contents

1. Introduction
2. Getting Started
3. Staff Portal – Employee Guide
4. Staff Portal – Manager Guide
5. Admin Panel Guide
6. Vehicle & Mileage Tracking
7. Safety Net & Warning System
8. Troubleshooting & FAQs
9. Glossary

---

## 1. Introduction

Tap and Track is a web-based employee attendance management system designed to simplify and automate the process of recording, verifying, and approving employee attendance. Built on Laravel 12 and the Filament 3.2 admin framework, it provides role-based access for Administrators, Managers, and Staff members.

### 1.1 Purpose of this Guide
This guide explains how to use all features of the Tap and Track application. It is intended for three types of users:

- **Staff (Role 3)** – employees who clock in and out daily
- **Managers (Role 2)** – supervisors who review and approve attendance, and manage staff accounts
- **Administrators (Role 1)** – system administrators who manage the full platform

### 1.2 Conventions Used

| Convention | Meaning |
|---|---|
| **Bold text** | UI element (button, menu item, field name) |
| *Italic text* | Important note or tip |
| "Quoted text" | Text you should type exactly as shown |
| [Role] | Step applies only to the specified role |

---

## 2. Getting Started

### 2.1 System Requirements
To use Tap and Track you need:
- A modern web browser (Google Chrome, Firefox, Edge, or Safari)
- An internet connection
- Your phone number and password (provided by your administrator or manager)

### 2.2 Accessing the Application

| Portal | URL | Who Can Access |
|---|---|---|
| Staff Portal | `/staff` | Managers & Staff employees |
| Admin Panel | `/admin` | Administrators only |
| Public Landing Page | `/` | Everyone |

### 2.3 Logging In
1. Open the appropriate portal URL in your browser.
2. Enter your **Phone Number** in the phone field.
3. Enter your **Password**.
4. Click the **Log In** button.

You will be redirected to your dashboard upon successful login.

*Note: If you cannot log in, contact your administrator or manager to verify your account credentials and role assignment.*

### 2.4 Logging Out
Click on your profile name or avatar in the top-right corner of the screen and select **Sign Out** from the dropdown menu.

---

## 3. Staff Portal – Employee Guide

This section is for employees (Role 3 – Staff). After logging in to the Staff Portal (`/staff`), you will see your personal dashboard.

### 3.1 Dashboard Overview
The Staff dashboard shows a summary of your attendance activity, including:
- Total time worked today, this week, and this month
- Your active or last shift details
- Any pending approvals
- Your warning/infraction count

### 3.2 Clocking In
1. On your dashboard, locate the **Clock In** widget.
2. Click **Get Location** to capture your GPS coordinates (optional but recommended).
3. Click **Clock In**.
4. The system verifies your clock-in using this order:
   - IP match against active site IPs
   - GPS radius match (if location is provided)
   - Group verification (5+ approved nearby clock-ins within 50m, last 2 hours)
   - If verification succeeds → clock-in is **Approved** immediately.
   - If verification fails → clock-in is set to **Pending** and requires manager approval.
   - If you already completed one shift today, a new shift is always set to **Pending** for manager review.
5. A notification will confirm the clock-in and indicate the verification result.

*Important: You must be connected to the site's designated network for IP verification to succeed. Clock-ins from unrecognised IP addresses will be flagged as pending.*

### 3.3 Clocking Out
1. On your dashboard, locate the **Clock Out** button in the Clock In/Out widget.
2. Click **Clock Out**.
3. Your attendance record status will update:
   - If the shift was already **Approved** → status becomes **Completed**.
   - If the shift was **Pending** → status becomes **Temporary** (awaiting manager approval).
4. A success notification will appear.

### 3.4 Viewing Your Attendance History
Go to **Attendance Logs** in the left sidebar to see a full list of your past attendance records. Each record shows the date, site name, clock-in time, clock-out time, hours worked, overtime, and approval status.

**Status Badge meanings:**

| Status | Meaning |
|---|---|
| Pending | Clocked in; awaiting manager verification/approval |
| Temporary | Clocked out; awaiting manager approval |
| Approved | Shift approved by a manager (or auto-verified at clock-in) |
| Completed | Approved shift with a successful clock-out |
| Rejected | Rejected by manager – check the approval notes for the reason |

### 3.5 Checking Your Warnings
If you have missed clock-outs or violated attendance policies, warnings are recorded. Your dashboard **Warnings** widget shows:
- **This Month Warnings** – count of auto clock-out incidents this month
- **Total Infractions** – all-time count of missed clock-outs
- **Status** – Good Standing or Escalated (at 3+ monthly warnings, your manager is alerted)

---

## 4. Staff Portal – Manager Guide

Managers (Role 2) have access to all Staff features plus approval, reporting, and team management tools.

### 4.1 Approving Attendance Records
1. Click **Clock-In Approvals** in the left sidebar (under **Management**).
2. You will see a list of attendance records with status **Pending** or **Temporary**.
3. Click the **✓ Approve** button on a record to approve it.
   - A modal will appear where you can optionally add **Approval Notes**.
   - Click **Approve** to confirm.
4. Click the **✗ Reject** button to decline a record.
   - You must enter a **Rejection Reason** (required).
   - Click **Reject** to confirm.

*Note: You cannot approve or reject your own attendance records.*

You can also select multiple records and use **Bulk Actions** to approve or reject them together.

### 4.2 Viewing All Employee Attendance
Go to **Staff Attendance Overview** (under **Management**) to see attendance records for all staff employees. You can:
- Filter by status, staff member, location, or date range
- See clock-in/clock-out times, worked duration, and overtime
- Edit approval notes on a record
- Delete records if necessary

### 4.3 Managing Staff Users
Go to **Staff Management** (under **Company**) to manage employee accounts.

**Managers can:**
- **Create** new Staff (Role 3) user accounts
- **Edit** staff user details
- **Delete** staff user accounts

*Note: Only Administrators can create Manager or Admin accounts.*

### 4.4 Viewing Office Locations
Go to **Office Locations** (under **Company**) to view the list of registered work sites. Each site shows its name, GPS coordinates, allowed radius, and active status. Click **Map** on any site to open it in Google Maps.

*Note: Only Administrators can add or edit site details.*

### 4.5 Exporting Attendance Data
Managers can export attendance data by opening `/attendance/export`.  
Print view is available at `/attendance/print`.
 
*Note: the current CSV export endpoint returns all attendance records (newest first), not the active table filters.*

### 4.6 Vehicle Service Alerts
Managers see a **Vehicle Service Alerts** widget on their dashboard, which lists vehicles that are due for service soon (within 500 km) or overdue. Click **Update Service** on a vehicle to set the next service mileage.

---

## 5. Admin Panel Guide

Administrators (Role 1) access the dedicated Admin Panel at `/admin`. This panel provides full control over all system data.

### 5.1 User Management
Navigate to **Staff Management** in the sidebar to create, edit, or deactivate user accounts.

**Administrators can create users with any role:**

| Field | Description |
|---|---|
| Name | Full name of the employee |
| Phone | Mobile number used for login (must start with `01`, 10–11 digits) |
| Password | Set initial password (hashed automatically) |
| Role | 1 = Super Admin, 2 = Manager, 3 = Staff |

### 5.2 Site Management
Navigate to **Sites** to manage work locations.

| Field | Description |
|---|---|
| Name | Human-readable site name |
| IP Address | Allowed IP address for clock-in verification |
| Latitude / Longitude | GPS coordinates of the site |
| Radius (metres) | Acceptable GPS radius for location check |
| Is Active | Toggle to enable/disable the site |

### 5.3 Attendance Management
The **Attendance Logs** resource gives the administrator a full view of every attendance record across all employees, with the ability to filter, edit, approve, or delete records.

### 5.4 Bulk Actions
On any resource list, check the checkbox next to one or more records and use the **Bulk Actions** dropdown to perform operations on multiple records at once.

---

## 6. Vehicle & Mileage Tracking

Tap and Track supports logging company vehicle mileage to track usage and flag upcoming or overdue service intervals.

### 6.1 Viewing Vehicles
Go to **Vehicles** (under **Fleet**) in the Staff Portal sidebar to see all registered company vehicles, their current mileage, next service mileage, and service status.

| Status | Meaning |
|---|---|
| OK | Service not due yet |
| Service Due Soon | Within 500 km of next service interval |
| Service Overdue | Current mileage has reached or exceeded next service mileage |

### 6.2 Logging Mileage
1. Navigate to **Mileage Logs** (under **Fleet**) in the sidebar.
2. Click **New Mileage Log**.
3. Select the **Vehicle** from the dropdown. The current odometer reading will be shown as a hint.
4. Enter the **Odometer Reading (KM)** – this is the odometer reading at the time you are starting the journey (must be equal to or greater than the vehicle's current mileage).
5. Confirm the **Date & Time** of the log.
6. Optionally add any **Notes** (trip details, fuel, etc.).
7. Click **Save**.

*The mileage reading is recorded once at journey start. Each log captures a single odometer snapshot to track the vehicle's usage over time.*

### 6.3 Editing Mileage Logs
- **Managers** can edit any mileage log at any time.
- **Staff** can edit their own logs within 24 hours of creation.

---

## 7. Safety Net & Warning System

The Safety Net System protects both employees and the organisation by automatically handling missed clock-outs and escalating repeated violations.

### 7.1 Auto Clock-Out
If an employee has not clocked out after **16 hours** (configurable via `ATTENDANCE_MAX_SHIFT_HOURS`), the system will automatically clock them out the next time their dashboard loads.

- The attendance record will be set to status **Temporary** (the auto clock-out time is set to `clock_in_time + max_shift_hours`).
- The record is then submitted for manager review.
- An infraction (`forgot_clock_out`) is recorded and the employee's `incomplete_clock_out_count` is incremented.

### 7.2 Warning Escalation

| This Month Warnings | Dashboard Warning Widget |
|---|---|
| 0 | ✓ Good Standing |
| 1 | First warning – visible on dashboard |
| 2 | Final warning – visible on dashboard |
| 3+ | Escalated – manager review needed |

*Note: `incomplete_clock_out_count` is still tracked as all-time infractions, but escalation in the warning widget is based on monthly warnings.*

### 7.3 Resetting Warnings
The warning counter is system-managed in the current UI and is not directly editable from Filament forms.
If a reset is required, it must be done manually at database level by an administrator with backend access.

---

## 8. Troubleshooting & FAQs

| Problem | Possible Cause | Solution |
|---|---|---|
| Cannot log in | Wrong phone number or password | Check credentials; contact your admin or manager |
| Clock In button disabled | Already clocked in | Check your active shift; clock out first |
| Clock-in shows "Pending" | IP address not recognised | Manager will review and approve; or connect to the site network |
| Attendance shows "Temporary" | Clocked out but not yet approved | Wait for manager approval |
| Cannot see Approvals menu | Role is Staff (3), not Manager (2) | Request role change from admin |
| Cannot see Manager features | Logged in as Staff | Log in with a Manager account |
| Export not downloading | Browser pop-up blocker | Allow pop-ups for the application URL |
| Vehicle shows "Service Overdue" | Mileage logged exceeds service threshold | Arrange vehicle service and update the next service mileage |
| Attendance was auto clocked out | Shift was open for 16+ hours | Contact manager for approval review; avoid leaving shifts open overnight |

---

## 9. Glossary

| Term | Definition |
|---|---|
| Clock In | Recording the start of a work shift |
| Clock Out | Recording the end of a work shift |
| Safety Net | Automated system that closes shifts open for 16+ hours |
| IP Verification | Matching the employee's network IP to the registered site IP |
| Infraction | A recorded violation of attendance policy (e.g., forgot to clock out) |
| `incomplete_clock_out_count` | Cumulative count of auto clock-out incidents for a user |
| Approval Status | The current state of an attendance record (Pending / Temporary / Approved / Completed / Rejected) |
| Filament | The Laravel admin panel framework used to build the UI |
| Site | A registered physical work location with IP and GPS data |
| Mileage Reading | The odometer value recorded at the start of a vehicle journey |
| Operational Day | The 24-hour window starting at `ATTENDANCE_DAY_START_HOUR` (default 08:00), used for daily attendance metrics |
