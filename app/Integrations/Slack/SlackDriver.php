<?php

namespace App\Integrations\Slack;

use App\Integrations\Contracts\IntegrationDriverInterface;
use App\Integrations\Contracts\IntegrationException;
use App\Integrations\Contracts\IntegrationNotConfiguredException;
use App\Integrations\Observability\Contracts\LoggerInterface;
use App\Services\TenantSettingService;

class SlackDriver implements IntegrationDriverInterface
{
    public function __construct(
        private TenantSettingService $tenantSettings,
        private LoggerInterface $logger
    ) {
    }

    public function send(array $payload): void
    {
        if (! $this->tenantSettings->integrationEnabled('slack')) {
            throw new IntegrationNotConfiguredException('Slack integration is disabled for this tenant.');
        }

        $credentials = $this->tenantSettings->integrationValue('slack', 'credentials', []);
        $hasCredentials = ! empty($credentials['bot_token']) || ! empty($credentials['webhook_url']);

        if (! $hasCredentials) {
            throw new IntegrationNotConfiguredException('Slack credentials are missing.');
        }

        if (! is_array($payload)) {
            throw new IntegrationException('Slack payload must be a JSON object.');
        }

        $this->logger->info('integration_slack_stub', [
            'payload' => $payload,
        ]);
    }
}
