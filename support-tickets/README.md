# Support Ticket System

A support ticket system where users can submit tickets with file attachments and receive email notifications. Built with [WebFiori Framework](https://webfiori.com) v3.

This example demonstrates file uploads, email sending, background tasks (CRON scheduling), rate-limiting middleware, and form validation.

## Tech Stack

- **PHP 8.1+** with `mysqli` or `sqlsrv` extension
- **WebFiori Framework v3.0.0-RC0**
- **MySQL** or **MSSQL** database

## Project Structure

```
App/
├── Apis/
│   ├── TicketService.php            # Ticket CRUD + file upload API
│   ├── ReplyService.php             # Ticket replies API
│   ├── AttachmentService.php        # File upload/download API
│   └── TicketServicesManager.php    # Registers all services
├── Domain/
│   ├── Ticket.php, Reply.php, Attachment.php
├── Infrastructure/
│   ├── Repository/
│   │   ├── TicketRepository.php, ReplyRepository.php, AttachmentRepository.php
│   └── Schema/
│       ├── TicketsTable.php, RepliesTable.php, AttachmentsTable.php
├── Database/
│   ├── Migrations/CreateTicketTables.php
│   └── Seeders/SeedSampleTickets.php
├── Pages/
│   ├── SubmitTicketPage.php         # Ticket submission form with file upload
│   ├── TicketListPage.php           # All tickets table
│   └── TicketDetailPage.php         # Ticket detail with replies
├── Middleware/
│   └── RateLimitMiddleware.php      # Rate-limits POST requests
├── Tasks/
│   └── SendDailyDigestTask.php      # Daily email digest of open tickets
└── Ini/Routes/
    ├── PagesRoutes.php
    └── APIsRoutes.php
tests/
├── TicketServiceTest.php
├── ReplyServiceTest.php
└── AttachmentServiceTest.php
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

Use `tickets` as the connection name.

### 3. Initialize and Run Migrations

```bash
php webfiori migrations:ini --connection=tickets
php webfiori migrations:run --connection=tickets --env=dev
```

### 4. (Optional) Add SMTP Connection

For sending real email notifications:

```bash
php webfiori add:smtp-connection
```

Use `no-reply` as the account name. If no SMTP is configured, all emails (ticket confirmation, reply notification, daily digest) are automatically stored as HTML files in `App/Storage/Logs/emails/` for preview.

### 5. Start the Server

```bash
php -S localhost:8080 -t public
```

## Pages

| Page | URL | Description |
|------|-----|-------------|
| Submit Ticket | `/submit` | Form to create a new ticket with file attachments |
| All Tickets | `/tickets` | Table listing all tickets with links to details |
| Ticket Detail | `/tickets/{id}` | View ticket with replies, attachments, and reply form |

## API Endpoints

| Method | URL | Parameters | Description |
|--------|-----|------------|-------------|
| `GET` | `/apis/tickets` | `status`, `email` (optional) | List tickets |
| `GET` | `/apis/tickets` | `id` | Get ticket with replies and attachments |
| `POST` | `/apis/tickets` | `subject`, `description`, `submitterName`, `submitterEmail`, `priority` + `file` (multipart) | Create ticket with optional file attachments |
| `PUT` | `/apis/tickets` | `id`, `status` | Update ticket status (open/in-progress/closed) |
| `POST` | `/apis/replies` | `ticketId`, `authorName`, `content` | Add reply to a ticket |
| `POST` | `/apis/attachments` | `ticketId` + `file` (multipart) | Upload attachment to existing ticket |
| `GET` | `/apis/attachments` | `id` | Download an attachment |

### File Upload

Ticket creation and the attachment endpoint accept file uploads via `multipart/form-data`. The file input name must be `file`. Allowed types: `pdf`, `doc`, `docx`, `png`, `jpg`, `jpeg`, `txt`, `zip`. Files are stored in `App/Storage/Uploads/tickets/{ticketId}/`.

## Email Notifications

| Trigger | Recipient | Content |
|---------|-----------|---------|
| Ticket created | Submitter | Confirmation with ticket ID and priority |
| Reply added | Submitter | Notification with reply content |
| Daily digest | Support staff | Summary of open tickets grouped by priority |

When no SMTP connection is configured, all emails are stored as HTML files in `App/Storage/Logs/emails/` using `SendMode::TEST_STORE`. You can open them in a browser to preview the email content.

## Background Task

The `SendDailyDigestTask` runs daily at 8:00 AM and sends a summary email of all open tickets grouped by priority (high, medium, low).

### Running the Scheduler

Add a crontab entry for production:

```
* * * * * php /path/to/project/webfiori scheduler:run
```

### Testing the Task

Force-run the task with the `--test` flag to store the email as an HTML file instead of sending via SMTP (no SMTP configuration needed):

```bash
php webfiori scheduler --force --task-name=send-daily-digest --test
```

The email is saved to `App/Storage/Logs/emails/` as an HTML file that you can open in a browser to preview the digest content.

To test with real SMTP sending (requires SMTP configured):

```bash
php webfiori scheduler --force --task-name=send-daily-digest
```

The `--test` flag uses `SendMode::TEST_STORE` from the WebFiori mailer library, which saves the full email including headers and body as an HTML page.

## Running Tests

```bash
composer test
```

Tests require a database with the `tickets` connection configured and migrations applied.

## Features Demonstrated

| Feature | How It Is Used |
|---------|---------------|
| Web Services (attributes) | `#[RestController]`, `#[GetMapping]`, `#[PostMapping]`, `#[PutMapping]`, `#[RequestParam]`, `#[ResponseBody]` |
| Database + Repository | 3 repositories for tickets, replies, attachments |
| File Uploads | `FileUploader` with type restrictions, integrated into ticket creation |
| Email Sending | `EmailMessage` for ticket confirmation, reply notification, and daily digest; `SendMode::TEST_STORE` for local testing |
| Background Tasks | `AbstractTask` with custom `--test` execution argument |
| Rate Limiting | Custom middleware to prevent spam ticket creation |
| Input Validation | Required params, email validation, file type restrictions |
| Error Handling | `NotFoundException`, `BadRequestException` |
| API Testing | `APITestCase` for all endpoints |
