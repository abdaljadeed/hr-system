# HR Management System — Build Plan

> **STATUS: ✅ COMPLETE & PRODUCTION-READY** — all 10 phases + UI Sprints A & B + final production/portfolio
> pass + a full security/code-quality/functional audit shipped and verified. Audit fixes: closed an
> EmployeePolicy privilege-escalation (employees could PATCH their own salary via the employee edit form)
> and converted the `/` closure route to a cacheable redirect (`route:cache`-safe). Includes custom split-screen login, a rich `DemoSeeder` (5 departments,
> 15 employees, 6 months attendance, mixed leaves, 3 months finalized payroll, 20 tasks, 2 announcements),
> `storage:link` + public-disk avatars, professional `README.md`, and a cleaned `.env.example`. Final
> end-to-end workflow verified: create employee → assign task → approve leave → generate payroll.

## ✅ Completed (Phases 0–10)

All ten phases are built, seeded, and verified in the browser. Summary:

- **Phase 0 — Foundation:** Laravel 13 + Breeze (Blade) + Tailwind, Spatie Permission (4 roles) & Activitylog, dompdf, `Gate::before` admin bypass, responsive sidebar/topbar shell, seeded test accounts.
- **Phase 1 — Employees & Departments:** CRUD with User↔Employee linkage, file uploads, policies, form requests, `EmployeeService`, soft deletes.
- **Phase 2 — Attendance:** check-in/out with integrity guards, monthly report, manual entry, own/team/all scoping.
- **Phase 3 — Leave:** request → approve/reject/cancel workflow, balance ledger, attendance sync on approval, notifications.
- **Phase 4 — Payroll:** monthly payslip generation snapshotting attendance/leave, bonus/deduction items, immutable-after-finalize, PDF payslips.
- **Phase 5 — Tasks:** todo→in_progress→submitted→approved/rejected lifecycle, reassignment, comments, history timeline, notifications.
- **Phase 6 — Dashboard:** role-aware KPI cards + Chart.js (attendance trend, tasks doughnut, leave bar, top employees), `DashboardService`.
- **Phase 7 — Notifications:** topbar bell + unread badge + dropdown (view composer), full index, mark-(all-)read, delete.
- **Phase 8 — Activity Log:** explicit `activity()` logging across services/actions, filterable audit index (causer/type/date), Admin+HR gated.
- **Phase 9 — Reports & Exports:** `ReportService` + 4 Excel exports (maatwebsite/excel) + 3 dompdf reports + preview, Admin+HR gated.
- **Phase 10 — Hardening:** custom error pages, reusable empty states, centralized dismissible flash, submit-loading, mobile responsiveness, security & N+1 audits.

## 🚀 Next Steps (planned enhancements)

- **UI Sprint** ✅ shipped: toast notifications (`<x-toast>`, bottom-right, draining progress bar, 4 types) replacing flash; confirm dialog (`<x-confirm-dialog>` via `@confirm.window` + `$dispatch`) replacing native `confirm()`; grouped sidebar section labels (Main/People/Operations/Finance/System); dashboard stat cards with trend arrows + % vs previous period; topbar breadcrumb (`<x-breadcrumb>` via `@section('breadcrumb')`).
- **UI Sprint B** ✅ shipped:
  - Task "Due Soon" badge (`Task::is_due_soon` accessor, board badge + detail banner).
  - Birthdays & Work Anniversaries dashboard cards (Admin/HR only) via `DashboardService::getBirthdaysThisMonth/getAnniversariesThisMonth`.
  - Announcements system: `announcements` table, `Announcement` model + `active()` scope, `AnnouncementService` (create/update/deactivate with activity logging), `AnnouncementPolicy`, CRUD controller, index/create/edit views, dashboard banner (type→color, session-dismissible), sidebar link, `announcements.manage` permission (Admin + HR).
  - Employee self-service profile: tabbed `profile/edit` (Personal Info + Security), avatar (public disk) / phone / address / password update, `StoreProfileRequest`, topbar avatar + "My Profile" link.
  - Per-table Excel export buttons on employees/attendance/leave/payroll indexes (post current filters to `reports.excel`, gated Admin/HR).
- **Birthday reminders (remaining):** scheduled notifications to HR/managers (dashboard widget already shipped above).
- **Employee Self-Service Portal (remaining):** payslips, leave balance, and my-tasks shortcuts (profile edit already shipped above).

---

## Architecture Overview

A modular, monolithic Laravel application (Blade + Tailwind) following a thin-controller
architecture. Business rules live in **Services** and single-purpose **Actions**, validation
lives in **Form Requests**, and authorization is centralized through **Spatie Permission**
(roles + permissions) plus **Policies**.

