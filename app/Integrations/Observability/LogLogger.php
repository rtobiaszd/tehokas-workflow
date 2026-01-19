<?php

namespace App\Integrations\Observability;

use App\Integrations\Observability\Contracts\LoggerInterface;
use App\Services\TenantSettingService;
use Illuminate\Support\Facades\Log;

class LogLogger implements LoggerInterface
{
    public function __construct(private TenantSettingService $tenantSettingService)
    {
    }

    public function info(string $event, array $context = []): void
    {
        if (! $this->tenantSettingService->get('observability.logs.enabled', true)) {
            return;
        }

        Log::info($event, $context);
    }

    public function error(string $event, array $context = []): void
    {
        if (! $this->tenantSettingService->get('observability.logs.enabled', true)) {
            return;
        }

        Log::error($event, $context);
    }
}
