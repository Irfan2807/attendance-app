# Tap and Track

[![Laravel](https://img.shields.io/badge/Laravel-12-red?logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3-blue?logo=php)](https://php.net)
[![Filament](https://img.shields.io/badge/Filament-3.2-orange)](https://filamentphp.com)
[![License](https://img.shields.io/badge/License-MIT-green)](https://opensource.org/licenses/MIT)

A modern employee attendance, leave management, and workforce operations tracking platform built with **Laravel 12** and **Filament 3.2**. Features single-tap clock-ins with background GPS verification, automated leave balance tracking with Malaysian public holiday exclusions, fleet vehicle compliance, role-based approval governance, in-app notifications, and automated safety net protections.

---

## Features

| Feature | Description |
|---|---|
| 🕐 **Hero Shift Clock In/Out** | Single-tap primary clock-in with automated geolocation, off-site client selection, and quiet quick stats |
| 🛡️ **Safety Net System** | Automated shift closure after 16 hours with 3-strike infraction escalation and audit logging |
| 🌴 **Leave & Quota Management** | Annual, Medical (MC), and Hospitalization leave applications with attachment uploads and live balance deductions |
| 🇲🇾 **Malaysian Public Holidays** | Automatic holiday synchronization from public API, working-day auto-exclusion, and state observance hover tooltips |
| 🔔 **In-App Notification Bell** | Real-time database alerts with 30s Livewire polling for leave approvals, clock-in reviews, and infractions |
| 🚗 **Fleet & Road Tax Alerts** | Mileage logs, service due tracking, and automated 30-day/14-day road tax compliance scanners (`fleet:check-alerts`) |
| 👥 **Hierarchical Approval Governance** | Subordinate-scoped approvals: managers approve direct staff, Directors and HR Executives approve managers |
| 📊 **Operational Analytics** | Interactive team attendance and overtime trend charts, monthly aggregate hours, and live approval queues |
| 📄 **Monthly PDF Timesheets & Payroll** | Audit-ready A4 timesheet slips with signature blocks, company-wide payroll bundle, and CSV export for HR |
| 👤 **Staff Portal & Admin Panel** | Dedicated Filament portals for staff self-service, manager supervision, and executive administration |
| ⚡ **Performance Optimized** | Lazy-loaded widgets, database indexing, cache layers, and chunked CSV exports |

---

## Tech Stack

| Layer | Technology |
|---|---|
| **Backend** | Laravel 12, PHP 8.3 |
| **Admin & Staff UI** | Filament 3.2 (Blade, Livewire 3, Alpine.js) |
| **Frontend Styling** | Tailwind CSS 4, Vite 7 |
| **Database** | SQLite / MySQL compatible |
| **Caching** | Intelligent multi-driver caching (File / Redis) |
| **External API** | Malaysia Public Holidays API (`malaysia-holiday.dydxsoft.my`) |

---

## User Roles & Access

| Role ID | Title | Primary Portal | Key Capabilities |
|:---:|---|---|---|
| **1** | **Director / Super Admin** | `/admin` *(Auto-routed)* | Executive company oversight, system parameters, work site geofence setup, holiday API sync, global record management, and review of manager-level leaves. |
| **2** | **Operations Manager** | `/staff` *(Auto-routed)* | Field team supervision; reviews subordinate shift requests, approves leave applications, and monitors team attendance and overtime trends. |
| **3** | **Field Staff / Engineer** | `/staff` *(Auto-routed)* | Single-tap GPS clock in/out, client site check-ins, leave balance tracking, and personal monthly timesheet reviews. |
| **4** | **HR Executive** | `/staff` *(Auto-routed)* | Human resources operations; manages company quotas, vehicle compliance & road taxes, reviews leave applications, and generates monthly payroll timesheets. |

---

## Access Points & Authentication Flow

The system features a **Single Unified Sign-In Link (`/login`)** for all personnel. Users do not navigate to separate login portals; the system automatically authenticates their mobile phone number and routes them to their authorized environment:

| Access Point | URL | Primary Audience & Function |
|---|---|---|
| 🔐 **Unified Portal Sign In** | `/login` | **Single sign-in URL for all users** (Staff, Managers, HR, and Directors). Auto-routes to `/staff` or `/admin` based on role upon authentication. Unauthenticated requests to `/staff` or `/admin` are automatically redirected here. |
| 🌐 **Public Website** | `/` | Corporate landing page, services showcase, engineering portfolio, and contact enquiry form with topbar link to **Portal Login**. |
| 🟢 **Staff Operations Portal** | `/staff` | Daily operational workspace for **Staff (3)**, **Managers (2)**, and **HR Executives (4)**: Hero shift clock-in/out card, geofence verification, leave quotas, team approval queues, and fleet mileage. |
| 🟠 **Executive Admin Panel** | `/admin` | System management workspace for **Directors / Super Admins (1)**: System configurations, work site coordinates, public holiday API sync, and raw database records. |
| 📄 **Monthly Payroll Hub** | `/attendance/monthly-report` | End-of-month attendance auditing hub for **HR Executives**, **Managers**, and **Directors**: A4 printable timesheets with signature blocks, company payroll bundles, and CSV exports. |

---

## Database Schema Highlights

| Table | Purpose |
|---|---|
| `users` | Employee accounts with hierarchical `role` and `manager_id` reporting lines |
| `attendances` | Clock in/out records with operational-day timestamps, GPS coordinates, and verification notes |
| `attendance_infractions` | Safety net infractions and warning tracking for missed clock-outs |
| `leave_requests` | Leave applications with date ranges, working day counts, status, and attachment proofs |
| `leave_quotas` | Annual, medical, and hospitalization quotas and remaining balances per user per year |
| `public_holidays` | Synced national and state Malaysian public holidays |
| `notifications` | Database notification records supporting in-app topbar notification bells |
| `sites` | Work locations with GPS coordinates and IP whitelisting |
| `vehicles` | Fleet vehicles with mileage thresholds, service intervals, and road tax expiry dates |
| `mileage_logs` | Vehicle trip and odometer logs tied to drivers and vehicles |

---

## Safety, Compliance & Automation Commands

- **Auto Clock-Out (`attendance:auto-clock-out`)** – Automatically closes forgotten shifts exceeding the maximum threshold (default 16 hours), records an infraction, and alerts user and manager.
- **Fleet Compliance Scanner (`fleet:check-alerts`)** – Scheduled daily at 08:00 AM to scan vehicles for road tax expiring within 14 days and upcoming/overdue maintenance.
- **Holiday Synchronization (`holidays:sync`)** – Fetches and syncs nationwide and state-level holidays from the Malaysia Public Holidays API.
- **Hierarchical Approval Integrity** – Self-approvals and peer-manager approvals are blocked; supervisors only approve assigned subordinates.
- **PDPA Privacy & Secure MC Storage** – Medical certificates (MCs) are saved to private storage (`storage/app/private`) and streamed through an authenticated RBAC gateway (`/leaves/{id}/attachment`). Unauthenticated scraping and unauthorized peer downloads are strictly prohibited under Malaysia's Personal Data Protection Act (PDPA 2010).

---

## Performance Optimizations

- Composite database indexes on frequently queried columns (`user_id`, `clock_in_time`, `status`, `manager_id`)
- Lazy-loaded Filament widgets (`#[Lazy]`) for fast initial page paint
- Cached analytics queries with automatic cache invalidation on status transitions
- Chunked CSV exports with lazy collections to handle large datasets efficiently
- Minified assets with esbuild

---

## Installation & Setup

```bash
# Clone the repository
git clone https://github.com/Irfan2807/attendance-app.git
cd attendance-app

# Install PHP and Node dependencies
composer install
npm install

# Setup environment configuration
cp .env.example .env
php artisan key:generate

# Run database migrations and seed default data
php artisan migrate --seed

# Sync Malaysian Public Holidays
php artisan holidays:sync

# Build frontend assets
npm run build

# Start the application
php artisan serve
```

---

## Testing

The project includes an automated test suite verifying business logic, access controls, status transitions, leave quotas, and compliance alerts:

```bash
php artisan test
```

---

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

---

## License

The Laravel framework and this project are open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
