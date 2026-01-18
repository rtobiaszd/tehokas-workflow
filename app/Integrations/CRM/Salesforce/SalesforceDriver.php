<?php

namespace App\Integrations\CRM\Salesforce;

use App\Integrations\CRM\Contracts\CrmDriverInterface;
use App\Integrations\Contracts\IntegrationException;
use App\Integrations\Contracts\IntegrationNotConfiguredException;
use App\Services\TenantSettingService;

class SalesforceDriver implements CrmDriverInterface
{
    public function __construct(private TenantSettingService $tenantSettings)
    {
    }

    public function upsertLead(array $payload): void
    {
        $this->guardConfigured();

        throw new IntegrationException('Salesforce integration is not implemented yet.');
    }

    public function updateDeal(array $payload): void
    {
        $this->guardConfigured();

        throw new IntegrationException('Salesforce integration is not implemented yet.');
    }

    private function guardConfigured(): void
    {
        if (! $this->tenantSettings->integrationEnabled('salesforce')) {
            throw new IntegrationNotConfiguredException('Salesforce integration is disabled for this tenant.');
        }

        $credentials = $this->tenantSettings->integrationValue('salesforce', 'credentials', []);
        $hasCredentials = ! empty($credentials['client_id'])
            && ! empty($credentials['client_secret'])
            && ! empty($credentials['refresh_token'])
            && ! empty($credentials['instance_url']);

        if (! $hasCredentials) {
            throw new IntegrationNotConfiguredException('Salesforce credentials are missing.');
        }
    }
}
