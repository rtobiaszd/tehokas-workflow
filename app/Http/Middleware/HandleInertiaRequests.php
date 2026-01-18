<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Tenant;
use App\Services\TenantContext;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $user = $request->user();
        $tenant = app(TenantContext::class)->tenant();

        return array_merge(parent::share($request), [
            'appName' => config('app.name'),
            'auth' => [
                'user' => $user,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'tokenPreview' => $request->session()->get('token_preview'),
            ],
            'permissions' => [
                'canManageWorkflows' => $user?->isAdmin(),
                'canManageUsers' => $user?->isRoot() || $user?->isAdmin(),
                'canManageTenants' => $user?->isRoot(),
                'canEditSettings' => $user?->isAdmin(),
                'role' => $user?->role,
            ],
            'currentTenant' => $tenant ? [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
            ] : null,
            'navigation' => $this->navigationFor($user),
            'tenantSwitcher' => $user?->isRoot()
                ? [
                    'current' => $request->session()->get('impersonated_tenant_id'),
                    'tenants' => Tenant::orderBy('name')->get(['id', 'name', 'slug'])->map(fn ($tenant) => [
                        'id' => $tenant->id,
                        'name' => $tenant->name,
                        'slug' => $tenant->slug,
                    ])->all(),
                ]
                : null,
        ]);
    }

    private function navigationFor($user): array
    {
        if (! $user) {
            return [];
        }

        if ($user->isRoot()) {
            return [
                ['label' => 'Dashboard', 'href' => '/', 'icon' => 'grid'],
                ['label' => 'Companies', 'href' => '/companies', 'icon' => 'office'],
                ['label' => 'Workflows', 'href' => '/workflows', 'icon' => 'flows'],
                ['label' => 'Logs', 'href' => '/logs', 'icon' => 'log'],
                ['label' => 'Users', 'href' => '/users', 'icon' => 'users'],
                ['label' => 'Settings', 'href' => '/settings', 'icon' => 'settings'],
            ];
        }

        if ($user->isAdmin()) {
            return [
                ['label' => 'Dashboard', 'href' => '/', 'icon' => 'grid'],
                ['label' => 'Workflows', 'href' => '/workflows', 'icon' => 'flows'],
                ['label' => 'Webhooks', 'href' => '/webhooks', 'icon' => 'webhook'],
                ['label' => 'Logs', 'href' => '/logs', 'icon' => 'log'],
                ['label' => 'Users', 'href' => '/users', 'icon' => 'users'],
                ['label' => 'Settings', 'href' => '/settings', 'icon' => 'settings'],
            ];
        }

        return [
            ['label' => 'Dashboard', 'href' => '/', 'icon' => 'grid'],
            ['label' => 'Workflows', 'href' => '/workflows', 'icon' => 'flows'],
            ['label' => 'Settings', 'href' => '/settings', 'icon' => 'settings'],
        ];
    }
}
