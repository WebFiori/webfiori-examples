# Personal Finance Tracker — Backend API

A pure REST API backend for a personal finance tracker. Designed to be consumed by separate SPA frontends (Vue, React, Angular). No server-rendered pages.

Built with [WebFiori Framework](https://webfiori.com) v3.0.0-RC1.

## Tech Stack

- **PHP 8.1+** with `mysqli` or `sqlsrv` extension
- **WebFiori Framework v3.0.0-RC1**
- **MySQL** or **MSSQL** database

## Project Structure

```
finance-tracker/
├── backend/                  # WebFiori API
│   ├── App/
│   │   ├── Apis/             # Auth, Accounts, Categories, Transactions, Budgets, Analytics
│   │   ├── Domain/           # User, Account, Category, Transaction, Budget
│   │   ├── Infrastructure/   # Repositories + Schema
│   │   ├── Database/         # Migrations + Seeders
│   │   └── Middleware/       # CORS + Auth
│   ├── tests/
│   └── public/
├── frontend/                 # SPA frontends (separate projects)
│   ├── vue/                  # Vue 3 + Vuetify 3
│   ├── react/                # React + MUI
│   └── angular/              # Angular + Angular Material
└── README.md
```

## Setup

```bash
cd backend
composer install
php webfiori add:db-connection   # use "finance" as name
php webfiori migrations:ini --connection=finance
php webfiori migrations:run --connection=finance --env=dev
php -S localhost:8080 -t public
```

**Demo user:** `demo@example.com` / `demo123`

## API Endpoints

### Auth
| Method | URL | Auth | Description |
|--------|-----|:----:|-------------|
| `POST` | `/apis/auth` | No | Login or register (`register=true`) |
| `GET` | `/apis/auth` | Yes | Get current user profile |

### Accounts
| Method | URL | Description |
|--------|-----|-------------|
| `GET` | `/apis/accounts` | List user's accounts |
| `POST` | `/apis/accounts` | Create account |
| `PUT` | `/apis/accounts` | Update account |
| `DELETE` | `/apis/accounts` | Delete account |

### Categories
| Method | URL | Description |
|--------|-----|-------------|
| `GET` | `/apis/categories` | List categories (global + user's custom) |
| `POST` | `/apis/categories` | Create custom category |

### Transactions
| Method | URL | Description |
|--------|-----|-------------|
| `GET` | `/apis/transactions` | List with filters (account, category, type, date range) |
| `POST` | `/apis/transactions` | Create transaction (updates account balance) |
| `DELETE` | `/apis/transactions` | Delete transaction |

### Budgets
| Method | URL | Description |
|--------|-----|-------------|
| `GET` | `/apis/budgets` | List budgets with spent amounts |
| `POST` | `/apis/budgets` | Create budget |
| `PUT` | `/apis/budgets` | Update budget |
| `DELETE` | `/apis/budgets` | Delete budget |

### Analytics
| Method | URL | Parameters | Description |
|--------|-----|------------|-------------|
| `GET` | `/apis/analytics` | `report=summary` | Total income, expenses, net |
| `GET` | `/apis/analytics` | `report=byCategory` | Spending by category (pie chart) |
| `GET` | `/apis/analytics` | `report=monthlyTrend` | Monthly income vs expenses (line chart) |
| `GET` | `/apis/analytics` | `report=accountBalances` | Account balances (bar chart) |

All analytics endpoints accept optional `fromDate` and `toDate` parameters.

## CORS

The `CorsMiddleware` adds `Access-Control-Allow-Origin: *` headers to all API responses, enabling cross-origin requests from SPA frontends running on different ports/domains.

## Data Scoping

All data is user-scoped. Each authenticated user only sees their own accounts, transactions, categories, and budgets. Queries filter by `user_id` from the session.

## Running Tests

```bash
composer test
```

21 tests covering auth (login, register, profile), accounts (CRUD), transactions (CRUD + filters), analytics (all report types), and auth enforcement.

## Frontend Projects

This backend is designed to be consumed by separate SPA frontends:

- `frontend-vue/` — Vue 3 + Vuetify 3
- `frontend-react/` — React + MUI
- `frontend-angular/` — Angular + Angular Material

Each frontend implements: login/register, dashboard with charts, transaction management, account management, and budget tracking.

## Features Demonstrated

| Feature | How It Is Used |
|---------|---------------|
| Pure API Backend | No server-rendered pages — JSON only |
| CORS Middleware | Enables cross-origin SPA access |
| Session Auth | Login/register with session-based authentication |
| User-Scoped Data | All queries filtered by authenticated user |
| Aggregation Queries | Analytics endpoints with GROUP BY, SUM, date functions |
| Cross-Database | MySQL + MSSQL compatible (DATE_FORMAT vs FORMAT) |
| Input Validation | Required params, type validation on all endpoints |
| API Testing | `APITestCase` with session mocking for authenticated tests |
