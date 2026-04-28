# Blog Application

A full-stack blog application with server-rendered pages and a REST API backend, built with [WebFiori Framework](https://webfiori.com) v3.

This example covers the complete request lifecycle: routing, themed HTML output, sessions-based authentication, middleware, database relationships, pagination, and internationalization (i18n).

## Tech Stack

- **PHP 8.1+** with `mysqli` or `sqlsrv` extension
- **WebFiori Framework v3.0.0-RC0**
- **MySQL** or **MSSQL** database

## Project Structure

```
App/
├── Apis/
│   ├── AuthService.php              # Login API
│   ├── PostService.php              # Post CRUD API
│   ├── CategoryService.php          # Category API
│   ├── CommentService.php           # Comment API
│   └── BlogServicesManager.php      # Registers all services
├── Domain/
│   ├── Author.php, Post.php, Category.php, Comment.php
├── Infrastructure/
│   ├── Repository/
│   │   ├── AuthorRepository.php, PostRepository.php
│   │   ├── CategoryRepository.php, CommentRepository.php
│   └── Schema/
│       ├── AuthorsTable.php, PostsTable.php
│       ├── CategoriesTable.php, CommentsTable.php
├── Database/
│   ├── Migrations/CreateBlogTables.php
│   └── Seeders/SeedBlogContent.php
├── Pages/
│   ├── HomePageView.php             # Paginated post listing
│   ├── PostDetailView.php           # Single post with comments
│   ├── CategoryPostsView.php        # Posts by category
│   ├── LoginPage.php                # Admin login form
│   └── admin/
│       ├── DashboardPage.php        # Post management
│       ├── PostEditorPage.php       # Create/edit post
│       └── CategoriesPage.php       # Category management
├── Middleware/AuthMiddleware.php     # Protects admin routes
├── Langs/LangEN.php, LangAR.php    # English & Arabic i18n
├── Themes/BlogTheme/BlogTheme.php   # Custom blog theme
└── Ini/Routes/
    ├── PagesRoutes.php              # Page routes (public + admin)
    └── APIsRoutes.php               # API routes
tests/
├── PostServiceTest.php, CategoryServiceTest.php, AuthServiceTest.php
```

## Setup

### 1. Install Dependencies

```bash
composer install
```

### 2. Add Database Connection

```bash
php webfiori add:db-connection
```

Use `blog` as the connection name. Example for MySQL:

```json
"database-connections": {
    "blog": {
        "type": "mysql",
        "host": "127.0.0.1",
        "port": 3306,
        "username": "root",
        "database": "blog_app",
        "password": "your_password"
    }
}
```

### 3. Initialize and Run Migrations

```bash
php webfiori migrations:ini --connection=blog
php webfiori migrations:run --connection=blog --env=dev
```

This creates all 4 tables and seeds sample content (1 author, 3 categories, 5 posts, 2 comments).

**Default admin credentials:** `admin@example.com` / `admin123`

### 4. Start the Server

```bash
php -S localhost:8080 -t public
```

## Pages

| Page | URL | Auth | Description |
|------|-----|:----:|-------------|
| Home | `/` | No | Paginated published posts with sidebar categories |
| Post Detail | `/posts/{slug}` | No | Full post with comments and comment form |
| Category Posts | `/categories/{slug}` | No | Posts filtered by category |
| Login | `/login` | No | Admin login form |
| Dashboard | `/admin` | Yes | All posts with edit links |
| Post Editor | `/admin/posts/create`, `/admin/posts/{id}/edit` | Yes | Create or edit a post |
| Categories | `/admin/categories` | Yes | List and add categories |

## API Endpoints

| Method | URL | Auth | Description |
|--------|-----|:----:|-------------|
| `GET` | `/apis/posts` | No | List published posts (paginated) |
| `GET` | `/apis/posts?id=1` | No | Get single post |
| `POST` | `/apis/posts` | Yes | Create post |
| `PUT` | `/apis/posts` | Yes | Update post |
| `DELETE` | `/apis/posts` | Yes | Delete post |
| `GET` | `/apis/categories` | No | List categories |
| `POST` | `/apis/categories` | Yes | Create category |
| `POST` | `/apis/comments` | No | Add comment to post |
| `POST` | `/apis/auth` | No | Login |

## Running Tests

```bash
composer test
```

Tests require a database with the `blog` connection configured and migrations applied.

## Features Demonstrated

| Feature | How It Is Used |
|---------|---------------|
| Web Services (attributes) | `#[RestController]`, `#[GetMapping]`, `#[PostMapping]`, `#[PutMapping]`, `#[DeleteMapping]`, `#[RequestParam]`, `#[ResponseBody]`, `#[AllowAnonymous]` |
| Database + Repository | 4 repositories with relationships, raw SQL joins, pagination |
| Domain Entities | `Post`, `Comment`, `Category`, `Author` with constructor promotion |
| Migrations & Seeders | Single migration creates all tables; seeder populates sample content |
| Routing | Page routes + API routes + grouped admin routes with middleware |
| WebPage + UI Package | Server-rendered HTML pages using `HTMLNode` |
| Themes | Custom `BlogTheme` with header, footer, aside, CSS |
| Sessions | Login/logout via `SessionsManager` |
| Middleware | `AuthMiddleware` protects admin routes |
| i18n | English (`LangEN`) and Arabic (`LangAR`) with RTL support |
| Pagination | Paginated post listing on home page and API |
| Error Handling | `NotFoundException`, `UnauthorizedException` for API errors |
| API Testing | `APITestCase` tests for posts, categories, auth, and comments |
