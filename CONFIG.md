# Config summary (config/packages and routes)

## config/packages/

- **framework.yaml** – Symfony framework: secret, serializer, error handling. No router config (default prefix is fine).
- **doctrine.yaml** – DBAL URL from `DATABASE_URL`, ORM with underscore naming. Use same URL in test env.
- **doctrine_migrations.yaml** – Migrations path: `DoctrineMigrations` → `%kernel.project_dir%/migrations`.
- **nelmio_api_doc.yaml** – API doc title/description/version; `path_patterns`: `^/api(?!/doc$)` so all `/api` routes except `/api/doc` are documented. Swagger UI is served by the routes below.

## config/routes.yaml

- **controllers** – All controllers under `src/Controller/` with attribute routing (so `/api/jobs`, `/api/inspectors`, `/api/assignments` come from controller attributes).
- **api_doc** – `GET /api/doc` → Nelmio Swagger UI.
- **api_docs** – `GET /api/docs` → same Swagger UI (alias for your requested path).
- **api_doc_json** – `GET /api/doc.json` → OpenAPI JSON spec.

## config/services.yaml

- **App\\** – Autowire/autoconfigure everything under `src/` except Entity, Kernel, DependencyInjection.
- **App\Service\TimezoneService** – Injected `$allowedTimezones` = `Inspector::ALLOWED_TIMEZONES` (Europe/Madrid, America/Mexico_City, Europe/London).

## Environment

- **.env** – `APP_ENV`, `APP_SECRET`, `DATABASE_URL` (default SQLite: `sqlite:///%kernel.project_dir%/var/data.db`).
- Create `var/` if missing (e.g. `mkdir var`). Run migrations: `php bin/console doctrine:migrations:migrate`.

## After install

1. `composer install`
2. `mkdir -p var` (or `mkdir var` on Windows)
3. `php bin/console doctrine:migrations:migrate`
4. Start server: `symfony server:start` or `php -S localhost:8000 -t public`
5. Open Swagger UI: **http://localhost:8000/api/docs** (or `/api/doc`)

No extra config is required in `config/packages` for the listed behaviour; the above files are sufficient.
