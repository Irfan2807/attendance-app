# Tap and Track – User Guide

**Version:** 2.0  
**Date:** September 14, 2026  
**Application:** Tap and Track – Attendance, Leave & Operations Management System  

---

## Table of Contents

1. Introduction
2. Getting Started & User Roles
3. Staff Portal – Employee Guide
4. Staff Portal – Manager & Supervisor Guide
5. Admin Panel – Executive & HR Administration
6. Leave Management & Quota Tracking
7. Malaysian Public Holidays Calendar
8. Fleet Compliance & Vehicle Tracking
9. Safety Net & Disciplinary System
10. In-App Notification Bell
11. Troubleshooting & FAQs
12. Glossary

---

## 1. Introduction

Tap and Track is an enterprise employee attendance, leave management, and workforce operations system built on **Laravel 12** and **Filament 3.2**. It provides unified, responsive interfaces for field technicians, operational managers, human resource executives, and company directors.

### 1.1 Purpose of this Guide
This guide provides complete walkthroughs for all user roles:
- **Staff (Role 3)** – Field technicians and employees recording shifts and applying for leaves.
- **Managers (Role 2)** – Supervisors managing assigned subordinates, reviewing clock-ins, approving leaves, and monitoring team overtime.
- **HR Executives (Role 4)** – Human resources managers overseeing quotas, fleet compliance, and company-wide leave approvals.
- **Administrators (Role 1)** – Technical administrators managing site configurations, system parameters, and raw logs.
- **Directors (Role 0)** – Executive leadership with company-wide visibility and authority to review manager-level attendance and leaves.

---

## 2. Getting Started & User Roles

### 2.1 System Requirements
- A modern web browser: Chrome, Firefox, Microsoft Edge, or Safari.
- Network connection (cellular or Wi-Fi).
- Phone number and password credentials.
- Browser location access enabled (for automated GPS attendance verification).

### 2.2 Access Portals

| Portal | URL | Authorized Roles |
|---|---|---|
| **Staff Portal** | `/staff` | Staff (3), Managers (2), HR Executives (4), Directors (0) |
| **Admin Panel** | `/admin` | Administrators (1), HR Executives (4), Directors (0) |
| **Company Landing Page** | `/` | All users (public-facing) |

### 2.3 Logging In
1. Navigate to `/staff` or `/admin`.
2. Enter your registered **Phone Number** (e.g., `0123456789`).
3. Enter your **Password**.
4. Click **Sign In**.

---

## 3. Staff Portal – Employee Guide

### 3.1 Hero Shift Card Overview
The top of your dashboard features the unified **Hero Shift Card**:
- **Personal Greeting**: Displays your name and current operational date.
- **Standing Indicator**: Shows `● Good Standing` when your record has zero infractions.
- **Shift Status Badge**:
  - `Ready to Start Shift` (before clock-in)
  - `● On Duty · Started at 08:30 (2h 15m)` (while working)
  - `✓ Shift Completed (08:30 - 17:30) · 9h 0m worked` (after shift close)
- **Quick Metrics Strip**: Pinned horizontally at the bottom:
  - **TODAY**: Total hours worked today.
  - **THIS WEEK**: Cumulative hours from Monday to Sunday.
  - **THIS MONTH**: Total completed shifts this calendar month.

### 3.2 Clocking In (Single-Tap)
1. On your Hero Shift Card, tap the primary green **`Clock In`** button.
2. The browser automatically captures your GPS coordinates in the background.
3. Verification is evaluated seamlessly:
   - **Office IP match**: Shift is automatically **Approved**.
   - **GPS radius match**: Shift is automatically **Approved**.
   - **Group presence**: 5+ colleagues nearby within 50 meters auto-approves your presence.
   - **Off-site work**: Shift transitions to **Pending** for manager verification.

### 3.3 Working Off-Site or at a Client Project
1. Before tapping Clock In, click the link: **`▼ Working at a client site or off-site?`**.
2. An expandable drawer appears:
   - **Select Predefined Site**: Choose an assigned company site from the dropdown.
   - **Or Enter Client Location**: Type your project name (e.g., *Petronas Subang Site*) and click **Set**.
3. Tap **`Clock In`**. Your location name is attached to your shift record for manager approval.

