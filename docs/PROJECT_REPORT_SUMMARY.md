# Tap and Track – Project Report (Current State)

Date: 2026-04-14
Project: Tap and Track
Framework: Laravel 12 + Filament 3.2

## 1. Project Overview
This project is an employee attendance and workforce operations platform with role-based panels for admin, manager, and staff. It supports clock-in/clock-out flows, location and IP verification, manager approval workflows, attendance exports, and public company-facing pages.

## 2. Current Modules
- Authentication: custom login flow, role-based panel access.
- Staff operations: clock in/out widget, personal attendance logs, warning widget.
- Manager operations: staff attendance overview and approval queue.
- Admin operations: global attendance resource and export/print endpoints.
- Public pages: Home, Services, Contact with updated branding and content.

## 3. Key Enhancements Completed
- Public site redesign and UX refinement across static pages.
- Added visible Login button in public navigation.
- Fixed Vite production build issue (terser dependency conflict) by switching minifier to esbuild.
- Fixed local asset loading issue caused by forced HTTPS in local environment.
- Added contact map embed from legacy site.
- Fixed manager approval query so pending/temporary records include staff records (not manager-only).
- Fixed widget refresh cache-key mismatch.
- Improved IP extraction for proxied environments (X-Forwarded-For first IP).
- Restricted manager analytics widget visibility to managers only.
- Implemented operational-day attendance logic for flexible/night shifts via AttendanceWindowService.

## 4. Attendance Logic (Important)
The system now supports flexible shift behavior better than strict calendar-day logic:
- Operational day start hour is configurable (default 05:00).
- Overnight work is grouped under one operational attendance day.
- Stale open shifts are auto-closed by max-shift threshold (default 16 hours).
- Staff and manager widgets now use operational-day windows for daily metrics.

Config:
- ATTENDANCE_DAY_START_HOUR=5
- ATTENDANCE_MAX_SHIFT_HOURS=16

## 5. Known Gaps / Risks
- Automated tests are currently missing (no test suite coverage for attendance flows).
- Some analytics are still basic; trend charts and policy scoring can be expanded.
- Attendance policy engine is not yet fully configurable per role/site/shift template.

## 6. Recommended Next Development Phase
1) Add shift templates (Day/Night/Flexible per site/team).
2) Add policy engine (late, early leave, overtime, grace period).
3) Add analytics dashboard (attendance trends, overtime, approval SLA).
4) Add scheduled jobs for compliance automation and stale-shift handling audit logs.
5) Add feature tests for end-to-end scenarios.

## 7. Validation Snapshot
- Public routes load and styled assets compile with Vite build.
- Login route is available from public navbar.
- Staff portal and manager resources resolve.
- Attendance widgets and approval logic updated for role correctness and operational-day behavior.

## 8. Files Most Recently Updated
- app/Filament/Staff/Widgets/ClockInOutWidget.php
- app/Filament/Staff/Widgets/ClockInDetailsWidget.php
- app/Filament/Staff/Widgets/StaffAttendanceOverviewStatsWidget.php
- app/Filament/Staff/Resources/StaffAttendanceApprovalResource.php
- app/Services/AttendanceWindowService.php
- app/Services/AttendanceVerificationService.php
- config/attendance.php
- resources/views/layouts/site.blade.php
- resources/views/welcome.blade.php
- resources/views/services.blade.php
- resources/views/contact.blade.php
- resources/css/app.css
- vite.config.js

## 9. Conclusion
The project is in a strong state for FYP demonstration, with meaningful real-world improvements already implemented. The highest-impact next step is to formalize attendance policy rules and analytics dashboards, then add automated tests for confidence and reproducibility.
