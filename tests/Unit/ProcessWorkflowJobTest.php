<?php

namespace Tests\Unit;

use App\Jobs\ProcessWorkflowJob;
use App\Models\Tenant;
use App\Services\TenantContext;
use App\Services\WorkflowEngineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ProcessWorkflowJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sets_tenant_context_before_processing(): void
    {
        $tenant = Tenant::factory()->create();
        $payload = [
            'event' => 'project.status_changed',
            'data' => ['project_id' => 99],
        ];

        $job = new ProcessWorkflowJob($tenant->id, $payload);

        $tenantContext = app(TenantContext::class);
        $tenantContext->setTenantId(null);

        $workflowService = Mockery::mock(WorkflowEngineService::class);
        $workflowService->shouldReceive('processEvent')
            ->once()
            ->with($payload);

        $job->handle($workflowService, $tenantContext);

        $this->assertSame($tenant->id, $tenantContext->tenantId());
    }
}
