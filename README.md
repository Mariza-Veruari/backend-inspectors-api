# Inspector Scheduling REST API

Symfony 6/7 REST API: inspectors claim jobs, set a scheduled date, mark completed, and add assessments.  
Timezones: **Europe/Madrid**, **America/Mexico_City**, **Europe/London**. Datetimes are stored in UTC; request/response use inspector timezone (ISO 8601).

---

## Setup

```bash
composer install
```

Configure the database in `.env` (or `.env.local`):

```bash
# SQLite (default)
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"

# Or MySQL
# DATABASE_URL="mysql://user:password@127.0.0.1:3306/inspectors?serverVersion=8.0"

# Or PostgreSQL
# DATABASE_URL="postgresql://user:password@127.0.0.1:5432/inspectors?serverVersion=15"
```

Create the database and run migrations:

```bash
mkdir -p var
php bin/console doctrine:migrations:migrate --no-interaction
```

---

## Run the server

```bash
php -S localhost:8000 -t public
```

Or with the Symfony CLI:

```bash
symfony server:start
```

API base URL: **http://localhost:8000**

---

## Swagger UI

Interactive API docs (Swagger UI) are available at:

**http://localhost:8000/api/docs**

OpenAPI JSON spec: **http://localhost:8000/api/doc.json**

---

## Example curl calls

Assume the server is running at `http://localhost:8000`. You need at least one **Job** and one **Inspector** in the database (e.g. created via a fixture or manually) before assigning.

### GET /api/jobs — List open jobs

```bash
curl -s http://localhost:8000/api/jobs
```

Example response:

```json
{"items":[{"id":1,"title":"Inspection at Site A","status":"open"}]}
```

---

### POST /api/jobs/{id}/assign — Assign job to inspector

`scheduleAt` must be ISO 8601 in the **inspector’s** timezone (e.g. `Europe/Madrid` → `+01:00` in winter).

```bash
curl -s -X POST http://localhost:8000/api/jobs/1/assign \
  -H "Content-Type: application/json" \
  -d '{"inspectorId":1,"scheduleAt":"2025-03-15T09:00:00+01:00"}'
```

Example response:

```json
{"id":1,"jobId":1,"inspectorId":1,"scheduledAt":"2025-03-15T09:00:00+01:00","status":"scheduled"}
```

---

### GET /api/inspectors/{id}/schedule — Get inspector schedule

```bash
curl -s http://localhost:8000/api/inspectors/1/schedule
```

Example response:

```json
{
  "inspectorId":1,
  "timezone":"Europe/Madrid",
  "assignments":[
    {
      "id":1,
      "jobId":1,
      "jobTitle":"Inspection at Site A",
      "scheduledAt":"2025-03-15T09:00:00+01:00",
      "status":"scheduled",
      "assessment":null,
      "completedAt":null
    }
  ]
}
```

---

### POST /api/assignments/{id}/complete — Complete assignment with assessment

```bash
curl -s -X POST http://localhost:8000/api/assignments/1/complete \
  -H "Content-Type: application/json" \
  -d '{"assessment":"All checks passed. Minor repairs recommended."}'
```

Example response:

```json
{
  "id":1,
  "jobId":1,
  "inspectorId":1,
  "scheduledAt":"2025-03-15T09:00:00+01:00",
  "completedAt":"2025-03-15T11:30:00+01:00",
  "status":"completed",
  "assessment":"All checks passed. Minor repairs recommended."
}
```

---

## Endpoints summary

| Method | Path | Description |
|--------|------|-------------|
| GET | /api/jobs | List open jobs |
| POST | /api/jobs/{id}/assign | Assign job to inspector (body: `inspectorId`, `scheduleAt`) |
| GET | /api/inspectors/{id}/schedule | List assignments for inspector |
| POST | /api/assignments/{id}/complete | Complete assignment (body: `assessment`) |

**HTTP codes:** 400 validation error, 404 not found, 409 conflict (e.g. job already assigned, assignment already completed).

---

## Config

See **CONFIG.md** for `config/packages` and routes (Nelmio, Doctrine, framework, services).