Core design principle: **authentication is separated from HR identity.** A `User` row is only
for login + role. An `Employee` row holds all HR data and is linked one-to-one to a `User`
(nullable, so HR can create an employee before provisioning a login).

Cross-module data flows:
- **Attendance → Payroll**: payroll snapshots a month's attendance into immutable payslip rows.
- **Leave → Attendance**: approving leave writes `on_leave` attendance rows for those days.
- **Leave → Payroll**: unpaid leave days feed payroll deductions.
- **Roles → UI + Routes + Data scope**: role drives sidebar, route middleware, and row-level visibility.

### Tech Stack
- Laravel 13 (PHP 8.4) · Blade + Tailwind CSS · MySQL (SQLite fallback for local dev)
- Laravel Breeze (Blade auth) · Spatie Laravel Permission · Spatie Activitylog
- barryvdh/laravel-dompdf (payslips/reports) · Chart.js (dashboard)

---

## Database Schema (target)

Auth/RBAC: `users` (+ `is_active`), `roles`, `permissions`, Spatie pivots.
HR core: `employees` (1–1 `users`, soft deletes), `departments` (soft deletes), `employee_files`.
Attendance: `attendances` (unique `employee_id`+`date`).
Leave: `leave_types`, `leave_requests`, `leave_balances` (remaining = entitled − used, computed).
Payroll: `payrolls` (unique `employee_id`+year+month, no soft deletes), `payroll_items`.
Tasks: `tasks` (soft deletes, `assigned_to`/`assigned_by` → `users`), `task_comments`, `task_histories`.
System: `notifications` (native), `activity_log` (Spatie).

Full column-level schema is recorded in the Phase sections below.

---

## Roles & Permissions (seeded)

| Role | Scope |
|------|-------|
| Admin | Full access (Gate::before bypass) |
| HR Manager | Employees, departments, attendance, leave approval, payroll, reports |
| Team Lead | Team employees (view), tasks (manage/assign), leave approval, reports |
| Employee | Own attendance, leave requests, own payroll, assigned tasks |

Permission keys: `employees.*`, `departments.*`, `attendance.*`, `leaves.*`, `payroll.*`,
`tasks.*`, `reports.view`, `activitylog.view`, `users.manage`, `roles.manage`.

Seeded test accounts (password: `password`): `admin@hr.test`, `hr@hr.test`,
`lead@hr.test`, `employee@hr.test`.

---

## Build Phases

Status markers: `[ ]` not started · `[~]` in progress · `[x]` done

### Phase 0 — Foundation & Planning  `[x]`
Goal: clean, runnable project skeleton with auth, RBAC, base layout, and this plan.
Tables: `users` (+`is_active`), Spatie permission tables, `activity_log`.
- [x] Scaffold Laravel 13 project (`hr-system/`)
- [x] Install Breeze (Blade) + Tailwind
- [x] Install Spatie Permission, Spatie Activitylog, laravel-dompdf
- [x] `.env` configured for MySQL with SQLite fallback
- [x] `User` model: `HasRoles` trait + `is_active`
- [x] Register `role` / `permission` / `role_or_permission` middleware aliases
- [x] `Gate::before` super-admin bypass for Admin role
- [x] RolePermissionSeeder: 4 roles, base permissions, Admin + 3 sample users
- [x] Responsive sidebar + topbar layout with role-gated nav
- [x] Verified login + RBAC menu gating in browser
- [x] PLAN.md

### Phase 1 — Employees & Departments  `[x]`
Goal: HR core CRUD with User↔Employee linkage and file uploads.
Tables/Models: `departments`, `employees`, `employee_files`.
- [x] Migrations: `departments`, `employees` (FK `user_id` nullable, `department_id`), `employee_files`
- [x] Models + relationships (`User` 1–1 `Employee`, `Department` 1–* `Employee`, manager FK)
- [x] DepartmentPolicy / EmployeePolicy
- [x] Form Requests (Store/Update) for both
- [x] DepartmentController + EmployeeController (resourceful, thin)
- [x] EmployeeService (create employee + optional user provisioning, file handling)
- [x] File uploads (CV/contract/certificate) to `storage`, `employee_files` rows
- [x] Blade: index (table + filters), create/edit forms, employee profile page
- [x] Wire sidebar links + route protection
- [x] Seed sample departments + employees

### Phase 2 — Attendance  `[x]`
Goal: check-in/out with integrity guards and monthly report.
Tables/Models: `attendances`.
- [x] Migration `attendances` (unique `employee_id`+`date`)
- [x] AttendanceService (open-session rule, no double check-in, hours calc)
- [x] CheckIn / CheckOut Actions
- [x] Controller + Form Requests
- [x] Blade: today widget, monthly report table
- [x] Policies + scoping (employee sees own; lead sees team; HR all)

