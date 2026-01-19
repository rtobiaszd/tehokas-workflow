<?php

namespace App\Integrations\WhatsApp;

use App\Integrations\Contracts\IntegrationDriverInterface;
use App\Integrations\Contracts\IntegrationException;
use App\Integrations\Contracts\IntegrationNotConfiguredException;
use App\Integrations\Observability\Contracts\LoggerInterface;
use App\Services\TenantSettingService;

class WhatsAppDriver implements IntegrationDriverInterface
{
    public function __construct(
        private TenantSettingService $tenantSettings,
        private LoggerInterface $logger
    ) {
    }

    public function send(array $payload): void
    {
        if (! $this->tenantSettings->integrationEnabled('whatsapp')) {
            throw new IntegrationNotConfiguredException('WhatsApp integration is disabled for this tenant.');
        }

        $credentials = $this->tenantSettings->integrationValue('whatsapp', 'credentials', []);
        $hasCredentials = ! empty($credentials['provider'])
            && ! empty($credentials['api_token'])
            && ! empty($credentials['phone_number_id']);

        if (! $hasCredentials) {
            throw new IntegrationNotConfiguredException('WhatsApp credentials are missing.');
        }

        if (! is_array($payload)) {
            throw new IntegrationException('WhatsApp payload must be a JSON object.');
        }

        $this->logger->info('integration_whatsapp_stub', [
            'payload' => $payload,
        ]);
    }
}
