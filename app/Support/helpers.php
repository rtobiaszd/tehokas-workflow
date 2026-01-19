<?php

use App\Services\TenantSettingService;

if (! function_exists('settings')) {
    function settings(?string $path = null, mixed $default = null): mixed
    {
        $service = app(TenantSettingService::class);

        if ($path === null) {
            return $service->forTenant();
        }

        return $service->get($path, $default);
    }
}
