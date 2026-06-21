# WebFiori Framework — Developer Experience Findings & Enhancement Proposals

**Date:** 2026-05-31
**Context:** Building the Order Processing System example (enterprise features showcase)
**Framework Version:** v3.0.0-RC.5

---

## 1. `Access::can()` Should Read Roles from SecurityPrincipal

### Problem

`AccessManager::can($user, $permission, $resource)` checks the user's roles by calling `$this->getUserRoles($userId)`, which looks up an internal `$userRoles` map populated only by `Access::assignRoleToUser()`.

Even though the `$user` object implements `SecurityPrincipal` and has a `getRoles()` method, `can()` ignores it. This forces developers to manually sync two sources of truth:

```php
// Must do BOTH — setting the principal AND assigning the role
SecurityContext::setCurrentUser($user);        // provides getRoles()
Access::assignRoleToUser($user->getId(), $role); // populates internal map
```

If you forget `assignRoleToUser()`, `can()` always returns `false` even though the user clearly has the role.

### Current Behavior

```php
// AccessManager.php
public function can($user, string $permission, ?object $resource = null): bool {
    $userId = is_object($user) && method_exists($user, 'getId') ? $user->getId() : $user;
    $roles = $this->getUserRoles($userId); // ← only checks internal map

    // If no roles found in map → permission denied immediately
    // Never checks $user->getRoles()
}
```

### Proposed Fix

```php
public function can($user, string $permission, ?object $resource = null): bool {
    $userId = is_object($user) && method_exists($user, 'getId') ? $user->getId() : $user;
    $roles = $this->getUserRoles($userId);

    // Fallback: if no roles in storage, check the user object directly
    if (empty($roles) && is_object($user) && method_exists($user, 'getRoles')) {
        $roles = $user->getRoles();
    }

    // ... rest of permission check
}
```

### Impact

- Eliminates the need for `assignRoleToUser()` in most cases
- SecurityPrincipal becomes the single source of truth for user roles
- `assignRoleToUser()` remains useful for database-backed role storage scenarios
- Zero breaking changes (additive fallback)

---

## 2. `#[RequiresAuth]` Should Check SecurityContext, Not `isAuthorized()`

### Problem

When `#[RequiresAuth]` is present on a method, the framework calls `$this->isAuthorized()` first. The default implementation of `isAuthorized()` returns `false`. This means:

1. Developer adds `#[RequiresAuth]` to a method
2. Developer sets up SecurityContext correctly with a valid user
3. Request is rejected with 401 — no obvious reason why
4. Developer must override `isAuthorized()` in every service class

The attribute name "RequiresAuth" strongly implies "check if the user is authenticated." The current behavior makes it mean "check if `isAuthorized()` returns true AND the user is authenticated."

### Current Behavior

```php
// WebService.php — authorization check flow
if ($hasRequiresAuth) {
    if (!$this->isAuthorized()) {  // ← defaults to false!
        return false;
    }
    // Then check PreAuthorize...
    return true;
}
```

### Proposed Fix

```php
if ($hasRequiresAuth) {
    if (!SecurityContext::isAuthenticated()) {  // ← check authentication directly
        return false;
    }
    // Then check PreAuthorize...
    return true;
}
```

### Workaround (Current)

Every service that uses `#[RequiresAuth]` must override:

```php
public function isAuthorized(): bool {
    return SecurityContext::isAuthenticated();
}
```

Or avoid `#[RequiresAuth]` entirely and use `#[PreAuthorize("isAuthenticated()")]` instead.

### Impact

- `#[RequiresAuth]` works as its name implies without boilerplate
- `isAuthorized()` remains available for custom auth logic (backward compatible if called only when no auth attributes are present)
- Reduces confusion for new developers

---

## 3. `AbstractRepository::save()` Should Auto-Load Schema

### Problem

`AbstractRepository::save()` calls `$this->db->table($tableName)->insert($data)->execute()`. Without the table schema loaded into the `Database` instance, the framework cannot properly map column names (hyphen ↔ underscore conversion, bracket escaping for MSSQL, etc.).

This forces every repository to include boilerplate in its constructor:

```php
public function __construct(Database $db) {
    parent::__construct($db);
    $table = AttributeTableBuilder::build(MyTable::class, $db->getConnectionInfo()->getDatabaseType());
    $db->addTable($table);
}
```

If you forget this, `save()` generates invalid SQL silently (wrong column names), leading to cryptic database errors.

### Proposed Solutions (Pick One)

**Option A: Abstract method for schema class**

```php
abstract class AbstractRepository {
    abstract protected function getSchemaClass(): ?string; // e.g., ProductsTable::class

    public function __construct(Database $db) {
        $this->db = $db;
        $schemaClass = $this->getSchemaClass();
        if ($schemaClass !== null) {
            $table = AttributeTableBuilder::build($schemaClass, $db->getConnectionInfo()->getDatabaseType());
            $db->addTable($table);
        }
    }
}
```

**Option B: Auto-discover from table name**

The framework could scan `App/Infrastructure/Schema/` for a class with a `#[Table(name: '...')]` attribute matching `getTableName()`.

**Option C: Lazy-load on first `save()`**

If `save()` detects the table isn't registered, it could attempt to find and load the schema automatically.

### Impact

- Eliminates 4 lines of boilerplate per repository
- Prevents silent failures from missing schema registration
- Makes the "happy path" work without extra setup

---

## 4. Column `name` Parameter Should Be Respected Literally

### Problem

The `#[Column(name: 'created_at')]` attribute creates a database column named `created-at` (hyphenated). The framework silently converts underscores to hyphens in the column name, ignoring the developer's explicit naming.

