<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;

class SetTenantContext
{
    public function __construct(private TenantContext $tenantContext)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $tenant = null;
        $user = $request->user();

        if ($user?->isRoot()) {
            $impersonatedTenantId = $request->session()->get('impersonated_tenant_id');
            $tenant = $impersonatedTenantId ? Tenant::find($impersonatedTenantId) : null;
            $this->tenantContext->setTenant($tenant);

            return $next($request);
        }

        $tenantId = $request->header('X-Tenant-ID');
        $tenantSlug = $request->header('X-Tenant-Slug');

        if ($tenantId) {
            $tenant = Tenant::find($tenantId);
        } elseif ($tenantSlug) {
            $tenant = Tenant::where('slug', $tenantSlug)->first();
        } elseif ($user?->tenant_id) {
            $tenant = Tenant::find($user->tenant_id);
        }

        if (! $tenant && $user) {
            $this->tenantContext->setTenantId(0);
        } else {
            $this->tenantContext->setTenant($tenant);
        }

        return $next($request);
    }
}
