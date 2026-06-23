# Blog Example 04: Middleware

Demonstrates WebFiori's middleware system: custom middleware, dependency resolution, priority, groups, and built-in rate limiting.

## What It Shows

- `AuditLogMiddleware` — Logs requests (low priority, runs first due to dependency)
- `ApiKeyMiddleware` — Validates API key header, depends on audit-log
- `ResponseTimerMiddleware` — Adds timing header, depends on api-key (transitive chain)
- Middleware groups and priority
- Built-in `RateLimitMiddleware` with parameterized construction

## Run Tests

```bash
composer test
```

## Key Files

| File | Purpose |
|------|---------|
| `App/Middleware/AuditLogMiddleware.php` | Custom logging middleware |
| `App/Middleware/ApiKeyMiddleware.php` | Auth middleware with dependency |
| `App/Middleware/ResponseTimerMiddleware.php` | Timing middleware (transitive deps) |
| `tests/MiddlewareTest.php` | Tests for all middleware features |
