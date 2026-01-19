<?php

namespace App\Http\Middleware;

use App\Services\TenantSettingService;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ValidateWebhookToken
{
    public function __construct(private TenantSettingService $settingService)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $expectedToken = (string) $this->settingService->get('integrations.webhook.token', '');
        $providedToken = (string) $request->header('X-WEBHOOK-TOKEN', '');

        if ($expectedToken === '') {
            return $this->unauthorized('webhook_token_not_configured');
        }

        if ($providedToken === '') {
            return $this->unauthorized('webhook_token_missing');
        }

        if (! hash_equals($expectedToken, $providedToken)) {
            return $this->unauthorized('invalid_webhook_token');
        }

        return $next($request);
    }

    private function unauthorized(string $reason): JsonResponse
    {
        return response()->json([
            'message' => 'Unauthorized',
            'reason' => $reason,
        ], 401);
    }
}
