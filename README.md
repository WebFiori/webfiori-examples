# WebFiori Framework Examples

A collection of real-world example projects built with [WebFiori Framework](https://webfiori.com) v3. Each project demonstrates specific framework features, progressing from simple to complex.

## Prerequisites

- **PHP 8.1+** with `mysqli` and/or `sqlsrv` extensions
- **Composer** for dependency management
- **MySQL** or **MSSQL** database

## Examples

### 1. [Task Manager REST API](task-manager/)

A pure REST API for managing tasks (to-do items). No UI — JSON input/output only. The simplest example and entry point for learning WebFiori v3.

| Feature | Details |
|---------|---------|
| Web Services | `#[RestController]`, `#[GetMapping]`, `#[PostMapping]`, `#[PutMapping]`, `#[DeleteMapping]`, `#[RequestParam]`, `#[ResponseBody]` |
| Database | `AbstractRepository` for CRUD, PHP 8 attribute-based table definitions |
| Migrations & Seeders | Table creation + sample data |
| Error Handling | `NotFoundException` for missing resources |
| Testing | `APITestCase` for all endpoints |

---

### 2. [Blog Application](blog/)

A full-stack blog with server-rendered pages, REST API backend, session-based auth, and internationalization.

| Feature | Details |
|---------|---------|
| WebPage + Themes | Custom `BlogTheme` with header, footer, aside, CSS |
| Sessions | Login/logout via `SessionsManager` |
| Middleware | `AuthMiddleware` protecting admin routes |
| i18n | English + Arabic with RTL support |
| Pagination | Paginated post listing |
| Database | 4 tables with join queries (posts, comments, categories, authors) |
| Testing | `APITestCase` for posts, categories, auth, comments |

---

### 3. [Support Ticket System](support-tickets/)

A support ticket system with file attachments, email notifications, background tasks, and rate limiting.

| Feature | Details |
|---------|---------|
| File Uploads | `FileUploader` with type restrictions, integrated into ticket creation |
| Email Sending | Ticket confirmation, reply notification, daily digest |
| Background Tasks | `AbstractTask` for daily digest with `--test` flag for HTML preview |
| Rate Limiting | Custom middleware to prevent spam |
| SendMode | `SendMode::TEST_STORE` for local email testing without SMTP |
| Testing | `APITestCase` + `RateLimitMiddleware` unit tests |

---

### 4. [URL Shortener](url-shortener/)

A URL shortener with caching, custom CLI commands, dynamic redirects, and a health check endpoint.

| Feature | Details |
|---------|---------|
| Caching | Cache v3 instance API via `AppCache` singleton with `FileStorage` |
| CLI Commands | `links:list`, `links:stats`, `links:cleanup` with `$this->table()` |
| Dynamic Redirects | Closure route `/{code}` with cache-first lookup |
| Health Check | `/apis/health` endpoint for DB connectivity |
| Link Expiration | Optional `expires_at`, enforced on redirect and cleanup |
| Testing | `APITestCase` + `CommandTestCase` for CLI commands |

---

### 5. [Multi-Tenant Dashboard](dashboard/)

An admin dashboard with role-based access control, audit logging, switchable themes, and Swagger API documentation. The capstone example combining nearly every framework feature.

| Feature | Details |
|---------|---------|
| Privileges | `Access::newGroup` with parent-child inheritance, 3 roles (Admin, Manager, Viewer) |
| Middleware Chain | 5 middleware: session → auth → profile refresh → role check → audit log |
| Audit Logging | All POST/PUT/DELETE operations logged with user context |
| Themes | Light/Dark switching per user preference |
| i18n | English + Arabic on all pages |
| Email | Welcome email on user creation |
| Background Tasks | Weekly report generation |
| CLI Commands | `users:create`, `users:list`, `reports:generate` |
| OpenAPI / Swagger | Auto-generated spec at `/apis/openapi`, Swagger UI at `/api-docs` |
| Testing | `APITestCase` with authenticated session testing |

## Quick Start

Each example follows the same setup pattern:

```bash
cd <example-folder>
composer install
php webfiori add:db-connection    # Follow prompts, use the connection name from the example's README
php webfiori migrations:ini --connection=<name>
php webfiori migrations:run --connection=<name> --env=dev
php -S localhost:8080 -t public   # Start development server
composer test                     # Run tests
```

## Framework Features Coverage

| Feature | Ex.1 | Ex.2 | Ex.3 | Ex.4 | Ex.5 |
|---------|:----:|:----:|:----:|:----:|:----:|
| Web Services (attributes) | ✅ | ✅ | ✅ | ✅ | ✅ |
| Database + Repository | ✅ | ✅ | ✅ | ✅ | ✅ |
| Migrations & Seeders | ✅ | ✅ | ✅ | ✅ | ✅ |
| WebPage + UI Package | | ✅ | ✅ | ✅ | ✅ |
| Themes | | ✅ | | | ✅ |
| Sessions | | ✅ | | | ✅ |
| Middleware | | ✅ | ✅ | | ✅ |
| i18n | | ✅ | | | ✅ |
| File Uploads | | | ✅ | | |
| Email Sending | | | ✅ | | ✅ |
| Background Tasks | | | ✅ | | ✅ |
| Caching | | | | ✅ | |
| CLI Commands | | | | ✅ | ✅ |
| Privileges & Access | | | | | ✅ |
| Audit Logging | | | | | ✅ |
| OpenAPI / Swagger | | | | | ✅ |
| API Testing | ✅ | ✅ | ✅ | ✅ | ✅ |
| CLI Testing | | | | ✅ | |

## Database Compatibility

All examples are tested with both **MySQL** and **MSSQL**. Table schemas use both `autoIncrement` (MySQL) and `identity` (MSSQL) flags for cross-database support. The `DataType::TEXT` auto-maps to `nvarchar` on MSSQL via the framework's type mapping system.

## License

MIT
