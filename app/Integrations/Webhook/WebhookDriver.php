<?php

namespace App\Integrations\Webhook;

use App\Integrations\Contracts\IntegrationDriverInterface;
use App\Integrations\Contracts\IntegrationException;
use App\Integrations\Contracts\IntegrationNotConfiguredException;
use App\Integrations\Observability\Contracts\LoggerInterface;
use App\Services\TenantSettingService;
use Illuminate\Support\Facades\Http;

class WebhookDriver implements IntegrationDriverInterface
{
    public function __construct(
        private TenantSettingService $tenantSettings,
        private LoggerInterface $logger
    ) {
    }

    public function send(array $payload): void
    {
        if (! $this->tenantSettings->integrationEnabled('webhook', true)) {
            throw new IntegrationNotConfiguredException('Outgoing webhook integration is disabled for this tenant.');
        }

        $token = (string) $this->tenantSettings->integrationValue('webhook', 'token', '');
        if (! $token) {
            throw new IntegrationNotConfiguredException('Webhook token is missing.');
        }

        $url = $payload['url'] ?? null;

        if (! $url) {
            throw new IntegrationException('Webhook payload must include a url.');
        }

        $method = strtoupper($payload['method'] ?? 'POST');
        $defaultHeaders = $this->tenantSettings->integrationValue('webhook', 'credentials.headers', []);
        $headers = array_merge($defaultHeaders ?? [], $payload['headers'] ?? []);
        $body = $payload['body'] ?? [];
        $defaultRetries = (int) $this->tenantSettings->integrationValue('webhook', 'credentials.retries', 0);
        $retries = (int) ($payload['retries'] ?? $defaultRetries);
        $timeout = (int) ($payload['timeout'] ?? $this->tenantSettings->integrationValue('webhook', 'credentials.timeout', 10));

        if (! is_array($headers)) {
            throw new IntegrationException('Webhook headers must be an object.');
        }

        $request = Http::withHeaders($headers)->timeout($timeout);

        if ($retries > 0) {
            $request = $request->retry($retries, 200);
        }

        $options = [];
        $isQueryMethod = in_array($method, ['GET', 'DELETE'], true);

        if (is_array($body)) {
            $options[$isQueryMethod ? 'query' : 'json'] = $body;
        } elseif ($body !== null) {
            $options['body'] = (string) $body;
        }

        $response = $request->send($method, $url, $options);

        if (! $response->successful()) {
            $this->logger->error('integration_webhook_failed', [
                'status' => $response->status(),
                'url' => $url,
            ]);

            throw new IntegrationException('Webhook request failed with status '.$response->status().'.');
        }

        $this->logger->info('integration_webhook_sent', [
            'url' => $url,
            'status' => $response->status(),
        ]);
    }
}
