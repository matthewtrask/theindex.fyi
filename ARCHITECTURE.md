# Architecture

theindex.fyi is a manually curated meta-index of indie web and small web index sites. It is a Laravel 13 monolith with three distinct surfaces: a public-facing website, an authenticated admin panel, and a read-only public API.

## Tech stack

| Layer | Choice |
|---|---|
| Framework | Laravel 13 (PHP 8.3) |
| Frontend | Blade + Tailwind CSS v4 (Vite) |
| Admin UI | Livewire + Flux (Livewire starter kit) |
| Auth | Laravel Fortify (email/password + 2FA) |
| Database | Postgres (production), SQLite in-memory (tests) |
| API docs | Scalar (`/api/docs`) reading `docs/openapi.yaml` |
| Deployment | Laravel Forge, atomic deployments |
| Scheduling | Laravel scheduler via Forge cron |

## Request surfaces

```
┌─────────────────────────────────────┐
│           theindex.fyi              │
├──────────┬──────────────┬───────────┤
│  Public  │    Admin     │    API    │
│  /       │  /admin/*    │  /api/*   │
│  /about  │  (auth)      │  (public) │
│  /submit │              │           │
│  /visit/ │              │           │
└──────────┴──────────────┴───────────┘
```

### Public site

Standard Laravel controllers returning Blade views. No Livewire on public pages. Layout: `resources/views/layouts/public.blade.php`.

Outbound links are never direct — all clicks go through `/visit/{slug}`, which records a `Click` row and redirects. This is how click counts are tracked.

Category filtering on the home page is a query string parameter (`?category=curated_directories`). The `HomeController` maps over all `Category` enum cases and queries each, so the grouped structure always follows the canonical enum order.

### Admin panel

Uses the `x-layouts::app` layout (Flux/Livewire). All routes require `auth` middleware. The submission workflow is intentionally simple: submissions arrive as `pending`, the admin approves or rejects them, and approved submissions must be manually created as `Index` entries — there is no automatic promotion.

The stats page computes date-windowed counts (today, last 7 days) using `America/Chicago` timezone explicitly, while all timestamps are stored in UTC.

### Public API

`routes/api.php` — Laravel's `api` route file, so all routes are automatically prefixed with `/api`. Rate limited at 60 requests/minute per IP via a named limiter `api` registered in `AppServiceProvider`.

Conforms to JSON:API 1.1 (`application/vnd.api+json`). Versioning is intentionally absent; if a breaking change is ever required, it will use an `API-Version` request header rather than a URL prefix.

The API only exposes `active` indexes. `inactive` and `dead` entries are hidden from all API responses.

## Database schema

```
indexes
  id, name, slug (unique), url, description
  category        -- string, cast to Category enum
  status          -- string, cast to IndexStatus enum (active/inactive/dead)
  language        -- ISO 639-1 code, nullable (null = English/unknown)
  accepts_submissions -- boolean
  last_checked_at -- timestamp, nullable
  submitted_by    -- nullable string
  timestamps

submissions
  id, name, url, description
  category        -- nullable string
  submitted_by_email -- nullable
  status          -- string (pending/approved/rejected)
  timestamps

clicks
  id
  index_id        -- FK → indexes (cascade delete)
  clicked_at      -- timestamp

page_views
  id
  path            -- e.g. "/" or "/about"
  referrer        -- cleaned to hostname only, nullable
  visited_at      -- timestamp
```

`clicks` and `page_views` are append-only event logs — they are never updated, only inserted and aggregated.

## Enums

All enums are PHP backed string enums stored as their raw value in the database. Eloquent casts handle the conversion.

`Category` carries UI metadata directly on the enum: `label()`, `description()`, and multiple CSS class methods (`labelClass()`, `linkHoverClass()`, `badgeClass()`, etc.). This keeps category-specific presentation logic colocated rather than scattered across views.

`Index::LANGUAGES` is a plain constant array (`code => name`) rather than an enum because the set of languages is stable reference data that doesn't need behaviour attached to it.

## Analytics pipeline

```
Request → LogPageView middleware → page_views table
                                        ↓
                               StatsController aggregates
                                        ↓
                               /admin/stats view
```

`LogPageView` runs post-response (it calls `$next($request)` first) so it never adds latency to the user-facing response. It skips: non-200 responses, non-GET requests, `/admin/*` routes, and known bot user agents.

Referrer headers are cleaned to hostname only and self-referrals from `theindex.fyi` are dropped before storage.

## Link health checking

```
indexes:check-links (artisan command)
  → HTTP GET each URL (15s timeout, browser-like UA)
  → 200 + no parking signals → Active
  → non-200                  → Inactive
  → connection failure        → Dead
  → updates status + last_checked_at
```

Scheduled weekly. A 200 response is not sufficient — many expired domains return 200 from GoDaddy/Sedo parking pages. The command scans the response body for a list of known parking page signals before marking a link active.

## API response shaping

```
IndexController → IndexCollection (ResourceCollection)
                      ↓
              toResponse() override
                      ↓
              JSON:API envelope:
              { data: [...], meta: {...}, links: {...} }
```

`IndexCollection` overrides `toResponse()` directly rather than relying on Laravel's `PaginatedResourceResponse`. This is required because `PaginatedResourceResponse` wraps the `toArray()` output in an additional `data` key, producing a `data.data` double-wrap. The override bypasses that entirely and returns the correctly shaped JSON:API document.

Individual resources (`IndexResource`) use `$wrap = 'data'` for single-resource responses and have a `withResponse()` hook to set the `application/vnd.api+json` content type header.

## Deployment

Forge atomic deployments create a new release directory per deploy. The deploy script:

1. `composer install --no-dev`
2. `php artisan optimize`
3. `php artisan storage:link`
4. `php artisan migrate --force`
5. `npm ci && npm run build`

The database is Postgres managed by Forge — credentials are injected into the server's `.env` directly by Forge, not stored in the repository. The local `.env` uses SQLite for development.

The Laravel scheduler runs via a Forge-managed cron (`* * * * * php artisan schedule:run`). Currently scheduled: `indexes:check-links` weekly.

## API documentation

`docs/openapi.yaml` is the hand-maintained OpenAPI 3.1.0 spec. It is served publicly at `/api/openapi.yaml` (via a route in `web.php`) and consumed by Scalar at `/api/docs`. The Scalar config lives at `config/scalar.php`. When changing API response shapes, the spec must be updated manually to stay in sync.
