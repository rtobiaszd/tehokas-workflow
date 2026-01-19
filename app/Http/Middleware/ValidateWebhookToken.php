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

       if (! $expectedToken) {
            return response()->json([
                'message' => 'Unauthorized',
                'reason' => 'webhook_token_not_configured',
            ], 401);
        }

        if (! $providedToken) {
            return response()->json([
                'message' => 'Unauthorized',
                'reason' => 'webhook_token_missing',
            ], 401);
        }

        if (! hash_equals($expectedToken, $providedToken)) {
            return response()->json([
                'message' => 'Unauthorized',
                'reason' => 'invalid_webhook_token',
            ], 401);
        }


        return $next($request);
    }
}
