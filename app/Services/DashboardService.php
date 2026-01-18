<?php

namespace App\Services;

use App\Models\Workflow;
use App\Models\WorkflowLog;
use Illuminate\Support\Collection;

class DashboardService
{
    public function getDashboardData(): array
    {
        return [
            'total_workflows' => $this->totalWorkflows(),
            'active_workflows' => $this->activeWorkflows(),
            'executions_last_24h' => $this->executionsLast24h(),
            'failures_last_24h' => $this->failuresLast24h(),
            'recent_executions' => $this->recentExecutions(),
            'recent_failures' => $this->recentFailures(),
        ];
    }

    private function totalWorkflows(): int
    {
        return Workflow::count();
    }

    private function activeWorkflows(): int
    {
        return Workflow::where('is_active', true)->count();
    }

    private function recentExecutions(): Collection
    {
        return WorkflowLog::query()
            ->with('workflow:id,name')
            ->latest()
            ->take(8)
            ->get(['id', 'workflow_id', 'status', 'created_at', 'executed_at'])
            ->map(fn ($log) => [
                'id' => $log->id,
                'workflow' => $log->workflow?->name ?? 'Workflow',
                'status' => $log->status,
                'executed_at' => $log->executed_at?->toIso8601String() ?? $log->created_at?->toIso8601String(),
            ]);
    }

    private function recentFailures(): Collection
    {
        return WorkflowLog::query()
            ->with('workflow:id,name')
            ->where('status', 'failed')
            ->latest()
            ->take(6)
            ->get(['id', 'workflow_id', 'status', 'created_at', 'executed_at', 'context'])
            ->map(fn ($log) => [
                'id' => $log->id,
                'workflow' => $log->workflow?->name ?? 'Workflow',
                'executed_at' => $log->executed_at?->toIso8601String() ?? $log->created_at?->toIso8601String(),
                'error' => data_get($log->context, 'error'),
            ]);
    }

    private function executionsLast24h(): int
    {
        return WorkflowLog::where('created_at', '>=', now()->subDay())->count();
    }

    private function failuresLast24h(): int
    {
        return WorkflowLog::where('status', 'failed')
            ->where('created_at', '>=', now()->subDay())
            ->count();
    }
}
