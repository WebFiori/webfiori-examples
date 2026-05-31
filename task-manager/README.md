# Task Manager REST API

A pure REST API for managing tasks (to-do items) built with [WebFiori Framework](https://webfiori.com) v3. No UI — JSON input/output only.

This example demonstrates the foundational patterns of WebFiori: web services with PHP 8 attributes, database CRUD via the repository pattern, entities, migrations, seeders, input validation, and API testing.

## Tech Stack

- **PHP 8.1+** with the `sqlsrv` extension
- **WebFiori Framework v3.0.0-RC0**
- **MSSQL** (Microsoft SQL Server) database

## Project Structure

```
App/
├── Apis/
│   ├── TaskService.php              # REST controller (annotations-based)
│   └── TaskServicesManager.php      # Services manager
├── Domain/
│   └── Task.php                     # Entity
├── Infrastructure/
│   ├── Repository/
│   │   └── TaskRepository.php       # Data access
│   └── Schema/
│       └── TasksTable.php           # Table definition (PHP 8 attributes)
├── Database/
│   ├── Migrations/
│   │   └── CreateTasksTable.php     # Creates the tasks table
│   └── Seeders/
│       └── SeedSampleTasks.php      # Seeds sample task data
├── Config/
│   └── app-config.json              # Application configuration
└── Ini/
    └── Routes/
        └── APIsRoutes.php           # API route definitions
tests/
├── TaskServiceTest.php              # APITestCase tests
├── bootstrap.php                    # Test bootstrap
└── phpunit.xml                      # PHPUnit configuration
```

## Setup

### 1. Install Dependencies

```bash
composer install
composer update
```

### 2. Add Database Connection

```bash
php webfiori add:db-connection
```

When prompted, select `mssql` as the database type, provide your SQL Server details, and use `task-manager` as the connection name.

Alternatively, edit `App/Config/app-config.json` directly:

```json
"database-connections": {
    "task-manager": {
        "type": "mssql",
        "host": "localhost",
        "port": 1433,
        "username": "sa",
        "database": "task_manager",
        "password": "your_password",
        "extras": {
            "TrustServerCertificate": true,
            "Encrypt": false
        }
    }
}
```

> **Tip:** For local development, you can run MSSQL in Docker:
> ```bash
> docker run -e "ACCEPT_EULA=Y" -e "MSSQL_SA_PASSWORD=YourPass@123" \
>   -p 1433:1433 -d mcr.microsoft.com/mssql/server:2022-latest
> ```

### 3. Initialize and Run Migrations

```bash
php webfiori migrations:ini --connection=task-manager
php webfiori migrations:run --connection=task-manager --env=dev
```

This creates the `tasks` table and seeds it with sample data.

### 4. Start the Server

```bash
php -S localhost:8080 -t public
```

## API Endpoints

All endpoints are accessed via `/apis/tasks`. The service name `tasks` is passed as part of the URL path.

| Method | URL | Parameters | Description |
|--------|-----|------------|-------------|
| `GET` | `/apis/tasks` | `status` (optional): `pending` or `completed` | List all tasks, optionally filtered by status |
| `GET` | `/apis/tasks` | `id` (required): integer | Get a single task by ID |
| `POST` | `/apis/tasks` | `title` (required), `description` (optional) | Create a new task |
| `PUT` | `/apis/tasks` | `id` (required), `title`, `description`, `status` (all optional) | Update a task |
| `DELETE` | `/apis/tasks` | `id` (required) | Delete a task |

### Examples

**List all tasks:**
```bash
curl http://localhost:8080/apis/tasks
```

**List pending tasks:**
```bash
curl "http://localhost:8080/apis/tasks?status=pending"
```

**Get a task by ID:**
```bash
curl "http://localhost:8080/apis/tasks?id=1"
```

**Create a task:**
```bash
curl -X POST http://localhost:8080/apis/tasks \
  -d "title=Buy groceries" \
  -d "description=Milk, eggs, bread"
```

**Update a task:**
```bash
curl -X PUT http://localhost:8080/apis/tasks \
  -d "id=1" \
  -d "status=completed"
```

**Delete a task:**
```bash
curl -X DELETE "http://localhost:8080/apis/tasks?id=1"
```

## Running Tests

Tests require a running MSSQL instance with a `task-manager` connection configured in `App/Config/app-config.json`. Once configured, run:

```bash
composer test
```

## Features Demonstrated

| Feature | How It Is Used |
|---------|---------------|
| Web Services (attributes) | `#[RestController]`, `#[GetMapping]`, `#[PostMapping]`, `#[PutMapping]`, `#[DeleteMapping]`, `#[RequestParam]`, `#[ResponseBody]`, `#[AllowAnonymous]` |
| Database + Repository | `AbstractRepository` for CRUD on tasks table |
| Domain Entities | Plain PHP class `Task` with constructor promotion |
| Migrations | `CreateTasksTable` migration creates the table |
| Seeders | `SeedSampleTasks` populates sample data in dev/test |
| Input Validation | Required/optional params with type validation |
| Error Handling | `NotFoundException` for missing resources |
| Env Config (JSON) | `app-config.json` with MSSQL connection |
| API Testing | `APITestCase` for all endpoints |
| Routing | API routes registered in `APIsRoutes.php` |
| JSON Handling | All responses are JSON via `#[ResponseBody]` |
