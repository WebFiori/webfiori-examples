# Enterprise Order System — Full Application Walkthrough

Example application for the blog post: [Building an Enterprise Order System with WebFiori v3](https://webfiori.com/blog/full-walkthrough)

## What This Demonstrates

All WebFiori features working together in one system:

- **REST APIs** — `#[RestController]`, `#[RequestParam]`, parameter injection
- **Authorization** — `#[RequiresAuth]`, `#[PreAuthorize]`, RBAC + ABAC policies
- **Repository pattern** — explicit mapping, database persistence
- **Job queue** — async payment processing with retry
- **Event dispatcher** — decoupled listeners for stock, email, analytics
- **Dependency injection** — `ContainerFacade::bind()` for testable interfaces
- **Middleware** — session, security context loading
- **Health checks** — auto-discovered from `App/Health/`
- **Structured logging** — throughout all layers

## Running

```bash
composer install
php webfiori migrations:run
php webfiori db:seed
php -S localhost:8080 -t public
```

## API Endpoints

### Authentication

```bash
# Login as customer
curl -X POST http://localhost:8080/apis/auth \
  -H "Content-Type: application/json" \
  -d '{"username": "alice", "password": "password"}'

# Login as admin
curl -X POST http://localhost:8080/apis/auth \
  -H "Content-Type: application/json" \
  -d '{"username": "admin", "password": "password"}'
```

### Orders

```bash
# Place an order (requires auth cookie from login)
curl -X POST http://localhost:8080/apis/orders \
  -b cookies.txt \
  -H "Content-Type: application/json" \
  -d '{"items": "[{\"productId\": 1, \"quantity\": 2}]"}'

# List orders
curl http://localhost:8080/apis/orders -b cookies.txt

# Get specific order
curl "http://localhost:8080/apis/orders?id=1" -b cookies.txt
```

### Health Check

```bash
curl http://localhost:8080/health
```

## Running Tests

```bash
composer test
```

## Related

- [Blog post](https://webfiori.com/blog/full-walkthrough)
- [Full documentation](https://webfiori.com/docs)
- [WebFiori Framework](https://github.com/WebFiori/framework)
