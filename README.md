# Attendance App

[![Laravel](https://img.shields.io/badge/Laravel-12-red?logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue?logo=php)](https://php.net)
[![Filament](https://img.shields.io/badge/Filament-3.2-orange)](https://filamentphp.com)
[![License](https://img.shields.io/badge/License-MIT-green)](https://opensource.org/licenses/MIT)

A modern employee attendance tracking system built with **Laravel 12** and **Filament 3.2**. Track clock-ins, clock-outs, site assignments, vehicle mileage, and manage approvals with automated warnings and safety features.

---

## Features

| Feature | Description |
|---|---|
| 🕐 **Employee Clock In/Out** | Real-time attendance tracking with location verification |
| 🌙 **Operational-Day Logic** | Overnight and flexible shifts are grouped correctly; configurable day-start hour |
| 🛡️ **Safety Net System** | Auto clock-out after a configurable max shift hours with 3-strike warning escalation |
| ✅ **Manager Approvals** | Review and approve/reject employee attendance records with audit notes |
| 🏢 **Site Management** | Assign employees to multiple work sites with IP verification |
| 🚗 **Vehicle Tracking** | Log company vehicle usage and mileage |
| 📊 **Admin Panel** | Full Filament dashboard for managers and administrators |
| 👤 **Staff Portal** | Personal dashboard with attendance overview and statistics |
| ⚡ **Performance Optimized** | Lazy-loaded widgets, database indexing, and intelligent caching |

---

## Tech Stack

| Layer | Technology |
|---|---|
| **Backend** | Laravel 12, PHP 8.2+ |
| **Admin UI** | Filament 3.2 |
| **Frontend** | Tailwind CSS 4, Vite 7 |
| **Database** | SQLite (default) |
| **Caching** | File-based (configurable) |

---

## Installation

```bash
# Clone the repository
git clone https://github.com/Irfan2807/attendance-app.git
cd attendance-app

# Install dependencies
composer install
npm install

# Set up environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# (Optional) Seed demo data
php artisan db:seed

# Build assets
npm run build

# Start the application
php artisan serve
```

> **One-step setup:** `composer run setup` runs install, migrate, and build automatically.

---

## Access Points

| Portal | URL | Who |
|---|---|---|
| **Admin Panel** | `/admin` | Managers & Administrators |
| **Staff Portal** | `/staff` | Employees |
| **Public Site** | `/` | Landing page |

---

## Environment Configuration

Key variables to set in `.env` beyond the standard Laravel defaults:

```env
# Attendance logic
ATTENDANCE_DAY_START_HOUR=5     # Hour (0-23) that starts a new operational day
ATTENDANCE_MAX_SHIFT_HOURS=16   # Stale open shifts are auto-closed after this many hours
```

---

## Project Structure

```
app/
├── Filament/
│   ├── Resources/          # Admin-panel resources (Attendance, Site, User)
│   └── Staff/              # Staff-panel pages, resources, and widgets
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Responses/
├── Models/                 # Eloquent models
│   ├── Attendance.php
│   ├── AttendanceInfraction.php
│   ├── MileageLog.php
│   ├── Site.php
│   ├── User.php
│   └── Vehicle.php
├── Services/
│   ├── AttendanceMetricsService.php      # Statistics aggregation
│   ├── AttendanceVerificationService.php # IP / location checks
│   └── AttendanceWindowService.php       # Operational-day window logic
└── Providers/
database/
├── migrations/
├── seeders/
└── factories/
resources/
├── css/
├── js/
└── views/
routes/
├── web.php
└── console.php
docs/                       # Extended documentation
```

---

## Database Schema

| Table | Purpose |
|---|---|
| `users` | Employee accounts with roles (staff, manager, admin) |
| `attendances` | Clock in/out records with approval status |
| `attendance_infractions` | Warning tracking for policy violations |
| `sites` | Work locations with IP whitelisting |
| `vehicles` | Company vehicle assignments |
| `mileage_logs` | Vehicle usage tracking |

---

## Safety & Security Features

- **Auto Clock-Out** — Employees are automatically clocked out after `ATTENDANCE_MAX_SHIFT_HOURS`
- **Warning System** — 3-strike escalation: warning → final warning → manager review
- **Location Verification** — IP-based site verification (proxy-aware via `X-Forwarded-For`)
- **Manager Approval** — All critical attendance changes require manager sign-off

---

## Performance Notes

- Database indexes on `attendances(user_id, clock_in_time)`, `attendances(status)`, and other hot columns
- Lazy-loaded Filament widgets for faster dashboard loads
- Short-lived cache (2–10 minutes) on statistics queries
- Chunked CSV exports using `lazy()` for low memory usage
- Assets minified with esbuild via Vite

See [`docs/PERFORMANCE_OPTIMIZATION.md`](docs/PERFORMANCE_OPTIMIZATION.md) for the full guide.

---

## Documentation

| File | Description |
|---|---|
| [`docs/PROJECT_REPORT_SUMMARY.md`](docs/PROJECT_REPORT_SUMMARY.md) | Current state and FYP report |
| [`docs/PERFORMANCE_OPTIMIZATION.md`](docs/PERFORMANCE_OPTIMIZATION.md) | Performance tuning guide |
| [`docs/VIDEO_DEMO_SCRIPT.md`](docs/VIDEO_DEMO_SCRIPT.md) | Demo video narration script |
| [`docs/VIDEO_SHOT_CHECKLIST.md`](docs/VIDEO_SHOT_CHECKLIST.md) | Recording shot-by-shot checklist |

---

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

---

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
