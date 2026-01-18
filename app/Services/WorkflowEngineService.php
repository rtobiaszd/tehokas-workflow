<?php

namespace App\Services;

use App\Actions\ExecuteWorkflowAction;
use App\Integrations\Observability\Contracts\LoggerInterface;
use App\Models\Workflow;
use App\Models\WorkflowLog;
use App\Repositories\WorkflowRepository;
use App\Services\TenantSettingService;
use Illuminate\Support\Facades\Cache;

class WorkflowEngineService
{
    public function __construct(
        private WorkflowRepository $workflowRepository,
        private ExecuteWorkflowAction $executeWorkflowAction,
        private TenantContext $tenantContext,
        private LoggerInterface $logger,
        private TenantSettingService $tenantSettingService
    ) {
    }

    public function processEvent(array $payload): void
    {
        $tenantId = $this->tenantContext->tenantId();

        if (! $tenantId) {
            $this->logger->error('workflow_event_rejected', [
                'payload' => $payload,
            ]);

            return;
        }

        if (! $this->passesRateLimit($tenantId)) {
            $this->logger->info('workflow_rate_limited', [
                'tenant_id' => $tenantId,
            ]);

            return;
        }

        $event = data_get($payload, 'event');
        $workflows = $this->workflowRepository->getActiveWorkflows();

        foreach ($workflows as $workflow) {
            if (! $this->workflowMatchesEvent($workflow, $event)) {
                continue;
            }

            if (! $this->conditionsPass($workflow, $payload)) {
                continue;
            }

            $log = WorkflowLog::create([
                'tenant_id' => $tenantId,
                'workflow_id' => $workflow->id,
                'event' => (string) $event,
                'status' => 'running',
                'payload' => $payload,
            ]);

            try {
                $this->executeWorkflow($workflow, $payload);

                $log->update([
                    'status' => 'success',
                    'executed_at' => now(),
                ]);

                $this->logger->info('workflow_executed', [
                    'workflow_id' => $workflow->id,
                    'tenant_id' => $tenantId,
                    'event' => $event,
                ]);
            } catch (\Throwable $exception) {
                $this->logger->error('workflow_failed', [
                    'workflow_id' => $workflow->id,
                    'tenant_id' => $tenantId,
                    'error' => $exception->getMessage(),
                ]);

                $log->update([
                    'status' => 'failed',
                    'executed_at' => now(),
                    'context' => [
                        'error' => $exception->getMessage(),
                    ],
                ]);

                if ($this->tenantSettingService->get('policies.workflow.retry_on_fail', true)) {
                    throw $exception;
                }
            }
        }
    }

    private function workflowMatchesEvent(Workflow $workflow, ?string $event): bool
    {
        foreach ($workflow->triggers as $trigger) {
            if (! $trigger->is_active) {
                continue;
            }

            if ($trigger->type === 'webhook') {
                $configuredEvent = $trigger->config['event'] ?? null;

                if (! $configuredEvent || $configuredEvent === $event) {
                    return true;
                }
            }
        }

        return false;
    }

    private function conditionsPass(Workflow $workflow, array $eventPayload): bool
    {
        if ($workflow->conditions->isEmpty()) {
            return true;
        }

        foreach ($workflow->conditions as $condition) {
            $payload = $condition->payload ?? $condition->config ?? [];

            if (! is_array($payload)) {
                continue;
            }

            $field = $payload['field'] ?? null;
            $operator = $payload['operator'] ?? 'equals';
            $expected = $payload['value'] ?? null;

            if (! $field) {
                continue;
            }

            $actual = data_get($eventPayload, $field);

            if (! $this->evaluateCondition($operator, $actual, $expected)) {
                return false;
            }
        }

        return true;
    }

    private function evaluateCondition(string $operator, mixed $actual, mixed $expected): bool
    {
        return match ($operator) {
            'equals' => $actual == $expected,
            'not_equals' => $actual != $expected,
            'contains' => is_array($actual)
                ? in_array($expected, $actual, true)
                : str_contains((string) $actual, (string) $expected),
            'gt' => $actual > $expected,
            'lt' => $actual < $expected,
            'in' => is_array($expected) ? in_array($actual, $expected, true) : false,
            'not_in' => is_array($expected) ? ! in_array($actual, $expected, true) : true,
            default => false,
        };
    }

    private function executeWorkflow(Workflow $workflow, array $payload): void
    {
        foreach ($workflow->actions as $action) {
            if (! $action->is_active) {
                continue;
            }

            $this->executeWorkflowAction->execute($action, $payload);
        }
    }

    private function passesRateLimit(int $tenantId): bool
    {
        $limit = (int) $this->tenantSettingService->get('policies.workflow.rate_limit', 0);

        if ($limit <= 0) {
            return true;
        }

        $bucket = now()->format('YmdHi');
        $key = "workflow:rate:{$tenantId}:{$bucket}";
        $count = Cache::increment($key);

        if ($count === 1) {
            Cache::put($key, 1, now()->addMinute());
        }

        return $count <= $limit;
    }
}