### 3.4 Clocking Out
1. When your shift ends, tap the red **`Clock Out`** button.
2. Your shift duration is calculated instantly:
   - Pre-verified shifts transition directly to **Completed**.
   - Off-site shifts transition to **Temporary** awaiting manager sign-off.

---

## 4. Staff Portal – Manager & Supervisor Guide

### 4.1 Manager Overview Strip
Supervisors see an executive 3-card metric strip at the top of their dashboard:
1. **Attendance Rate (Today)**: Live percentage of active subordinates on duty today (e.g., `100% · 2 of 2 staff` or accounting for staff on approved leave).
2. **Team Total Hours**: Aggregate working hours delivered by your subordinates this month (formatted clearly as `92hrs 2 mins`).
3. **Pending Approvals**: Live badge showing the total queue of subordinate clock-ins and leave applications awaiting your review.

### 4.2 Clock-In Approvals (Hierarchical Governance)
1. Click **Clock-In Approvals** in the sidebar.
2. The queue strictly lists records belonging to your **assigned direct subordinates**.
   *(Note: Fellow managers are excluded; your own supervisor or the Director approves your shifts).*
3. Review the captured timestamp, GPS coordinates, and verification notes.
4. Click **Approve** (with optional remarks) or **Reject** (with required reason).
5. The employee immediately receives an in-app notification confirming your decision.

### 4.3 Leave & MC Approvals
1. Click **Leave & MC Approvals** in the sidebar.
2. Review the leave application details: applicant name, leave type, working days deducted, and dates.
3. For medical leaves, click the **Attachment** link to inspect the uploaded doctor's Medical Certificate (MC).
4. Click **Approve** or **Reject**. If rejected, provide an explanation note.

### 4.4 Team Attendance & Overtime Trends Chart
The interactive trends widget below your shift card visualizes:
- **Attendance Rate (%)** (green area curve) across 7, 14, or 30 days.
- **Overtime Hours** (blue line curve) tracking team extra hours.
- Use the dropdown in the top-right corner to switch between Weekly, Bi-weekly, and Monthly scopes.

### 4.5 Monthly Timesheets & Payroll Reports (HR & Managers)
Managers and HR Executives can generate end-of-month attendance reports for payroll calculation:
1. Navigate to **Management** &rarr; **Staff Attendance Overview** (or click **Monthly Timesheets / Payroll** in the top header).
2. Direct URL: `/attendance/monthly-report`.
3. **Filter Period**: Select the **Month** and **Year** (e.g. *September 2026*).
4. **Inspect KPI Strip**: View total active staff, total present days, regular hours, overtime hours, approved leave days, and late starts.
5. **Print Individual Timesheet Slip**: Click **Timesheet Slip** next to any employee to view and print their official A4 attendance card with signature boxes.
6. **Download All Staff (Payroll Bundle)**: Click **Print All Staff (Payroll Bundle)** to generate a multi-page compiled PDF document of all active staff timesheets for payroll submission.
7. **Export Summary CSV**: Click **Export Summary CSV** to download a spreadsheet for direct import into payroll software.

---

## 5. Admin Panel – Executive & HR Administration

Administrators, HR Executives, and Directors access `/admin` for enterprise configuration:
- **Staff Management**: Create users with explicit reporting lines (`manager_id`), assign roles, toggle `is_active` status, and access individual timesheet slips.
- **Work Sites**: Configure static office IP addresses, GPS coordinates, and allowed geofence radii.
- **Public Holidays**: View national and state holidays and trigger live API syncs via the **Sync Holidays from API** button.
- **Company Attendance & Leaves**: View global company records with full search, date filters, CSV export, and printable audit sheets.
- **Monthly Timesheets / Payroll**: Header action linking to the company-wide monthly payroll hub.

---

## 6. Leave Management & Quota Tracking

### 6.1 Quotas & Balances
Every staff member receives an annual quota allocation:
- **Annual Leave**: Typically 14 days/year.
- **Medical Leave (MC)**: Typically 14 days/year.
- **Hospitalization**: Typically 60 days/year.

Your current balances are displayed directly in the **Leave Quota Cards** on your dashboard (`14 / 14 Days Remaining`).

