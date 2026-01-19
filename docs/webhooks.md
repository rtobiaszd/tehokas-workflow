# Webhook de Workflows

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
