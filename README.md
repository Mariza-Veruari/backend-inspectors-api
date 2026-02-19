# Inspector Scheduling REST API

Symfony 6+ REST API: auditors, jobs, and job assignments. Allowed timezones: **Europe/Madrid**, **America/Mexico_City**, **Europe/London**. Datetimes are stored in UTC; API uses ISO 8601.

## Requirements

- PHP 8.2+
- Composer
- SQLite (default) or MySQL/PostgreSQL

## Setup

```bash
composer install
```

Set `DATABASE_URL` in `.env` (or `.env.local`):

```env
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
```

Create DB and run migrations:

```bash
mkdir -p var
php bin/console doctrine:migrations:migrate --no-interaction
```

Load fixtures (3 auditors, 10 open jobs):

```bash
composer require --dev doctrine/doctrine-fixtures-bundle
php bin/console doctrine:fixtures:load --no-interaction
```

## Run server

```bash
php -S localhost:8000 -t public
```

API base: **http://localhost:8000**

## Swagger UI

**http://localhost:8000/api/doc**

OpenAPI JSON: **http://localhost:8000/api/doc.json**

## Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | /api/jobs | List jobs (optional `?status=OPEN`, `ASSIGNED`, `COMPLETED`) |
| GET | /api/jobs/{id} | Get job (includes assignment if present) |
| POST | /api/jobs/{id}/assign | Assign job (body: `auditorId`, `scheduledAt` ISO 8601) |
| POST | /api/jobs/{id}/complete | Complete job (body: `auditorId`, `assessment`, `completedAt` optional) |

**Status codes:** 200 OK, 400 validation, 404 not found, 409 conflict.

## Example requests

List open jobs:

```bash
curl -s "http://localhost:8000/api/jobs?status=OPEN"
```

Get job 1:

```bash
curl -s http://localhost:8000/api/jobs/1
```

Assign job 1 to auditor 1:

```bash
curl -s -X POST http://localhost:8000/api/jobs/1/assign \
  -H "Content-Type: application/json" \
  -d '{"auditorId":1,"scheduledAt":"2025-03-15T09:00:00+01:00"}'
```

Complete job 1:

```bash
curl -s -X POST http://localhost:8000/api/jobs/1/complete \
  -H "Content-Type: application/json" \
  -d '{"auditorId":1,"assessment":"All checks passed","completedAt":"2025-03-15T11:30:00+01:00"}'
```

## Business rules

- Assign only when job status is `OPEN` and no assignment exists.
- On assign: status → `ASSIGNED`.
- Complete only when status is `ASSIGNED` and `auditorId` matches the assignment.
- On complete: status → `COMPLETED`.
- All datetimes: accept ISO 8601 with timezone; store UTC; return in auditor timezone where relevant.
