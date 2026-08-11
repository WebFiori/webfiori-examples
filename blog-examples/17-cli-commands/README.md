# Production Observability — Health Checks and Structured Logging

Example application for the blog post: [Production Observability with WebFiori](https://webfiori.com/blog/observability)

## What This Demonstrates

- **Health checks** with `HealthCheckInterface` — custom checks for database and external services
- **Built-in checks** — `StorageCheck` (writable directory) and `CacheCheck` (read/write cycle)
- **Callable checks** — register simple checks without a class
- **HTTP health endpoint** — returns 200/503 for load balancer integration
- **After-all callbacks** — trigger logging or alerts when checks fail
- **Structured logging** with `FileLogger` — daily rotation, level filtering, JSON context
- **LoggerFacade** — static convenience API for logging anywhere

## Running

```bash
composer install
php -S localhost:8080 -t public
```

## API Endpoints

### Health Check

The framework auto-registers a `/health` endpoint. Just register your checks.

```bash
# All checks pass (simulate by creating marker files)
touch App/Storage/.db-available
touch App/Storage/.payment-available
curl http://localhost:8080/health

# Simulate database failure
rm App/Storage/.db-available
curl -w "\nHTTP Status: %{http_code}\n" http://localhost:8080/health
```

### Create Order (demonstrates logging)

```bash
curl -X POST http://localhost:8080/apis/orders \
  -H "Content-Type: application/json" \
  -d '{"product": "Widget", "quantity": 3}'

# Check the log file
cat App/Storage/Logs/app-$(date +%Y-%m-%d).log
```

## Running Tests

```bash
composer test
```

## Related

- [Blog post](https://webfiori.com/blog/observability)
- [Health Checks docs](https://webfiori.com/docs/health-checks)
- [Logging docs](https://webfiori.com/docs/logging)
