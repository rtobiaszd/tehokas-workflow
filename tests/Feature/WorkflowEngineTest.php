<?php

namespace Tests\Feature;

use App\Actions\ExecuteWorkflowAction;
use App\Integrations\Contracts\IntegrationException;
use App\Models\Tenant;
use App\Models\Workflow;
use App\Models\WorkflowLog;
use App\Services\TenantContext;
use App\Services\TenantSettingService;
use App\Services\WorkflowEngineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class WorkflowEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_it_executes_matching_workflow_and_logs_success(): void
    {
        $tenant = Tenant::factory()->create();
        $workflow = $this->bootstrapWorkflow($tenant);

        $this->mock(ExecuteWorkflowAction::class)
            ->shouldReceive('execute')
            ->once();

        $payload = [
            'event' => 'project.status_changed',
            'data' => [
                'new_status' => 'Delayed',
                'project_id' => 42,
            ],
        ];

        app(WorkflowEngineService::class)->processEvent($payload);

        $this->assertDatabaseHas('workflow_logs', [
            'workflow_id' => $workflow->id,
            'status' => 'success',
        ]);
    }

    public function test_it_honors_retry_policy_toggle(): void
    {
        $tenant = Tenant::factory()->create();
        $workflow = $this->bootstrapWorkflow($tenant);

        $this->mock(ExecuteWorkflowAction::class)
            ->shouldReceive('execute')
            ->once()
            ->andThrow(new IntegrationException('integration failed'));

        $settingService = app(TenantSettingService::class);
        $settings = $settingService->forTenant();
        data_set($settings, 'policies.workflow.retry_on_fail', false);
        $settingService->update($settings);

        $payload = [
            'event' => 'project.status_changed',
            'data' => [
                'new_status' => 'Delayed',
            ],
        ];

        app(WorkflowEngineService::class)->processEvent($payload);

        $this->assertDatabaseHas('workflow_logs', [
            'workflow_id' => $workflow->id,
            'status' => 'failed',
        ]);
    }

    public function test_it_applies_rate_limits_per_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        $this->bootstrapWorkflow($tenant);

        $settingService = app(TenantSettingService::class);
        $settings = $settingService->forTenant();
        data_set($settings, 'policies.workflow.rate_limit', 1);
        $settingService->update($settings);

        $this->mock(ExecuteWorkflowAction::class)
            ->shouldReceive('execute')
            ->once();

        $payload = [
            'event' => 'project.status_changed',
            'data' => [
                'new_status' => 'Delayed',
            ],
        ];

        $service = app(WorkflowEngineService::class);
        $service->processEvent($payload);
        $service->processEvent($payload);

        $this->assertEquals(1, WorkflowLog::count());
    }

    private function bootstrapWorkflow(Tenant $tenant): Workflow
    {
        $tenantContext = app(TenantContext::class);
        $tenantContext->setTenant($tenant);

        $workflow = Workflow::create([
            'name' => 'Project Delay Alert',
            'description' => 'Dispara alertas para gestores',
            'is_active' => true,
            'definition' => [],
        ]);

        $workflow->triggers()->create([
            'type' => 'webhook',
            'config' => ['event' => 'project.status_changed'],
            'is_active' => true,
        ]);

        $workflow->conditions()->create([
            'config' => ['field' => 'data.new_status', 'operator' => 'equals', 'value' => 'Delayed'],
            'payload' => null,
            'position' => 1,
        ]);

        $workflow->actions()->create([
            'type' => 'send_email',
            'config' => ['to' => 'ops@acme.test'],
            'payload' => null,
            'position' => 1,
            'is_active' => true,
        ]);

        return $workflow;
    }
}
