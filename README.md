# Tehokas Workflow Engine

Workflow Automation Engine em Laravel + Inertia + Vue 3, focado em multi-tenancy, processamento assincrono, webhooks e arquitetura limpa.

## Stack

- Laravel 12
- Inertia.js + Vue 3
- Redis para filas e cache
- Tailwind CSS 4
- Swagger (L5-Swagger) para documentacao de API
- PHPUnit para testes

## Principais recursos

- Multi-tenancy com `tenant_id` e escopo global
- Webhook seguro com `X-WEBHOOK-TOKEN`
- Execucao assincrona via jobs e fila `workflows`
- Cache de workflows ativos por tenant
- Logs estruturados de execucao
- UI Inertia para dashboard e gerenciamento de workflows

## Setup rapido

1. Instale dependencias PHP e JS:
   - `composer install`
   - `npm install`
2. Configure o `.env`:
   - `QUEUE_CONNECTION=redis`
   - `WORKFLOW_WEBHOOK_TOKEN=change-me`
3. Rode migrations:
   - `php artisan migrate`
4. Suba os servicos:
   - `php artisan queue:work --queue=workflows`
   - `npm run dev`
   - `php artisan serve`

## Docker e Docker Compose

1. Suba os containers:
   - `docker compose up --build`
2. Rode as migrations (em outro terminal):
   - `docker compose exec app php artisan migrate`
3. Gere a documentacao Swagger:
   - `docker compose exec app php artisan l5-swagger:generate`
4. Acesse a aplicacao:
   - `http://localhost:8000`
   - Vite: `http://localhost:5173`

Os containers incluem `app`, `queue`, `mysql`, `redis` e `vite`.

## Swagger (OpenAPI)

1. Gere a documentacao:
   - `php artisan l5-swagger:generate`
2. Abra no navegador:
   - `GET /api/documentation`

As anotacoes estao no controller de webhooks e no arquivo `app/Swagger/OpenApi.php`.

## Webhook

Endpoint:

- `POST /api/webhooks/workflows`
- Header obrigatorio: `X-WEBHOOK-TOKEN`
- Header opcional: `X-Tenant-ID` ou `X-Tenant-Slug`

Exemplo de payload:

```json
{
  "event": "project.status_changed",
  "data": {
    "project_id": 123,
    "project_name": "Website Redesign",
    "old_status": "In Progress",
    "new_status": "Delayed",
    "manager_email": "manager@company.com"
  }
}
```

## Testes (PHPUnit)

Execute:

- `php artisan test`

Os testes de webhook ficam em `tests/Feature/WebhookTest.php`.

## Documentacao tecnica

Consulte o arquivo `ARCHITECTURE.md` para entender as decisoes de arquitetura (multi-tenancy, fila, cache, observabilidade e UI) e o pipeline de CI/CD.
