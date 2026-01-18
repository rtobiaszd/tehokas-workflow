<?php

namespace App\Providers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define('view-tenants', function (User $user) {
            return $user->isRoot() || $user->isAdmin();
        });

        Gate::define('manage-tenants', function (User $user) {
            return $user->isRoot();
        });

        Gate::define('view-users', function (User $user) {
            return $user->isRoot() || $user->isAdmin();
        });

        Gate::define('manage-users', function (User $user) {
            return $user->isRoot() || $user->isAdmin();
        });

        Gate::define('view-logs', function (User $user) {
            return $user->isRoot() || $user->isAdmin();
        });

        Gate::define('view-webhooks', function (User $user) {
            return $user->isRoot() || $user->isAdmin();
        });

        Gate::define('view-settings', function (User $user) {
            return $user->isRoot() || $user->isAdmin() || $user->isUser();
        });

        Gate::define('manage-settings', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('view-workflows', function (User $user) {
            return $user->isRoot() || $user->isAdmin() || $user->isUser();
        });

        Gate::define('manage-workflows', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('switch-tenant', function (User $user) {
            return $user->isRoot();
        });

        Gate::define('view-tenant', function (User $user, Tenant $tenant) {
            if ($user->isRoot()) {
                return true;
            }

            return (int) $user->tenant_id === (int) $tenant->id;
        });
    }
}
