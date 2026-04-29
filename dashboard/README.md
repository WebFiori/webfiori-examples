# Multi-Tenant Dashboard with Role-Based Access

An admin dashboard with role-based access control, audit logging, switchable themes, and background report generation. Built with [WebFiori Framework](https://webfiori.com) v3.

This capstone example combines nearly every framework feature: privileges, middleware chains, multiple repositories, themes, i18n, background tasks, CLI commands, email notifications, and audit logging.

## Tech Stack

- **PHP 8.1+** with `mysqli` or `sqlsrv` extension
- **WebFiori Framework v3.0.0-RC0**
- **MySQL** or **MSSQL** database

## Setup

```bash
composer install
php webfiori add:db-connection   # use "dashboard" as name
php webfiori migrations:ini --connection=dashboard
php webfiori migrations:run --connection=dashboard --env=dev
php -S localhost:8080 -t public
```

**Default users:**

| Email | Password | Role |
|-------|----------|------|
| admin@example.com | admin123 | Admin |
| manager@example.com | manager123 | Manager |
| viewer@example.com | viewer123 | Viewer |

## Roles & Privileges

Privileges use the framework's `Access` group hierarchy. Assigning a parent group gives all child group privileges:

```
SYSTEM_ADMIN (Admin)
├── MANAGE_USERS, MANAGE_SETTINGS, VIEW_AUDIT_LOG
└── PROJECT_MANAGEMENT (Manager)
    ├── CREATE_PROJECT, EDIT_PROJECT, DELETE_PROJECT
    └── REPORTING
        ├── VIEW_REPORTS, GENERATE_REPORTS
        └── BASE (Viewer)
            └── VIEW_PROJECTS
```

| Role | Total Privileges |
|------|:---:|
| Admin | 9 (all) |
| Manager | 6 (project + reporting + base) |
| Viewer | 1 (view projects only) |

## Pages

| Page | URL | Role |
|------|-----|------|
| Login | `/login` | None |
| Dashboard | `/dashboard` | Any authenticated |
| Projects | `/projects` | Any (create form for Manager+) |
| Project Detail | `/projects/{id}` | Any |
| Reports | `/reports` | Any (generate button for Manager+) |
| Users | `/admin/users` | Admin (add user form) |
| Audit Log | `/admin/audit-log` | Admin |
| Settings | `/admin/settings` | Admin (theme + language switching) |

## API Endpoints

| Method | URL | Auth | Description |
|--------|-----|:----:|-------------|
| `POST` | `/apis/auth` | No | Login |
| `GET` | `/apis/auth` | Yes | Get profile + privileges |
| `GET` | `/apis/users` | Yes | List users |
| `POST` | `/apis/users` | Admin | Create user (sends welcome email) |
| `PUT` | `/apis/users` | Admin | Update user |
| `DELETE` | `/apis/users` | Admin | Deactivate user |
| `GET` | `/apis/projects` | Viewer+ | List projects |
| `POST` | `/apis/projects` | Manager+ | Create project |
| `PUT` | `/apis/projects` | Manager+ | Update project |
| `DELETE` | `/apis/projects` | Manager+ | Delete project |
| `GET` | `/apis/reports` | Viewer+ | List reports |
| `POST` | `/apis/reports` | Manager+ | Generate report |
| `GET` | `/apis/audit-log` | Admin | View audit log (filterable) |

## Middleware Chain

Request flow for protected routes (in priority order):

1. **StartSessionMiddleware** (PHP_INT_MAX) — Resume/start session
2. **AuthMiddleware** (200) — Verify user is logged in, redirect to `/login` if not
3. **RefreshUserProfileMiddleware** (150) — Reload privileges from DB
4. **RoleCheckMiddleware** (100) — Check required privilege for route
5. **AuditLogMiddleware** (50) — Log POST/PUT/DELETE operations to `audit_log` table

## Themes

Two switchable themes via Settings page (`/admin/settings`):
- **Light** — [Water.css Light](https://watercss.kognise.dev/)
- **Dark** — [Water.css Dark](https://watercss.kognise.dev/)

Theme preference is stored in the session and applied via `BasePage`.

## Internationalization

English and Arabic support. Switch via Settings page or `?lang=AR`/`?lang=EN` on any page. All page titles, labels, and table headers use `$this->get()` for translation.

## Email Notifications

- **Welcome email** sent when admin creates a new user
- When no SMTP is configured, emails are stored as HTML in `App/Storage/Logs/emails/` using `SendMode::TEST_STORE`

To configure SMTP: `php webfiori add:smtp-connection` (use `no-reply` as account name).

## Background Task

`GenerateWeeklyReportTask` runs every Monday at 7:00 AM. Aggregates project stats and stores a report.

```bash
php webfiori scheduler --force --task-name=generate-weekly-report
```

## CLI Commands

```bash
php webfiori users:list                                    # List all users in a table
php webfiori users:create --name=X --email=X --password=X  # Create a new user
php webfiori reports:generate                              # Generate project summary report
```

## Environment Configuration

The application uses a single `app-config.json`. Environment-specific values (database credentials, SMTP, verbose mode) are configured at deploy time using CLI commands:

```bash
php webfiori add:db-connection    # Set DB credentials per environment
php webfiori add:smtp-connection  # Set SMTP per environment
```

Set `WF_VERBOSE` to `true` for development and `false` for production in the `env-vars` section of `app-config.json`.

## API Documentation (Swagger)

- **Swagger UI**: `/api-docs` — interactive API explorer
- **OpenAPI spec (JSON)**: `/apis/openapi` — raw OpenAPI 3.1.0 specification

The spec is auto-generated from the registered services using `WebServicesManager::toOpenAPI()`. All services and parameters include descriptions via `#[RestController]` and `#[RequestParam]` annotations.

## Running Tests

```bash
composer test
```

21 tests covering auth (login, profile), users (CRUD + auth checks), projects (CRUD + auth), reports (auth), and audit log (admin-only access).

## Features Demonstrated

| Feature | How It Is Used |
|---------|---------------|
| Privileges & Access | `Access::newGroup` with parent-child inheritance, `User::addToGroup()` |
| Middleware Chain | 5 middleware in priority order: session → auth → profile → role → audit |
| Audit Logging | `AuditLogMiddleware` captures all POST/PUT/DELETE with user context |
| Theme Switching | Light/Dark themes per user preference via session |
| i18n | English + Arabic with `$this->get()` for all UI strings |
| Email Notifications | Welcome email on user creation, `SendMode::TEST_STORE` fallback |
| Background Tasks | Weekly report generation via `AbstractTask` |
| CLI Commands | `users:create`, `users:list`, `reports:generate` with `$this->table()` |
| Session Auth | Login/logout with privilege loading via `User::addToGroup()` |
| Multiple Repositories | User, Project, Report, AuditLog |
| Env Config | Single `app-config.json`, credentials set via CLI per environment |
| API Testing | `APITestCase` with `SessionsManager` for authenticated tests |
| OpenAPI / Swagger | Auto-generated spec via `toOpenAPI()`, Swagger UI at `/api-docs` |