### Phase 3 — Leave Management  `[x]`
Goal: request workflow, approval, balance ledger, attendance sync.
Tables/Models: `leave_types`, `leave_requests`, `leave_balances` (+ native `notifications`).
- [x] Migrations for the three tables (+ `notifications`)
- [x] Seed `leave_types` (annual, sick, unpaid) + yearly `leave_balances`
- [x] LeaveRequestService coordinating request / approve / reject / cancel
- [x] Approve upserts `on_leave` attendance rows + decrements balance (transactional)
- [x] Controller + Form Requests + Policy (own/team/all scoping)
- [x] Blade: request form, approval queue, balance display, request detail
- [x] Notifications on request (→ approvers) / approve+reject (→ employee)

### Phase 4 — Payroll  `[x]`
Goal: monthly payslip generation with immutable archive + PDF.
Tables/Models: `payrolls`, `payroll_items`.
- [x] Migrations (unique employee+year+month, NO soft deletes)
- [x] PayrollService: generate (snapshots attendance/leave/bonus/deduction), addBonus, addDeduction, removeItem, finalize, markPaid, recalculateNet, generatePdf
- [x] Immutable once finalized — guardDraft() throws on any edit attempt
- [x] dompdf payslip PDF template
- [x] Controller + Form Requests (GeneratePayrollRequest, AddPayrollItemRequest) + PayrollPolicy
- [x] Blade: index (month navigator, employee list with status + totals row), show (breakdown + item CRUD + action buttons)
- [x] Notification (PayrollFinalized → employee on finalize)

### Phase 5 — Tasks System  `[x]`
Goal: task lifecycle, reassignment, comments, history.
Tables/Models: `tasks`, `task_comments`, `task_histories`.
- [x] Migrations (`assigned_to`/`assigned_by` → users, soft deletes)
- [x] TaskService + ChangeTaskStatus / ReassignTask Actions (writes `task_histories`)
- [x] Status flow: todo → in_progress → submitted → approved/rejected (transition map on Task model)
- [x] Controller + Form Requests + TaskPolicy (own/team/all scoping, ability-per-transition)
- [x] Blade: Kanban board, detail with status actions + reassign + comments + history timeline
- [x] Notifications on assign/submit/approve/reject (database channel)
- [x] Seeded sample tasks across all statuses; manual verify (board, approve flow, history, notifications)

### Phase 6 — Dashboard  `[x]`
Goal: role-aware KPIs and charts.
- [x] DashboardService aggregating per-role stats (getStats, getAttendanceTrend, getTasksByStatus, getLeavesByType, getTopEmployees) — scoped via existing model accessibleBy scopes, no raw SQL
- [x] DashboardController (thin) → enhanced existing dashboard view; route now points to controller
- [x] Stat cards (active employees, present today, pending leaves, open tasks, monthly payroll total)
- [x] Chart.js (CDN + @stack('scripts')): attendance trend (line), tasks by status (doughnut), leave by type (bar) + top employees table
- [x] Role-based rendering: Employee = check-in + own trend + own task doughnut; Manager (Admin/HR/Lead) = full dashboard. Verified in browser for both.

### Phase 7 — Notifications  `[x]`
Goal: surface DB notifications in the UI.
- [x] `notifications` migration (native) — done in Phase 3
- [x] Notification classes for leave/task/payroll events — done in Phases 3/4/5 (unchanged this phase)
- [x] NotificationController (index/markAsRead/markAllAsRead/destroy) + routes — markAsRead redirects to the notification's target url
- [x] NotificationComposer (shares unread count + recent 5) registered in AppServiceProvider via View::composer on layouts.topbar, Auth::check guarded
- [x] Topbar bell + unread badge (server-rendered, hidden at 0) + dropdown (last 5 unread, "View all")
- [x] notifications/index full-page list (unread first via orderByRaw, paginated 15) + type→icon partial + empty state
- [x] Verified in browser: badge 7→6 on read, mark-all clears + hides badge, delete removes row, click navigates to resource

