# Background Job Queue — Priority, Retry, and Zero Infrastructure

Example application for the blog post: [Background Job Queue in WebFiori](https://webfiori.com/blog/job-queue)

## What This Demonstrates

- **Job interface** — `handle()`, `getMaxAttempts()`, `getRetryDelaySeconds()`
- **Dispatching** — basic, with priority, and with delay
- **Processing** — `QueueFacade::process()` runs pending jobs
- **Retry with backoff** — failed jobs retry until max attempts, then move to failed queue
- **Failed job management** — view, retry, flush
- **Payload encryption** — set `QUEUE_KEY` env var to encrypt at rest

## Running

```bash
composer install
php -S localhost:8080 -t public
```

## API Endpoints

```bash
# Dispatch a welcome email job
curl -X POST http://localhost:8080/apis/jobs/dispatch-email \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com", "name": "Alice"}'

# Dispatch a report job (with priority)
curl -X POST http://localhost:8080/apis/jobs/dispatch-report \
  -H "Content-Type: application/json" \
  -d '{"report-id": 42}'

# Check queue status
curl http://localhost:8080/apis/jobs/status

# Process pending jobs
php webfiori queue:work
```

## Running Tests

```bash
composer test
```

## Related

- [Blog post](https://webfiori.com/blog/job-queue)
- [Job Queue docs](https://webfiori.com/docs/job-queue)
