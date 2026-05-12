# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this project is

theindex.fyi is a curated meta-index of indie web and small web index sites — a manually maintained directory of directories. It is a Laravel 13 application deployed via Laravel Forge with atomic deployments.

## Commands

```bash
# Start all dev services (server, queue, logs, vite) concurrently
composer dev

# Run tests (clears config, checks lint, then runs Pest)
composer test

# Run tests only
php artisan test

# Run a single test file
php artisan test tests/Feature/Api/IndexApiTest.php

# Run a single test by name
php artisan test --filter="returns a list"

# Lint (fix)
composer lint

# Lint (check only, no changes)
composer lint:check

# Check a single index link manually
php artisan indexes:check-links --id=1
```

## Architecture

### Public site

The public-facing site uses standard Laravel controllers + Blade views with no Livewire on the public pages. The layout is `resources/views/layouts/public.blade.php`.

- `/` — `HomeController` renders all active indexes grouped by `Category` enum, with optional `?category=` filtering
- `/visit/{slug}` — `VisitController` records a `Click` and redirects to the index URL (outbound clicks are never direct links)
- `/submit` — `SubmitController` accepts public submissions into the `submissions` table with `pending` status
- `/about` — static page

### Admin section

Auth is handled by Laravel Fortify. All admin routes are under the `auth` middleware at `/admin/*`. The admin uses the `x-layouts::app` layout (Flux/Livewire based).

- `/admin/stats` — `StatsController` — page view counts, top paths, top referrers, click counts per entry. Date calculations use `America/Chicago` timezone explicitly (DB stores UTC).
- `/admin/submissions` — shows only `pending` submissions; approve/reject via PATCH
- `/admin/indexes` — lists all entries; delete is available here

### Public API

The API lives at `/api/*` with 60 req/min rate limiting by IP (named limiter `api`, configured in `AppServiceProvider`).

- `GET /api/indexes` — paginated, filterable by `filter[category]` and `filter[language]` (JSON:API bracket syntax, use `http_build_query` in tests)
- `GET /api/indexes/{slug}` — single entry; 404s on inactive/dead entries
- `GET /api/openapi.yaml` — serves the OpenAPI spec from `docs/openapi.yaml`
- `GET /api/docs` — Scalar interactive docs UI

**Important:** `IndexCollection` overrides `toResponse()` directly to bypass `PaginatedResourceResponse`, which would otherwise double-wrap the `data` key. Do not remove this override.

**Important:** API filter parameters use JSON:API bracket notation (`filter[category]`). In tests, always use `http_build_query(['filter' => ['category' => 'value']])` rather than inline bracket syntax in URLs, as the test HTTP client does not reliably parse literal brackets.

### Data model

- `Index` — the core model. Key fields: `name`, `slug`, `url`, `description`, `category` (enum), `status` (enum: active/inactive/dead), `language` (ISO 639-1, nullable — null means English), `accepts_submissions`, `last_checked_at`
- `Submission` — public submissions awaiting review. Approved submissions must be manually added as `Index` records.
- `Click` — one row per outbound click via `/visit/{slug}`
- `PageView` — one row per non-bot GET request on non-admin pages, logged by `LogPageView` middleware. Self-referrals stripped. Admin pages excluded.

### Enums

All three enums are backed string enums:
- `Category` — 6 values, each with `label()`, `description()`, and several CSS class methods for the public UI
- `IndexStatus` — `active`, `inactive`, `dead`
- `SubmissionStatus` — `pending`, `approved`, `rejected`

`Index::LANGUAGES` is a constant array of ISO 639-1 code → name mappings used for the language select and display.

### Link checker

`php artisan indexes:check-links` fetches each index URL, checks for non-200 responses and parking page signals in the response body, and updates `status` and `last_checked_at`. Scheduled weekly via `routes/console.php`. A 200 response is not sufficient — the command scans the body for known domain parking signatures.

### Analytics

`LogPageView` middleware runs on all web responses (appended in `bootstrap/app.php`). It skips: non-200s, non-GETs, admin routes, known bot user agents. Referrers are cleaned to hostname only, self-referrals dropped.

### Deployment

Forge atomic deployments — each deploy creates a fresh release directory. The database is Postgres (managed by Forge). The deploy script runs `php artisan migrate --force`. The Laravel scheduler must be configured in Forge's Scheduler tab (`php artisan schedule:run` every minute).

### API docs

The OpenAPI spec is hand-maintained at `docs/openapi.yaml`. It is served publicly at `/api/openapi.yaml` and consumed by Scalar at `/api/docs`. When changing API response shapes, update both the implementation and the spec.
