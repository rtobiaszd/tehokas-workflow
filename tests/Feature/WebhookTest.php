<?php

namespace Tests\Feature;

use App\Jobs\ProcessWorkflowJob;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_rejects_invalid_token(): void
    {
        config(['workflow.webhook_token' => 'secret-token']);

        $response = $this->postJson('/api/webhooks/workflows', [
            'event' => 'project.status_changed',
            'data' => ['project_id' => 123],
        ]);

        $response->assertStatus(401);
    }

    public function test_webhook_queues_job_for_valid_request(): void
    {
        Queue::fake();
        config(['workflow.webhook_token' => 'secret-token']);

        $tenant = Tenant::factory()->create();

        $response = $this->withHeaders([
            'X-WEBHOOK-TOKEN' => 'secret-token',
            'X-Tenant-ID' => $tenant->id,
        ])->postJson('/api/webhooks/workflows', [
            'event' => 'project.status_changed',
            'data' => [
                'project_id' => 123,
                'project_name' => 'Website Redesign',
            ],
        ]);

        $response->assertStatus(200)->assertJson(['status' => 'queued']);

        Queue::assertPushed(ProcessWorkflowJob::class, function (ProcessWorkflowJob $job) use ($tenant) {
            return $job->tenantId === $tenant->id
                && $job->payload['event'] === 'project.status_changed';
        });
    }
}
