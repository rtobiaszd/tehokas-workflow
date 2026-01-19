<?php

namespace App\Integrations\GoogleSheets;

use App\Integrations\Contracts\IntegrationDriverInterface;
use App\Integrations\Contracts\IntegrationException;
use App\Integrations\Contracts\IntegrationNotConfiguredException;
use App\Services\TenantSettingService;

class GoogleSheetsDriver implements IntegrationDriverInterface
{
    public function __construct(private TenantSettingService $tenantSettings)
    {
    }

    public function send(array $payload): void
    {
        $this->appendRow($payload);
    }

    public function appendRow(array $payload): void
    {
        if (! $this->tenantSettings->integrationEnabled('google_sheets')) {
            throw new IntegrationNotConfiguredException('Google Sheets integration is disabled for this tenant.');
        }

        $credentials = $this->tenantSettings->integrationValue('google_sheets', 'credentials', []);
        $hasCredentials = ! empty($credentials['service_account_json'])
            && ! empty($credentials['spreadsheet_id']);

        if (! $hasCredentials) {
            throw new IntegrationNotConfiguredException('Google Sheets credentials are missing.');
        }

        throw new IntegrationException('Google Sheets driver is not configured yet.');
    }
}
