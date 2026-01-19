<?php

namespace App\Providers;

use App\Integrations\IntegrationManager;
use App\Integrations\Observability\Contracts\LoggerInterface;
use App\Integrations\Observability\LogLogger;
use App\Repositories\TenantSettingRepository;
use App\Services\TenantContext;
use App\Services\TenantSettingService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantContext::class, fn () => new TenantContext());
        $this->app->singleton(TenantSettingRepository::class);
        $this->app->singleton(TenantSettingService::class);
        $this->app->singleton(IntegrationManager::class);
        $this->app->singleton(LoggerInterface::class, function ($app) {
            $driver = config('observability.default', 'log');
            $class = config("observability.drivers.{$driver}", LogLogger::class);

            return $app->make($class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
