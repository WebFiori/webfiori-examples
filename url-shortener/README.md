# URL Shortener

A URL shortener service with a web UI, redirect handling, click tracking, and caching. Built with [WebFiori Framework](https://webfiori.com) v3.

This example demonstrates caching, custom CLI commands, dynamic route-based redirects, and the health check pattern.

## Tech Stack

- **PHP 8.1+** with `mysqli` or `sqlsrv` extension
- **WebFiori Framework v3.0.0-RC0**
- **MySQL** or **MSSQL** database

## Setup

### 1. Install Dependencies

```bash
composer install
```

### 2. Add Database Connection

```bash
php webfiori add:db-connection
```

Use `shortener` as the connection name.

### 3. Initialize and Run Migrations

```bash
php webfiori migrations:ini --connection=shortener
php webfiori migrations:run --connection=shortener --env=dev
```

### 4. Start the Server

```bash
php -S localhost:8080 -t public
```

## Pages

| Page | URL | Description |
|------|-----|-------------|
| Home | `/` | Form to shorten a URL + list of recent links |

## API Endpoints

| Method | URL | Parameters | Description |
|--------|-----|------------|-------------|
| `GET` | `/apis/links` | `id` (optional) | List all links or get one by ID |
| `POST` | `/apis/links` | `url`, `expiresAt` (optional) | Create a short link |
| `DELETE` | `/apis/links` | `id` | Delete a short link |
| `GET` | `/apis/health` | — | Health check (DB connectivity) |
| `GET` | `/{code}` | — | Redirect to original URL |

## Caching

- On redirect (`/{code}`), the cache is checked first via `Cache::get("link:{code}")`
- On cache miss, the DB is queried and the result is cached with a 5-minute TTL
- On link creation, the new link is cached immediately
- On link deletion, the cache entry is invalidated
- The `links:cleanup` command also purges cache entries for expired links

## CLI Commands

```bash
php webfiori links:list      # Display all short links in a table
php webfiori links:stats     # Show total links, total clicks, top 10 most clicked
php webfiori links:cleanup   # Remove expired links from DB and purge their cache
```

## Running Tests

```bash
composer test
```

13 tests covering API endpoints (CRUD, validation, duplicates), health check, and CLI commands (`links:list`, `links:stats`, `links:cleanup`).

## Features Demonstrated

| Feature | How It Is Used |
|---------|---------------|
| Web Services (attributes) | `#[RestController]`, `#[GetMapping]`, `#[PostMapping]`, `#[DeleteMapping]`, `#[RequestParam]`, `#[ResponseBody]` |
| Database + Repository | `ShortLinkRepository` with custom queries (duplicate check, click increment, expiration, top clicked) |
| Caching (v3 instance API) | `Cache::get`, `set`, `delete` via `AppCache` singleton with `FileStorage` backend |
| CLI Commands | `links:list`, `links:stats`, `links:cleanup` using `$this->table()` for formatted output |
| CLI Testing | `CommandTestCase` for testing CLI commands |
| Dynamic Redirects | Closure route `/{code}` with cache-first lookup |
| Health Check | `/apis/health` endpoint for DB connectivity check |
| Link Expiration | Optional `expires_at` column, enforced on redirect and cleanup |
| API Testing | `APITestCase` for all CRUD endpoints + validation |
| Env Config (JSON) | DB connection, base URL |
| Migrations & Seeders | Table creation + sample links for dev/test |
