<?php

namespace App\Actions;

use App\Integrations\Contracts\IntegrationException;
use App\Integrations\Contracts\IntegrationNotConfiguredException;
use App\Integrations\IntegrationManager;
use App\Integrations\Observability\Contracts\LoggerInterface;
use App\Models\WorkflowAction;
use App\Services\TenantSettingService;
use Illuminate\Support\Arr;

class ExecuteWorkflowAction
{
    public function __construct(
        private IntegrationManager $integrationManager,
        private LoggerInterface $logger,
        private TenantSettingService $tenantSettingService
    ) {
    }

    public function execute(WorkflowAction $action, array $payload): void
    {
        $context = [
            'workflow_id' => $action->workflow_id,
            'action_id' => $action->id,
            'action_type' => $action->type,
            'action_payload' => $action->payload,
        ];

        $driver = $this->integrationManager->forAction($action->type);

        if (! $driver) {
            $this->logger->error('integration_driver_missing', $context);
            return;
        }

        $integrationKey = $this->integrationKeyForAction($action->type);
        if ($integrationKey) {
            $state = $this->tenantSettingService->integrationsStatus()[$integrationKey] ?? null;
            if ($state && ! $state['enabled']) {
                $this->logger->info('integration_disabled', [
                    ...$context,
                    'integration' => $integrationKey,
                ]);
                return;
            }

            if ($state && $state['enabled'] && ! $state['configured']) {
                $this->logger->error('integration_not_configured', [
                    ...$context,
                    'integration' => $integrationKey,
                ]);
                return;
            }
        }

        $this->logger->info('action_dispatched', [
            ...$context,
            'workflow_payload' => $payload,
        ]);

        $actionPayload = $this->renderActionPayload($action->payload ?? $action->config ?? [], $payload);

        try {
            $driver->send($actionPayload);
        } catch (IntegrationNotConfiguredException $exception) {
            $this->logger->info('integration_skipped', [
                ...$context,
                'reason' => $exception->getMessage(),
            ]);
        } catch (IntegrationException $exception) {
            $this->logger->error('integration_failed', [
                ...$context,
                'error' => $exception->getMessage(),
            ]);

            if ($this->tenantSettingService->get('policies.workflow.retry_on_fail', true)) {
                throw $exception;
            }
        }
    }

    private function integrationKeyForAction(string $type): ?string
    {
        return match ($type) {
            'send_email' => 'email',
            'http_request', 'webhook' => 'webhook',
            'slack_message' => 'slack',
            'teams_message' => 'teams',
            'whatsapp_message' => 'whatsapp',
            'google_sheets_append' => 'google_sheets',
            default => null,
        };
    }

    private function renderActionPayload($value, array $context)
    {
        if (is_string($value)) {
            return $this->renderTemplate($value, $context);
        }

        if (is_array($value)) {
            return collect($value)
                ->map(fn ($item) => $this->renderActionPayload($item, $context))
                ->all();
        }

        return $value;
    }

    private function renderTemplate(string $value, array $context): string
    {
        return preg_replace_callback('/{{\s*([^}]+)\s*}}/', function ($matches) use ($context) {
            $key = trim($matches[1]);
            $replacement = Arr::get($context, $key);

            return $replacement !== null ? (string) $replacement : $matches[0];
        }, $value);
    }
}
