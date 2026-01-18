<?php

namespace App\Repositories;

use App\Models\Workflow;
use App\Models\WorkflowLog;
use App\Services\TenantContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class WorkflowRepository
{
    public function __construct(private TenantContext $tenantContext)
    {
    }

    public function paginate(?string $search, ?string $status, int $perPage = 10): LengthAwarePaginator
    {
        $query = Workflow::query()->withCount(['actions', 'conditions', 'triggers']);

        if ($search) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive' || $status === 'draft') {
            $query->where('is_active', false);
        }

        return $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();
    }

    public function getMetrics(): array
    {
        $total = Workflow::count();
        $active = Workflow::where('is_active', true)->count();
        $failed = WorkflowLog::where('status', 'failed')
            ->where('created_at', '>=', now()->subDay())
            ->count();

        return [
            'total' => $total,
            'active' => $active,
            'failed' => $failed,
        ];
    }

    public function createFromDefinition(array $definition): Workflow
    {
        $tenantId = $this->tenantContext->tenantId();

        if (! $tenantId) {
            throw new RuntimeException('Tenant not resolved for workflow creation.');
        }

        $workflow = Workflow::create([
            'tenant_id' => $tenantId,
            'name' => $definition['name'],
            'description' => $definition['description'] ?? null,
            'is_active' => $definition['is_active'] ?? true,
            'definition' => $definition,
        ]);

        $trigger = $definition['trigger'] ?? [];
        $workflow->triggers()->create([
            'type' => $trigger['type'] ?? 'webhook',
            'config' => $trigger['config'] ?? [],
            'is_active' => $trigger['is_active'] ?? true,
        ]);

        foreach ($definition['conditions'] ?? [] as $index => $condition) {
            $payload = $condition['payload'] ?? null;
            $config = Arr::except($condition, ['payload', 'payload_text', 'payload_error']);
            $workflow->conditions()->create([
                'config' => $config,
                'payload' => $payload,
                'position' => $index + 1,
            ]);
        }

        foreach ($definition['actions'] as $index => $action) {
            $payload = $action['payload'] ?? null;
            $workflow->actions()->create([
                'type' => $action['type'],
                'config' => $action['config'] ?? [],
                'payload' => $payload,
                'position' => $index + 1,
                'is_active' => $action['is_active'] ?? true,
            ]);
        }

        $this->clearCacheForTenant($tenantId);

        return $workflow->load(['triggers', 'conditions', 'actions']);
    }

    public function updateFromDefinition(Workflow $workflow, array $definition): Workflow
    {
        $workflow->update([
            'name' => $definition['name'],
            'description' => $definition['description'] ?? null,
            'is_active' => $definition['is_active'] ?? true,
            'definition' => $definition,
        ]);

        $workflow->triggers()->delete();
        $workflow->conditions()->delete();
        $workflow->actions()->delete();

        $trigger = $definition['trigger'] ?? [];
        $workflow->triggers()->create([
            'type' => $trigger['type'] ?? 'webhook',
            'config' => $trigger['config'] ?? [],
            'is_active' => $trigger['is_active'] ?? true,
        ]);

        foreach ($definition['conditions'] ?? [] as $index => $condition) {
            $payload = $condition['payload'] ?? null;
            $config = Arr::except($condition, ['payload', 'payload_text', 'payload_error']);
            $workflow->conditions()->create([
                'config' => $config,
                'payload' => $payload,
                'position' => $index + 1,
            ]);
        }

        foreach ($definition['actions'] as $index => $action) {
            $payload = $action['payload'] ?? null;
            $workflow->actions()->create([
                'type' => $action['type'],
                'config' => $action['config'] ?? [],
                'payload' => $payload,
                'position' => $index + 1,
                'is_active' => $action['is_active'] ?? true,
            ]);
        }

        $this->clearCacheForTenant($workflow->tenant_id);

        return $workflow->load(['triggers', 'conditions', 'actions']);
    }

    public function toggle(Workflow $workflow): Workflow
    {
        $workflow->update([
            'is_active' => ! $workflow->is_active,
        ]);

        $this->clearCacheForTenant($workflow->tenant_id);

        return $workflow;
    }

    public function getActiveWorkflows(): Collection
    {
        $tenantId = $this->tenantContext->tenantId();

        if (! $tenantId) {
            return collect();
        }

        return Cache::remember(
            $this->activeCacheKey($tenantId),
            $this->cacheTtl(),
            fn () => Workflow::query()
                ->with(['triggers', 'conditions', 'actions'])
                ->where('is_active', true)
                ->get()
        );
    }

    public function clearCacheForTenant(?int $tenantId = null): void
    {
        $tenantId = $tenantId ?? $this->tenantContext->tenantId();

        if (! $tenantId) {
            return;
        }

        Cache::forget($this->activeCacheKey($tenantId));
    }

    private function activeCacheKey(int $tenantId): string
    {
        return "workflow:tenant:{$tenantId}:active";
    }

    private function cacheTtl(): int
    {
        return (int) config('workflow.cache_ttl', 300);
    }
}
