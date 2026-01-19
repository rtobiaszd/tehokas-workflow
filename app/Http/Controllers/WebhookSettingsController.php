<?php

namespace App\Http\Controllers;

use App\Services\TenantContext;
use App\Services\TenantSettingService;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class WebhookSettingsController extends Controller
{
    public function index(TenantSettingService $settingService, TenantContext $tenantContext): Response
    {
        Gate::authorize('view-webhooks');

        $token = (string) $settingService->get('integrations.webhook.token', '');

        return Inertia::render('Webhooks/Index', [
            'endpoint' => '/api/webhooks/workflows',
            'tokenConfigured' => (bool) $token,
            'webhookToken' => $token,
            'tenantId' => $tenantContext->tenantId(),
        ]);
    }
}
