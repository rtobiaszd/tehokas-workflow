<?php

namespace Tests\Unit;

use App\Models\Tenant;
use App\Repositories\TenantSettingRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantSettingRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_defaults_for_new_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        $repository = app(TenantSettingRepository::class);

        $settings = $repository->getForTenant($tenant);

        $this->assertTrue(data_get($settings, 'integrations.email.enabled'));
        $this->assertArrayHasKey('policies', $settings);
    }

    public function test_it_merges_overrides_with_defaults(): void
    {
        $tenant = Tenant::factory()->create();
        $repository = app(TenantSettingRepository::class);

        $overrides = [
            'integrations' => [
                'webhook' => [
                    'token' => 'secret-token',
                ],
            ],
            'policies' => [
                'workflow' => [
                    'rate_limit' => 5,
                ],
            ],
        ];

        $settings = $repository->updateForTenant($tenant, $overrides);

        $this->assertSame('secret-token', data_get($settings, 'integrations.webhook.token'));
        $this->assertSame(5, data_get($settings, 'policies.workflow.rate_limit'));

        $this->assertDatabaseHas('tenant_settings', [
            'tenant_id' => $tenant->id,
            'key' => 'integrations.webhook.token',
        ]);
    }
}
