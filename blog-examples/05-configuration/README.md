# Configuration & Environment Variables

Example application for the blog post: [Configuration and Environment Variables in WebFiori](https://webfiori.com/blog/configuration)

## What This Demonstrates

- **`app-config.json`** — centralized configuration file
- **Environment variables** — custom constants (`APP_ENV`, `MAX_UPLOAD_SIZE`, `API_RATE_LIMIT`)
- **Database connection** — SQLite configured via JSON
- **`App::getConfig()`** — typed runtime access to all settings
- **`env:` prefix** — reference system environment variables for secrets

## Running

```bash
composer install
php -S localhost:8080 -t public
```

## API Endpoint

```bash
# Get application config (non-sensitive)
curl http://localhost:8080/apis/config
```

Response:

```json
{
    "app_name": "Configuration Demo",
    "version": "1.0.0",
    "environment": "development",
    "primary_language": "EN",
    "max_upload_size": 10485760,
    "api_rate_limit": 100
}
```

## Running Tests

```bash
composer test
```

## Related

- [Blog post](https://webfiori.com/blog/configuration)
- [Environment Variables docs](https://webfiori.com/docs/env-vars)
- [Database docs](https://webfiori.com/docs/database)
