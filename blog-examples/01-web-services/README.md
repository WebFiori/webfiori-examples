# Blog Post #1: Building REST APIs with WebFiori v3

This example accompanies the blog post on building REST APIs using WebFiori v3's annotation-based web services.

## What This Demonstrates

- `#[RestController]` for declarative service definition
- `#[GetMapping]`, `#[PostMapping]`, `#[PutMapping]`, `#[DeleteMapping]` for HTTP verb mapping
- `#[RequestParam]` for input validation with types
- `#[MapEntity]` for automatic request-to-object mapping
- `#[Validate]` for cross-field validation
- `#[ResponseBody]` for JSON serialization
- `ServiceRouter::discover()` for auto-registration
- `OpenAPIGenerator` for generating OpenAPI 3.1 specs from annotations
- `ServiceTestCase` for testing services directly without HTTP server or manager

## Project Structure

```
App/
├── Apis/
│   ├── ProductService.php          ← CRUD API with annotations
│   ├── UserService.php             ← Registration with cross-field validation
│   └── OpenAPIService.php          ← Serves generated OpenAPI 3.1 spec
├── Domain/
│   └── Product.php                 ← Plain entity class
├── Config/
│   └── app-config.json             ← App configuration
└── Ini/
    └── Routes/
        └── APIsRoutes.php          ← ServiceRouter::discover() registration
tests/
├── ProductServiceTest.php          ← CRUD operation tests
└── UserServiceTest.php             ← Validation tests
```

## Quick Start

```bash
composer install
php -S localhost:8080 -t public
```

## Try It

```bash
# List all products
curl http://localhost:8080/apis/products

# Get a product by ID
curl http://localhost:8080/apis/products?id=1

# Filter by category
curl "http://localhost:8080/apis/products?category=Electronics"

# Create a product
curl -X POST http://localhost:8080/apis/products \
  -H "Content-Type: application/json" \
  -d '{"name":"Monitor","category":"Electronics","price":299.99,"in-stock":true}'

# Register a user (demonstrates cross-field validation)
curl -X POST http://localhost:8080/apis/users \
  -H "Content-Type: application/json" \
  -d '{"name":"John","email":"john@example.com","password":"securepass123","password_confirm":"securepass123"}'

# Trigger validation error
curl -X POST http://localhost:8080/apis/users \
  -H "Content-Type: application/json" \
  -d '{"name":"John","email":"john@example.com","password":"short","password_confirm":"different"}'

# Get OpenAPI spec
curl http://localhost:8080/apis/openapi
```

## Run Tests

```bash
composer test
```

## Related

- [Blog post: Building REST APIs with WebFiori v3](https://webfiori.com/blog/web-services)
- [Web Services documentation](https://webfiori.com/docs/web-services)
- [WebFiori Framework](https://github.com/WebFiori/framework)
