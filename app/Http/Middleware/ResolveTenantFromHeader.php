<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\TenantContext;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantFromHeader
{
    public function __construct(private TenantContext $tenantContext)
    {
    }

    public function handle(Request $request, Closure $next): JsonResponse|Response
    {
        $tenantId = $request->header('X-Tenant-ID');

        if ($tenantId === null || $tenantId === '') {
            return $this->unauthorized('tenant_missing');
        }

        if (! ctype_digit((string) $tenantId)) {
            return $this->unauthorized('tenant_not_found');
        }

        $tenant = Tenant::find((int) $tenantId);

        if (! $tenant) {
            return $this->unauthorized('tenant_not_found');
        }

        if (! $tenant->is_active) {
            return $this->unauthorized('tenant_inactive');
        }

        $this->tenantContext->setTenant($tenant);

        return $next($request);
    }

    private function unauthorized(string $reason, string $message = 'Unauthorized'): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'reason' => $reason,
        ], 401);
    }
}