### 6.2 Applying for Leave
1. Open **Apply / My Leave** from the sidebar.
2. Click **New Leave Request**.
3. Select **Leave Type** (Annual, Medical, or Hospitalization).
4. Pick **Start Date** and **End Date**.
5. **Smart Working Days Calculation**:
   - The system automatically inspects weekend rest days and recognized Malaysian public holidays.
   - Only legitimate working days are deducted from your balance.
   - Example: A Friday-to-Monday leave over a public holiday automatically calculates as 1 working day instead of 4 calendar days.
6. Attach supporting documentation (mandatory for Medical and Hospitalization leaves).
7. Enter a brief reason and click **Submit**.

---

## 7. Malaysian Public Holidays Calendar

1. Open **Public Holidays** from the sidebar.
2. View all upcoming holidays with countdown status (*"In 5 days"*, *"Today"*, or *"Passed"*).
3. **Observing States Hover Tooltip**:
   - For state holidays, hover your mouse over the **State Holiday** badge or holiday name.
   - A tooltip popup displays the full list of observing states (e.g., *"Applicable in: Selangor, WP Kuala Lumpur, Putrajaya"*).
4. Use the **State Filter** dropdown to view holidays specifically applicable to your work territory.

---

## 8. Fleet Compliance & Vehicle Tracking

### 8.1 Vehicle Inventory & Status
Navigate to **Fleet → Vehicles** to inspect company fleet vehicles:
- **OK**: Vehicle mileage is well within maintenance limits.
- **Service Due Soon**: Remaining distance to service is ≤ 500 KM.
- **Service Overdue**: Current mileage has reached or exceeded next service threshold (highlighted in red).
- **Road Tax Expiry**: Shows expiration dates with color alerts for road taxes expiring within 30 days or already expired.

### 8.2 Logging Trip Mileage
1. Open **Fleet → Mileage Logs**.
2. Click **New Mileage Log**.
3. Select the vehicle and record the odometer reading at the start/end of your journey.
4. Save the log to update the vehicle's telemetry.

---

## 9. Safety Net & Disciplinary System

### 9.1 16-Hour Automated Shift Closure
To prevent forgotten shifts from corrupting payroll, any shift left open for **16 hours** or longer is automatically closed by the system:
- Status is converted to **Temporary**.
- An `AttendanceInfraction` (`forgot_clock_out`) is recorded.
- An urgent notification is dispatched to both employee and direct supervisor.

### 9.2 Good Standing & Escalation
- **0 Infractions**: Account is in `● Good Standing`.
- **1st Infraction**: Informational notice.
- **2nd Infraction**: Formal warning notice.
- **3+ Infractions**: Escalation alert flagging manager disciplinary review.

---

## 10. In-App Notification Bell

The top header bar features a real-time **Notification Bell** with 30-second background polling:
- **Leave Alerts**: Immediate notification when a subordinate applies, or when your supervisor approves/rejects your leave with notes.
- **Clock-In Alerts**: Alerts managers when an off-site technician requires attendance verification.
- **Compliance Alerts**: Dispatches 14-day advance notices for expiring vehicle road taxes.
- Click any notification to jump directly to the relevant approval queue or record.

---

## 11. Troubleshooting & FAQs

| Problem | Likely Cause | Solution |
|---|---|---|
| Clock-in shows "Pending" | Clocked in outside registered office IP or off-site | Normal behavior; your manager will verify and approve your record |
| Cannot see subordinate in approvals | Subordinate is not assigned your `manager_id` | Contact HR or Admin to assign the reporting line in Staff Management |
| Cannot apply for leave | Requested working days exceed remaining quota balance | Check your leave balance cards; select dates within your quota |
| Medical leave upload rejected | File format or missing attachment | Attach a clear image (JPG/PNG) or PDF copy of your medical certificate |
| Holiday not showing for my state | Filter set to another state or holiday not synced | Select your state in the filter, or ask HR to run "Sync Holidays from API" |

---

## 12. Glossary

- **Hero Shift Card**: The unified single-card interface on the staff dashboard combining greeting, status, action button, and footer stats.
- **Smart Working Days**: Leave calculation logic that excludes weekend days and Malaysian public holidays.
- **Subordinate Governance**: Strict permission model where supervisors only access staff reporting directly to them.
- **Operational Day**: 24-hour attendance window (default 08:00 AM start with 2-hour early buffer) ensuring shifts are correctly attributed.
