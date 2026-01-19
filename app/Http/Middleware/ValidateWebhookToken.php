<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\TenantSettingService;

class ValidateWebhookToken
{
    public function __construct(private TenantSettingService $settingService)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $expectedToken = (string) $this->settingService->get('integrations.webhook.token', '');
        $providedToken = (string) $request->header('X-WEBHOOK-TOKEN');

        if (! $expectedToken || ! $providedToken || ! hash_equals($expectedToken, $providedToken)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
