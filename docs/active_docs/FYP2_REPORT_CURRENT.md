# Tap and Track – FYP2 Final Project Report

**Date:** September 14, 2026  
**Project:** Tap and Track – Enterprise Attendance, Leave & Operations Management System  
**Stack:** Laravel 12, Filament 3.2, PHP 8.3, Livewire 3, Alpine.js, Tailwind CSS 4, Vite 7  

---

## 1) Project Status

The system is in a **complete, production-ready** state with comprehensive workforce operations, leave management, automated safety net compliance, and hierarchical governance implemented and verified:

- Full Role-Based Access Control (RBAC) across 5 distinct tiers: Director (0), Administrator (1), Manager (2), Staff (3), HR Executive (4).
- Single-card Hero Shift attendance tracking with automated geolocation verification.
- Complete Leave Management engine with annual quotas, medical certificate attachment uploads, and smart working-day deduction.
- Malaysian Public Holidays calendar synchronization with state-specific observance tooltips.
- Real-time in-app notification bell system with 30-second Livewire polling.
- Fleet vehicle tracking, trip odometer logs, service maintenance indicators, and automated daily road tax compliance scans.
- Hierarchical approval governance eliminating peer-manager approvals and enforcing supervisor-subordinate isolation.
- Operational analytics: Attendance & Overtime trends, monthly aggregate hours, and live approval queues.
- 114 automated tests with 100% pass rate.

---

## 2) Completed Core Modules

### A. Authentication & Hierarchical RBAC
- Phone-based authentication with bcrypt hashing.
- Role-gated panel routing (`canAccessPanel()`):
  - Admin Panel (`/admin`): Administrator, Director, HR Executive.
  - Staff Portal (`/staff`): Staff, Manager, Director, HR Executive.
- Subordinate mapping via `manager_id` foreign key.

### B. Hero Shift Attendance System
- Streamlined single-card dashboard widget eliminating visual clutter.
- One-tap primary clock-in action with automated background HTML5 Geolocation capture.
- Tiered verification: Office IP match → GPS geofence radius match → Group presence (5+ nearby staff) → Off-site manager queue.
- Friendly off-site client location selector with assigned site dropdown or client text input.
- Clear status indicators (`Ready to Start`, `On Duty`, `Shift Completed`) and quiet 3-column stats strip (`Today`, `This Week`, `This Month`).

### C. Leave & Quota Management
- Supports Annual Leave, Medical Leave (MC), and Hospitalization Leave.
- Tracks annual quotas, used days, and remaining balances per employee.
- **Smart Working Days Engine**: Inspects weekend rest days and recognized Malaysian public holidays, ensuring only legitimate working days deduct from employee balances.
- Secure file attachment upload for medical certificates (PDF/PNG/JPG).

### D. Malaysian Public Holidays Integration
- Integrated with Malaysia Public Holidays API (`https://malaysia-holiday.dydxsoft.my`).
- Automated artisan command `holidays:sync` and admin header button for on-demand synchronization.
- Calendar view with upcoming countdowns, state filters, and reactive hover tooltips revealing observing states for state-specific holidays.

### E. In-App Notification Bell System
- Centralized `AppNotificationService` managing database notification persistence.
- Livewire 30-second polling for real-time topbar notification updates.
- Workflows connected: Leave submission, approval, and rejection; clock-in approval requests; auto-clock out infractions; fleet road tax alerts.

### F. Fleet Compliance & Road Tax Tracking
- Fleet vehicle inventory tracking registration plates, odometer readings, and service thresholds.
- Road tax expiry date tracking with visual status indicators.
- Automated daily scanner command `fleet:check-alerts` dispatching compliance alerts for road taxes expiring within 14 days and service overdue.

### G. Hierarchical Approval Governance
- Supervisors only see and approve records belonging to their assigned direct subordinates (`where('manager_id', $currentUserId)`).
- Peer-manager approvals and self-approvals are blocked server-side.
- Manager leaves and shifts route exclusively to Director and HR Executive.

---

## 3) Validation & Test Suite

The system has been verified through a rigorous automated test suite:
- **114 automated tests passing (421 assertions) — 100% green**.
- Test suites cover:
  - Attendance status transitions and operational day boundaries
  - Hierarchical approval access and self-approval guards
  - Leave quota allocations, attachments, and public holiday working day deductions
  - In-app database notifications and role-based dispatching
  - Vehicle mileage calculations, service thresholds, and road tax alerts
  - Security, sanitization, and formula injection guards on CSV exports

---

## 4) Conclusion

All functional and non-functional requirements set for the FYP2 scope have been **fully realized, verified, and documented**. The platform provides an enterprise-ready foundation for workforce operations and compliance monitoring.
