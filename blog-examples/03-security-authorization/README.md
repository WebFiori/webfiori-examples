# Blog Example 03: Security & Authorization

Demonstrates WebFiori's layered security system: SecurityPrincipal, SecurityContext, RBAC roles/permissions, ABAC policies, and declarative annotations.

## What It Shows

- `User` implementing `SecurityPrincipal` with roles and authorities
- `Order` entity for resource-level policy checks
- `OrderCancelPolicy` — ABAC: only pending orders, only owner or admin
- `Privileges.php` — Role definitions and policy registration
- `OrderService` — API with `#[RequiresAuth]` and `#[PreAuthorize]`
- Tests verifying SecurityContext, RBAC, and ABAC

## Important: Permission ID Format

Permission IDs only accept `[A-Za-z0-9_]`. Use underscores, not dots:
- ✅ `orders.create`
- ❌ `orders.create` (silently falls back to default ID)

## Run Tests

```bash
composer test
```

## Key Files

| File | Purpose |
|------|---------|
| `App/Domain/User.php` | SecurityPrincipal implementation |
| `App/Domain/Order.php` | Resource entity |
| `App/Policies/OrderCancelPolicy.php` | ABAC policy |
| `App/Ini/Privileges.php` | Role + policy registration |
| `App/Apis/OrderService.php` | Protected API endpoints |
| `tests/SecurityTest.php` | Tests for all security layers |
