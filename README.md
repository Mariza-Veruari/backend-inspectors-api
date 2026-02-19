# Inspector Scheduling REST API

Symfony 6+ REST API for managing auditors, jobs, and assignments.

## Quick run (same as pre-installed on my machine)

The repo includes a **ready-to-use database** (`sample-data.db`). To run the project the same way it runs on my laptop:

1. **Clone** the repo.
2. **Install dependencies:** `composer install`
3. **Use the included database:**
   - Windows: `mkdir var 2>nul & copy sample-data.db var\data.db`
   - Linux/Mac: `mkdir -p var && cp sample-data.db var/data.db`
4. **Start the server:** `php -S 127.0.0.1:8000 -t public`

Then open **http://127.0.0.1:8000/api/doc** for Swagger UI. No migrations or fixtures needed.

---

## Database

- **Engine:** SQLite  
- **File:** `var/data.db` (created on first migrate)  
- **Tables:** `auditor`, `job`, `job_assignment`  

A **sample database** is included in the repo: `sample-data.db` (3 auditors, 10 open jobs). To use it instead of creating a fresh DB:

- **Windows:** `mkdir var 2>nul & copy sample-data.db var\data.db`
- **Linux/Mac:** `mkdir -p var && cp sample-data.db var/data.db`

Then start the server; no need to run migrations or fixtures.

## Setup

1. Install dependencies:
```bash
composer install
```

2. **Option A – Use included sample database:**
```bash
mkdir -p var
copy sample-data.db var\data.db
```

**Option B – Create database from scratch:**
```bash
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

3. Start server:
```bash
php -S 127.0.0.1:8000 -t public
```

## Swagger UI

Access interactive API documentation at:

**http://127.0.0.1:8000/api/doc**

## API Endpoints

- `GET /api/jobs?status=OPEN` - List jobs (optional status filter)
- `GET /api/jobs/{id}` - Get job details (includes assignment if exists)
- `POST /api/jobs/{id}/assign` - Assign job to auditor
- `POST /api/jobs/{id}/complete` - Complete job assignment

## Business Rules

- Jobs can only be assigned if status is OPEN and no assignment exists
- Jobs can only be completed if status is ASSIGNED and auditorId matches assignment
- On assign: status changes to ASSIGNED
- On complete: status changes to COMPLETED
- All datetimes are stored in UTC and converted to/from auditor timezone in responses
