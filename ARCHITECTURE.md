# Architecture Overview

## Backend

- **Multi-tenancy** – Every domain model that carries tenant data uses the `App\Models\Traits\BelongsToTenant` global scope. Incoming requests pass through `SetTenantContext`, which resolves the tenant from the authenticated user, impersonation session or webhook headers. The resolved tenant is stored in `App\Services\TenantContext` and reused across repositories and services.
- **Workflow engine** – `WorkflowEngineService` orchestrates trigger/condition evaluation and dispatches actions through `ExecuteWorkflowAction`. Active workflows are cached per tenant by `WorkflowRepository` (key `workflow:tenant:{id}:active`) to avoid redundant queries. Business rules such as rate limiting and retry policies live in `TenantSettingService`, which reads encrypted settings merged from defaults plus overrides (`tenant_settings` table).
- **Asynchronous processing** – `WebhookController` validates payloads, resolves the tenant, and dispatches `ProcessWorkflowJob` onto the dedicated `workflows` queue. The job reinstates the tenant context before calling the engine, ensuring isolation even outside the HTTP lifecycle. Queue workers (Docker service and `composer dev` script) run `queue:work --tries=3 --backoff=30 --sleep=1` so failures are retried with exponential backoff as requested.
- **Integrations & actions** – `IntegrationManager` maps workflow actions to drivers (email, Slack, Teams, WhatsApp, Google Sheets, HTTP). `TenantSettingService` exposes feature flags plus credential state to block disabled or incomplete integrations before dispatching.
- **Observability** – A lightweight logger abstraction `LoggerInterface` sits in front of Laravel's logging stack. The concrete driver is selected via `config/observability.php` (env `OBSERVABILITY_DRIVER=log|sentry|cloudwatch`). `LogLogger` respects tenant-level observability toggles, while Sentry/CloudWatch stubs show how to forward enriched structured events.
- **Security** – AuthZ is enforced through Gates defined in `AuthServiceProvider`. Webhook access is guarded by `ValidateWebhookToken` comparing the configured per-tenant token with the `X-WEBHOOK-TOKEN` header (constant-time compare). All persistence goes through Eloquent, preventing SQL injection, and Inertia pages escape/encode dynamic data to mitigate XSS.

## Frontend (Inertia + Vue 3)

- **Layout & navigation** – `resources/js/Layouts/AppLayout.vue` centralizes shell UI, flash messages, and role-driven navigation shared across pages.
- **Workflow builder** – The Workflows module uses composables (`useWorkflows`) and builder components (`TriggerSelect`, `ConditionBuilder`, `ActionBuilder`) to add an arbitrary number of triggers, conditions, and actions with integration-aware validation states.
- **Performance UX** – Skeleton and lazy loading states are now in place: Workflows index shows skeleton rows on filter changes, Settings toggles skeletons through hydration flags, and Logs index streams more records via `IntersectionObserver` + `/logs/feed` API, showing skeleton placeholders while fetching.
- **State management** – Vue's composition API plus Inertia props drive forms and tables. Mutations stay co-located with server actions to simplify reasoning about multi-tenant data.

## Persistence & Settings

- Tenant-specific settings are normalized into dot-notated keys inside `tenant_settings`. `TenantSettingRepository` flattens nested arrays, while `TenantSettingService` caches fully merged trees for 10 minutes. Settings are used for integration credentials, rate limiting, retry/backoff toggles, and observability flags.
- Workflow definitions are versioned JSON blobs persisted with relationships to triggers, conditions and actions so editing regenerates the graph deterministically.

## Tests

- `tests/Feature/WorkflowEngineTest.php` covers the critical business rules: happy-path execution, retry policy toggles, and rate limiting.
- `tests/Unit/ProcessWorkflowJobTest.php` ensures the queue job hydrates tenant context before calling the engine.
- `tests/Unit/TenantSettingRepositoryTest.php` validates defaults + overrides and key/value persistence.
- Existing webhook feature tests keep the ingestion API contract locked. Run everything via `php artisan test`.

## Tooling & Delivery

- **Docker** – `docker-compose.yml` boots the Laravel app, queue worker, Redis, MySQL, and a dedicated Vite container. Queue workers now respect tries/backoff requirements without blocking HTTP requests.
- **CI/CD** – `.github/workflows/ci.yml` installs PHP/Node deps, runs Vite build, enforces Pint formatting (`vendor/bin/pint --test`), and executes the PHPUnit suite on every push/PR to `master`.
- **Documentation** – `README.md` outlines the stack plus operational steps, while this file explains the reasoning behind each design choice.
