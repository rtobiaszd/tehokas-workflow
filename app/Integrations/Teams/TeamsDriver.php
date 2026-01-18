<?php

namespace App\Integrations\Teams;

use App\Integrations\Contracts\IntegrationDriverInterface;
use App\Integrations\Contracts\IntegrationException;
use App\Integrations\Contracts\IntegrationNotConfiguredException;
use App\Integrations\Observability\Contracts\LoggerInterface;
use App\Services\TenantSettingService;

class TeamsDriver implements IntegrationDriverInterface
{
    public function __construct(
        private TenantSettingService $tenantSettings,
        private LoggerInterface $logger
    ) {
    }

    public function send(array $payload): void
    {
        if (! $this->tenantSettings->integrationEnabled('teams')) {
            throw new IntegrationNotConfiguredException('Teams integration is disabled for this tenant.');
        }

        $credentials = $this->tenantSettings->integrationValue('teams', 'credentials', []);
        if (empty($credentials['webhook_url'])) {
            throw new IntegrationNotConfiguredException('Teams webhook URL is missing.');
        }

        if (! is_array($payload)) {
            throw new IntegrationException('Teams payload must be a JSON object.');
        }

        $this->logger->info('integration_teams_stub', [
            'payload' => $payload,
        ]);
    }
}
