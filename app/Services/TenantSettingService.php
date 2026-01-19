<?php

namespace App\Services;

use App\Models\Tenant;
use App\Repositories\TenantSettingRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class TenantSettingService
{
    public function __construct(
        private TenantSettingRepository $repository,
        private TenantContext $tenantContext
    ) {
    }

    public function forTenant(?Tenant $tenant = null): array
    {
        $tenant = $tenant ?? $this->tenantContext->tenant();

        if (! $tenant && $this->tenantContext->tenantId()) {
            $tenant = Tenant::find($this->tenantContext->tenantId());
            $this->tenantContext->setTenant($tenant);
        }

        if (! $tenant) {
            return [];
        }

        return Cache::remember(
            $this->cacheKey($tenant->id),
            now()->addMinutes(10),
            fn () => $this->repository->getForTenant($tenant)
        );
    }

    public function update(array $settings): array
    {
        $tenant = $this->tenantContext->tenant();

        if (! $tenant) {
            return [];
        }

        $updated = $this->repository->updateForTenant($tenant, $settings);
        Cache::forget($this->cacheKey($tenant->id));

        return $updated;
    }

    public function get(string $path, mixed $default = null): mixed
    {
        $settings = $this->forTenant();

        return Arr::get($settings, $path, $default);
    }

    public function integrationEnabled(string $key, bool $default = false): bool
    {
        $enabled = $this->get("integrations.{$key}.enabled");

        if ($enabled === null) {
            return $default;
        }

        return (bool) $enabled;
    }

    public function integrationValue(string $key, string $path, mixed $default = null): mixed
    {
        return $this->get("integrations.{$key}.{$path}", $default);
    }

    public function integrationsStatus(): array
    {
        $settings = $this->forTenant();
        $integrations = $settings['integrations'] ?? [];

        return [
            'email' => $this->integrationState(
                (bool) data_get($integrations, 'email.enabled', false),
                $this->hasEmailCredentials($integrations['email']['credentials'] ?? [])
            ),
            'webhook' => $this->integrationState(
                (bool) data_get($integrations, 'webhook.enabled', false),
                ! empty(data_get($integrations, 'webhook.token'))
            ),
            'slack' => $this->integrationState(
                (bool) data_get($integrations, 'slack.enabled', false),
                $this->hasSlackCredentials($integrations['slack']['credentials'] ?? [])
            ),
            'teams' => $this->integrationState(
                (bool) data_get($integrations, 'teams.enabled', false),
                ! empty(data_get($integrations, 'teams.credentials.webhook_url'))
            ),
            'whatsapp' => $this->integrationState(
                (bool) data_get($integrations, 'whatsapp.enabled', false),
                $this->hasWhatsAppCredentials($integrations['whatsapp']['credentials'] ?? [])
            ),
            'google_sheets' => $this->integrationState(
                (bool) data_get($integrations, 'google_sheets.enabled', false),
                $this->hasGoogleSheetsCredentials($integrations['google_sheets']['credentials'] ?? [])
            ),
            'salesforce' => $this->integrationState(
                (bool) data_get($integrations, 'salesforce.enabled', false),
                $this->hasSalesforceCredentials($integrations['salesforce']['credentials'] ?? [])
            ),
            'hubspot' => $this->integrationState(
                (bool) data_get($integrations, 'hubspot.enabled', false),
                ! empty(data_get($integrations, 'hubspot.credentials.access_token'))
            ),
        ];
    }

    private function cacheKey(int $tenantId): string
    {
        return "tenant:{$tenantId}:settings";
    }

    private function integrationState(bool $enabled, bool $configured): array
    {
        return [
            'enabled' => $enabled,
            'configured' => $configured,
        ];
    }

    private function hasEmailCredentials(array $credentials): bool
    {
        return ! empty($credentials['host'])
            && ! empty($credentials['port'])
            && ! empty($credentials['username'])
            && ! empty($credentials['password'])
            && ! empty($credentials['from_name'])
            && ! empty($credentials['from_email']);
    }

    private function hasSlackCredentials(array $credentials): bool
    {
        return ! empty($credentials['bot_token']) || ! empty($credentials['webhook_url']);
    }

    private function hasWhatsAppCredentials(array $credentials): bool
    {
        return ! empty($credentials['provider'])
            && ! empty($credentials['api_token'])
            && ! empty($credentials['phone_number_id']);
    }

    private function hasGoogleSheetsCredentials(array $credentials): bool
    {
        return ! empty($credentials['service_account_json'])
            && ! empty($credentials['spreadsheet_id']);
    }

    private function hasSalesforceCredentials(array $credentials): bool
    {
        return ! empty($credentials['client_id'])
            && ! empty($credentials['client_secret'])
            && ! empty($credentials['refresh_token'])
            && ! empty($credentials['instance_url']);
    }
}