### Phase 8 — Activity Log  `[x]`
Goal: audit trail of create/edit/approve actions.
- [x] Explicit `activity()` logging in Services/Actions (writes only: employee create/update/delete, leave approve/reject, payroll generate/finalize/markPaid, task create/status-change/reassign, attendance manual-entry) — NOT model traits, NOT check-in/out. Causer = passed actor where available, else `auth()->user()`.
- [x] ActivityLogController (thin, 20/page, filters: causer/subject_type/date range), route `/activity-log` gated `role:Admin|HR Manager`
- [x] activity/index view (filter bar + table with causer role badge + soft-delete-aware subject links + pagination + empty state)
- [x] Wired sidebar Activity Log link (`@can('activitylog.view')` → Admin + HR Manager)
- [x] Verified in browser: 18 seeded entries, Type filter, live causer attribution (HR Manager markPaid), Team Lead blocked with 403

### Phase 9 — Reports & Exports  `[x]`
Goal: attendance, payroll, performance, top-performer reports.
- [x] Installed maatwebsite/excel 3.1.69 (+ published config)
- [x] ReportService (shared scoped query logic via accessibleBy: attendance/payroll/employees/leaves/performance)
- [x] Excel exports: Attendance, Payroll, EmployeeList, Leave (FromCollection + WithHeadings + WithMapping + ShouldAutoSize)
- [x] PDF views: attendance, payroll, employee-performance (dompdf, company header + filter summary + totals)
- [x] ReportController (index/exportExcel/exportPdf/preview, thin — query logic in ReportService, mapping in Exports) + routes gated `role:Admin|HR Manager`
- [x] reports/index with filter cards (Preview / Excel / PDF via formaction+formmethod) + sidebar link (`@hasanyrole('Admin|HR Manager')`)
- [x] Verified: all 7 export paths 200 (valid %PDF-/xlsx MIME), HTML previews with totals, Team Lead blocked (403, no sidebar link)

### Phase 10 — Hardening & Polish  `[x]`
- [x] Custom error pages (403/404/500) using app layout + reusable `<x-empty-state>`; topbar made guest-safe so the layout renders for unauthenticated errors
- [x] Empty states on index views via reusable `<x-empty-state>` (icon + heading + subtext + contextual action button)
- [x] Flash messages centralized in app layout (`<x-flash>`, dismissible, auto-hide); confirmed every write action already redirects with success/error
- [x] Loading state: global submit handler disables submit buttons on form submit (skips GET `_blank` previews)
- [x] Mobile responsiveness: sidebar hamburger toggle (Alpine, verified), tables wrapped in `overflow-x-auto`, dashboard cards stack to 1 col, full-width inputs
- [x] Security audit: routes auth+role/permission gated, download ownership enforced (employee files + payroll PDF), all 13 models have `$fillable`
- [x] N+1 audit: every index controller eager-loads its relations (employees, departments, attendance, leaves, payroll, tasks, activity)

---

## Conventions

### Naming
- Tables: plural snake_case (`leave_requests`). Models: singular StudlyCase (`LeaveRequest`).
- Pivots: alphabetical singular (`permission_role`). FKs: `<singular>_id`.
- Controllers: `XxxController` (resourceful verbs only). Form Requests: `StoreXxxRequest` / `UpdateXxxRequest`.
- Services: `XxxService` (stateful/coordinating). Actions: single verb-noun `VerbNoun` (`GeneratePayslip`).
- Policies: `XxxPolicy`. Routes: kebab plural (`/leave-requests`), named `resource.action`.
- Permissions: `module.ability` lowercase dotted. Roles: Title Case display names.

### Folder Structure
```
app/
  Actions/<Module>/      single-purpose actions
  Services/<Module>/     coordinating business logic
  Http/Controllers/      thin controllers
  Http/Requests/<Module>/ form requests
  Models/                Eloquent models
  Policies/              authorization
  Notifications/         notification classes
resources/views/
  layouts/  (app, sidebar, topbar)
  <module>/ (index, create, edit, show)
database/
  migrations/  seeders/  factories/
```

### Coding Standards
- **No comments in code.** Code must be self-explanatory via naming.
- Thin controllers: validate via Form Request → delegate to Service/Action → return view/redirect.
- All multi-step writes wrapped in DB transactions.
- Authorization via Policies + `@can` in Blade; never inline role string checks in controllers.
- Data scoping (own/team/all) centralized in query scopes, not scattered conditionals.
- Mass assignment via explicit `$fillable`. Money as `decimal(12,2)`. Enums via DB enum or PHP enum casts.
- Follow PSR-12; run `./vendor/bin/pint` before considering a phase done.
- Every phase ends with: migration + model + request + policy + service/action + controller + views + seed + manual verify.

### Local Run
```
# SQLite (instant): DB_CONNECTION=sqlite in .env
php artisan migrate:fresh --seed
php artisan serve         # http://127.0.0.1:8000
npm run dev               # Vite (or npm run build)

# MySQL (target): set DB_* + DB_CONNECTION=mysql, then migrate:fresh --seed
```
