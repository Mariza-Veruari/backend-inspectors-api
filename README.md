# Inspector Scheduling REST API

Symfony 6+ REST API for managing auditors, jobs, and assignments.

## Setup

1. Install dependencies:
```bash
composer install
```

2. Create database:
```bash
php bin/console doctrine:database:create
```

3. Run migrations:
```bash
php bin/console doctrine:migrations:migrate
```

4. Load fixtures:
```bash
php bin/console doctrine:fixtures:load
```

5. Start server:
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
