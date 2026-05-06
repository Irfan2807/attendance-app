# Tap and Track

[![Laravel](https://img.shields.io/badge/Laravel-12-red?logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3-blue?logo=php)](https://php.net)
[![Filament](https://img.shields.io/badge/Filament-3.2-orange)](https://filamentphp.com)
[![License](https://img.shields.io/badge/License-MIT-green)](https://opensource.org/licenses/MIT)

A modern employee attendance tracking system built with **Laravel 12** and **Filament 3.2**. Track clock-ins, clock-outs, site assignments, vehicle mileage, and manage approvals with automated warnings and safety features.

---

## Features

| Feature | Description |
|---|---|
| 🕐 **Employee Clock In/Out** | Real-time attendance tracking with location verification |
| 🛡️ **Safety Net System** | Auto clock-out after 16 hours with 3-strike warning escalation |
| ✅ **Manager Approvals** | Review and approve/reject employee attendance records |
| 🏢 **Site Management** | Assign employees to multiple work sites with IP verification |
| 🚗 **Vehicle Tracking** | Log company vehicle usage and mileage |
| 📊 **Admin Panel** | Full Filament dashboard for managers and administrators |
| 👤 **Staff Portal** | Personal dashboard with attendance overview and statistics |
| ⚡ **Performance Optimized** | Lazy-loaded widgets, database indexing, and intelligent caching |

---

## Tech Stack

| Layer | Technology |
|---|---|
| **Backend** | Laravel 12, PHP 8.3 |
| **Admin UI** | Filament 3.2 |
| **Frontend** | Tailwind CSS 4, Vite 7 |
| **Database** | SQLite |
| **Caching** | File-based cache |

---

## Installation

```bash
# Clone the repository
git clone https://github.com/Irfan2807/attendance-app.git
cd attendance-app

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Build assets
npm run build

# Start the application
php artisan serve
```

---

## Access Points

| Portal | URL | Who |
|---|---|---|
| **Admin Panel** | `/admin` |  Administrators |
| **Staff Portal** | `/staff` | Managers & Staffs |
| **Public Site** | `/` | Landing page |

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

- **Auto Clock-Out** – Employees are automatically clocked out after 16 hours (configurable via `ATTENDANCE_MAX_SHIFT_HOURS`)
- **Warning System** – 3-strike escalation: warning → final warning → manager review
- **Location Verification** – IP-based site verification on clock-in
- **Manager Approval** – All critical attendance changes require approval

---

## Performance Optimizations

- Database indexes on frequently queried columns (`user_id`, `clock_in_time`, `status`)
- Lazy-loaded Filament widgets for faster dashboard loads
- 5–10 minute cache on statistics queries
- Chunked CSV exports with lazy collection processing
- Minified assets with esbuild

---

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

---

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
