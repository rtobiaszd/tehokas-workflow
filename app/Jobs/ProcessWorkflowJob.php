<?php

namespace App\Jobs;

use App\Services\TenantContext;
use App\Services\WorkflowEngineService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessWorkflowJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public int $tenantId,
        public array $payload
    ) {
    }

    public function handle(WorkflowEngineService $workflowEngineService, TenantContext $tenantContext): void
    {
        $tenantContext->setTenantId($this->tenantId);
        $workflowEngineService->processEvent($this->payload);
    }
}
