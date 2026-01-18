<?php

namespace App\Integrations\CRM\HubSpot;

use App\Integrations\CRM\Contracts\CrmDriverInterface;
use App\Integrations\Contracts\IntegrationException;
use App\Integrations\Contracts\IntegrationNotConfiguredException;
use App\Services\TenantSettingService;

class HubSpotDriver implements CrmDriverInterface
{
    public function __construct(private TenantSettingService $tenantSettings)
    {
    }

    public function upsertLead(array $payload): void
    {
        $this->guardConfigured();

        throw new IntegrationException('HubSpot integration is not implemented yet.');
    }

    public function updateDeal(array $payload): void
    {
        $this->guardConfigured();

        throw new IntegrationException('HubSpot integration is not implemented yet.');
    }

    private function guardConfigured(): void
    {
        if (! $this->tenantSettings->integrationEnabled('hubspot')) {
            throw new IntegrationNotConfiguredException('HubSpot integration is disabled for this tenant.');
        }

        $credentials = $this->tenantSettings->integrationValue('hubspot', 'credentials', []);
        if (empty($credentials['access_token'])) {
            throw new IntegrationNotConfiguredException('HubSpot credentials are missing.');
        }
    }
}
