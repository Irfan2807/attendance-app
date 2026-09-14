# Tap and Track – Project Report (Current State)

**Date:** 2026-09-14  
**Project:** Tap and Track – Attendance, Leave & Operations Management System  
**Framework:** Laravel 12 + Filament 3.2 (PHP 8.3, Livewire 3, Tailwind CSS 4, Vite 7)  

---

## 1. Project Overview
Tap and Track is an enterprise employee attendance, leave management, and workforce operations platform featuring role-based panels for Directors, Administrators, HR Executives, Managers, and Field Staff. It integrates single-tap clock-ins with background GPS verification, automated leave quota balances with smart public holiday deduction, fleet vehicle compliance, and hierarchical approval workflows.

---

## 2. Current Modules & Architecture
- **Hero Shift Card**: Single-tap clock in/out with automated geolocation capture, off-site client selection drawer, Good Standing status badge, and quiet 3-column stats strip (`Today`, `This Week`, `This Month`).
- **Hierarchical Approval Governance**: Strict subordinate-scoped access (`manager_id`); managers approve assigned staff only, while Directors and HR Executives approve managers. Self-approvals and peer approvals are blocked.
- **Leave Quota & Application Engine**: Annual, Medical (MC), and Hospitalization leave management with doctor attachment verification, annual balance tracking, and smart working-day deduction.
- **Malaysian Public Holidays Integration**: Live API synchronization (`holidays:sync`), state-based coverage filters, and reactive hover tooltips showing observing states.
- **In-App Notification Bell**: Topbar alert system with 30s Livewire polling dispatching alerts for leave submissions, approval outcomes, clock-in reviews, and fleet compliance.
- **Fleet Compliance & Vehicle Tracking**: Odometer logging, service threshold warnings (due soon ≤ 500 km / overdue), and automated daily road tax compliance scanner (`fleet:check-alerts`).
- **Manager Operational Analytics**: Clean 3-card top strip (`Attendance Rate`, `Team Total Hours`, `Pending Approvals`), and interactive trends chart for attendance rates and overtime hours.

---

## 3. Major Enhancements Completed
1. **Consolidated Staff Dashboard (Hero Shift UX)**:
   - Combined competing widgets into a single, cohesive Hero Card, eliminating visual clutter and cognitive overload.
2. **Leave Management & Smart Public Holiday Deductions**:
   - Integrated Malaysian Public Holidays API (`https://malaysia-holiday.dydxsoft.my`).
   - Automatically excludes weekend rest days and recognized public holidays from leave deductions.
3. **In-App Notification Bell System**:
   - Added real-time database notifications across Staff and Admin portals with actionable buttons.
4. **Fleet Road Tax Expiry & Service Scanners**:
   - Added `road_tax_expiry_date` tracking and daily automated scans alerting on upcoming expirations.
5. **Operational Day & Early Arrival Buffer**:
   - Configurable `early_arrival_buffer_hours` (default 2 hours) ensuring shifts starting before 08:00 AM map to today's operational window.
6. **Elimination of Peer Approvals**:
   - Implemented Approach 1 (Trust, Telemetry & Audit) ensuring managers do not evaluate their peers, routing manager records to Director and HR Executive.
7. **Streamlined Manager Dashboard**:
   - Removed confusing Site Coverage KPI in favor of a clean, high-priority 3-card strip.
   - Simplified trends chart to focus on Attendance Rate (%) and Overtime (Hours).

---

## 4. Attendance & Shift Logic
- **Operational Day Window**: Default 08:00 AM to 08:00 AM next day, handling overnight shifts without splitting records.
- **Safety Net Auto-Closure**: Automatically closes shifts exceeding 16 hours (`ATTENDANCE_MAX_SHIFT_HOURS`), logs infractions, and increments warning counters.
- **Verification Hierarchy**: Office IP match → GPS geofence radius match → Group verification (5+ colleagues within 50m) → Off-site manager verification queue.

---

## 5. Test Suite & Validation Snapshot
- **114 automated tests passed** (421 assertions) across feature and unit suites.
- Coverage includes:
  - Role-based panel access and hierarchical subordinate isolation
  - Clock in/out status transitions and race-condition transactions
  - Leave quota deductions, year isolation, and public holiday exclusions
  - In-app notification creation, role dispatching, and Livewire polling
  - Fleet compliance alerts, road tax expiry checks, and service threshold calculations
  - Operational-day time windows and overtime calculations

---

## 6. Key Configuration Parameters (`config/attendance.php`)
- `ATTENDANCE_DAY_START_HOUR=8`
- `ATTENDANCE_MAX_SHIFT_HOURS=16`
- `ATTENDANCE_EARLY_ARRIVAL_BUFFER_HOURS=2`
- `ATTENDANCE_LATE_GRACE_MINUTES=15`

---

## 7. Conclusion
The Tap and Track platform is in a complete, battle-tested, production-ready state with comprehensive test coverage, polished user experience, and enterprise-grade operational controls.