This causes confusion because:
- Raw SQL queries must use `[created-at]` (with brackets on MSSQL) instead of `created_at`
- The `name` parameter suggests you control the exact column name
- Developers coming from other frameworks expect `name` to be literal
- Mixing raw SQL with framework queries requires knowing the conversion rule

### Current Behavior

```php
#[Column(name: 'created_at', type: DataType::DATETIME)]
// Creates DB column: created-at (not created_at)
// select() returns key: created-at
// toArray() must use: 'created-at'
// Raw SQL must use: [created-at]
```

### Proposed Fix

**Option A: Respect `name` literally**

If `name: 'created_at'` is specified, create the column as `created_at`. Only apply hyphen conversion when `name` is not provided (derived from property name).

**Option B: Document the convention prominently**

If the conversion is intentional (framework convention), document it clearly:
- In the `#[Column]` attribute docblock
- In the migration guide
- In the README's database section

Add a note: "All column names are normalized to kebab-case (hyphens). The `name` parameter specifies the logical name before normalization."

**Option C: Add a `raw` or `literal` flag**

```php
#[Column(name: 'created_at', literal: true)] // creates created_at exactly
#[Column(name: 'created_at')]                 // creates created-at (default behavior)
```

### Impact

- Reduces surprise for developers familiar with other ORMs
- Makes raw SQL interop predictable
- Eliminates a class of bugs where developers use underscored names in raw queries

---

## 5. `APITestCase` — Document the `$user` Parameter

### Problem

`APITestCase::postRequest()` has a 5th parameter `?SecurityPrincipal $user = null` that is the correct way to test authenticated endpoints. However:

- It's not immediately obvious from the method signature (positional parameter after `$httpHeaders`)
- The natural instinct is to set `SecurityContext::setCurrentUser()` before calling the request method — which doesn't work because the test case manages the context internally
- The `tearDown()` method clears SecurityContext, so any manual setup is lost between assertions

### Current Signature

```php
public function postRequest(
    WebServicesManager $manager,
    string $endpoint,
    array $parameters = [],
    array $httpHeaders = [],
    ?SecurityPrincipal $user = null  // ← easy to miss
): string
```

### Proposed Improvements

1. **Add a helper method** for readability:
   ```php
   public function actingAs(SecurityPrincipal $user): static {
       $this->currentUser = $user;
       return $this;
   }
   ```
   Usage: `$this->actingAs($admin)->postRequest($mgr, 'products', [...])`

2. **Document in class docblock** with an example showing authenticated testing

3. **Add a note in the error message** when a 401 is returned and no user was provided:
   "Hint: Pass a SecurityPrincipal as the 5th parameter to test authenticated endpoints"

---

## 6. Facade Discovery — README Should Reference Facade Classes

### Problem

The framework README lists features like "Dependency Injection" and "Event Dispatcher" but doesn't mention the actual class names developers need to use (`ContainerFacade`, `EventDispatcherFacade`). The `App` class doesn't expose these directly (no `App::getContainer()` or `App::getEventDispatcher()`).

A developer reading the README knows the feature exists but has to dig through vendor source to find the entry point.

### Current README

> **Dependency Injection**
> - Container with `bind()`, `singleton()`, and `instance()` registration
> - Automatic constructor dependency resolution

### Proposed README Addition

> **Dependency Injection**
> - `ContainerFacade::bind()`, `::singleton()`, `::instance()`, `::make()`
> - Or via `App::container()` for the underlying `Container` instance
> - Automatic constructor dependency resolution

Same for Event Dispatcher:

> **Event Dispatcher**
> - `EventDispatcherFacade::listen()`, `::dispatch()`
> - Decoupled event-driven architecture

---

## Summary Table

| # | Issue | Severity | Breaking Change? | Effort |
|---|-------|----------|-----------------|--------|
| 1 | `Access::can()` ignores SecurityPrincipal roles | High | No (additive fallback) | Low |
| 2 | `#[RequiresAuth]` calls `isAuthorized()` instead of checking auth | High | Potentially (behavior change) | Low |
| 3 | `AbstractRepository::save()` requires manual schema loading | Medium | No (additive) | Medium |
| 4 | Column `name` parameter silently converted to hyphens | Medium | Yes if changed | Low (docs) |
| 5 | `APITestCase` `$user` parameter not discoverable | Low | No | Low |
| 6 | Facade classes not mentioned in README | Low | No | Low |

---

## Workarounds Used in Order Processing System

For reference, here's how each issue was worked around in the example:

```php
// Issue 1: Assign role manually in SecurityContextLoader middleware
Access::assignRoleToUser($user->getId(), $user->role);

// Issue 2: Override isAuthorized() in service classes
public function isAuthorized(): bool {
    return SecurityContext::isAuthenticated();
}

// Issue 3: Load schema in repository constructor
public function __construct(Database $db) {
    parent::__construct($db);
    $table = AttributeTableBuilder::build(OrdersTable::class, $db->getConnectionInfo()->getDatabaseType());
    $db->addTable($table);
}

// Issue 4: Use hyphens everywhere (toArray, toEntity, where clauses)
// Raw SQL uses bracket-escaped names: [created-at]

// Issue 5: Pass SecurityPrincipal as 5th arg to request methods
$this->postRequest($mgr, 'orders', $params, [], $this->currentUser);

// Issue 6: Use facade classes directly
ContainerFacade::bind(Interface::class, Implementation::class);
EventDispatcherFacade::listen(Event::class, $listener);
```
