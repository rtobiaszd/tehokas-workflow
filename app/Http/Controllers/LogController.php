<?php

namespace App\Http\Controllers;

use App\Models\WorkflowLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class LogController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('view-logs');

        return Inertia::render('Logs/Index', [
            'logs' => $this->paginateLogs($request),
        ]);
    }

    public function feed(Request $request): JsonResponse
    {
        Gate::authorize('view-logs');

        $logs = $this->paginateLogs($request);

        return response()->json([
            'data' => $logs->items(),
            'next_page_url' => $logs->nextPageUrl(),
            'current_page' => $logs->currentPage(),
        ]);
    }

    private function paginateLogs(Request $request): LengthAwarePaginator
    {
        $perPage = $request->integer('per_page', 15);
        $perPage = min(50, max(5, $perPage));

        $paginator = WorkflowLog::query()
            ->select(['id', 'workflow_id', 'event', 'status', 'executed_at', 'created_at'])
            ->latest()
            ->paginate($perPage)
            ->through(fn (WorkflowLog $log) => $this->transformLog($log));

        return $paginator->withPath(route('logs.feed'));
    }

    private function transformLog(WorkflowLog $log): array
    {
        return [
            'id' => $log->id,
            'workflow_id' => $log->workflow_id,
            'event' => $log->event,
            'status' => $log->status,
            'executed_at' => $log->executed_at?->toIso8601String(),
            'created_at' => $log->created_at?->toIso8601String(),
        ];
    }
}
