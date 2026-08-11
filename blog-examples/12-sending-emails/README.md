# Blog Example 03: Dependency Injection

Demonstrates WebFiori's DI container with interface-based binding, auto-resolution, and environment-based swapping.

## What It Shows

- `PaymentGatewayInterface` / `NotifierInterface` — contracts for business dependencies
- `LivePaymentGateway` / `LogNotifier` — production implementations
- `MockPaymentGateway` / `NullNotifier` — test implementations
- `OrderService` — business logic with constructor injection
- `Privileges.php` — environment-based binding registration
- `OrderApi` — REST endpoint that resolves `OrderService` from the container

## Run Tests

```bash
composer test
```

## Key Files

| File | Purpose |
|------|---------|
| `App/Domain/PaymentGatewayInterface.php` | Payment contract |
| `App/Domain/NotifierInterface.php` | Notification contract |
| `App/Domain/OrderService.php` | Business logic (injected dependencies) |
| `App/Infrastructure/LivePaymentGateway.php` | Production payment |
| `App/Infrastructure/MockPaymentGateway.php` | Test payment |
| `App/Infrastructure/LogNotifier.php` | Production notifier |
| `App/Infrastructure/NullNotifier.php` | Test notifier |
| `App/Ini/Privileges.php` | Container bindings |
| `App/Apis/OrderApi.php` | REST endpoint |
| `tests/OrderServiceTest.php` | Tests demonstrating DI |
