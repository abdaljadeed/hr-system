# HR Management System

![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3-38BDF8?logo=tailwindcss&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green)

A full-featured, role-based **HR Management System** built with Laravel and Tailwind CSS. It covers the
complete employee lifecycle — people & departments, attendance, leave, payroll, tasks, reporting, and an
audit trail — behind a clean, responsive dashboard with role-aware navigation, notifications, and Excel/PDF
exports. Business rules live in services and single-purpose actions; authorization is centralized through
Spatie Permission and policies.

## Screenshots

> _Add screenshots here._

| Login | Dashboard |
| --- | --- |
| `docs/screenshots/login.png` | `docs/screenshots/dashboard.png` |

| Payroll | Tasks Board |
| --- | --- |
| `docs/screenshots/payroll.png` | `docs/screenshots/tasks.png` |

## Features

- **Authentication & RBAC** — Laravel Breeze auth, 4 roles (Admin, HR Manager, Team Lead, Employee), Spatie permissions, `Gate::before` super-admin bypass.
- **Employees & Departments** — CRUD with User↔Employee linkage, file uploads, soft deletes, manager assignment.
- **Attendance** — check-in/out with integrity guards, manual entry, monthly reports, own/team/all scoping.
- **Leave Management** — request → approve/reject/cancel workflow, balance ledger, attendance sync, notifications.
- **Payroll** — monthly payslip generation snapshotting attendance & leave, bonuses/deductions, immutable-after-finalize, PDF payslips.
- **Tasks** — Kanban board, todo → in_progress → submitted → approved/rejected lifecycle, reassignment, comments, history, "due soon" badges.
- **Dashboard** — role-aware KPI cards with trend indicators, Chart.js analytics, birthdays & work anniversaries.
- **Notifications** — database notifications with topbar bell, unread badge, and full inbox.
- **Activity Log** — explicit audit trail of every write action, filterable by user/type/date.
- **Reports & Exports** — attendance, payroll, employee, leave, and performance reports as Excel and PDF, plus per-table quick exports.
- **Announcements** — company-wide announcements with dashboard banners (dismissible per session).
- **Self-Service** — employees update their own avatar, phone, address, and password.

## Tech Stack

| Layer | Technology |
| --- | --- |
| Framework | Laravel 13 (PHP 8.4) |
| Frontend | Blade, Tailwind CSS, Alpine.js, Vite |
| Charts | Chart.js |
| Auth | Laravel Breeze |
| Authorization | Spatie Laravel Permission + Policies |
| Audit | Spatie Laravel Activitylog |
| PDF | barryvdh/laravel-dompdf |
| Excel | maatwebsite/excel |
| Database | MySQL (SQLite supported for local dev) |

## Quick Start

```bash
# 1. Clone
git clone <your-repo-url> hr-system
cd hr-system

# 2. Install dependencies
composer install
npm install

# 3. Environment
cp .env.example .env
php artisan key:generate

# 4. Database (SQLite is the zero-config default in .env.example)
php artisan migrate --seed

# 5. Storage symlink (for avatar uploads)
php artisan storage:link

# 6. Build assets & serve
npm run build      # or: npm run dev
php artisan serve  # http://127.0.0.1:8000
```

> Using MySQL? Set `DB_CONNECTION=mysql` and the `DB_*` values in `.env`, then run `php artisan migrate:fresh --seed`.

To reseed the demo dataset at any time:

```bash
php artisan migrate:fresh --seed
# or just the demo data:
php artisan db:seed --class=DemoSeeder
```

## Demo Credentials

All demo accounts use the password **`password`**.

| Role | Email | Access |
| --- | --- | --- |
| Admin | `admin@hr.test` | Full access to everything |
| HR Manager | `hr@hr.test` | Employees, attendance, leave, payroll, reports |
| Team Lead | `lead@hr.test` | Team employees, tasks, leave approvals |
| Employee | `employee@hr.test` | Own attendance, leave, payslips, tasks |

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
