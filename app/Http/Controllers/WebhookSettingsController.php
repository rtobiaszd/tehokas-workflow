<?php

namespace App\Http\Controllers;

use App\Services\TenantSettingService;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class WebhookSettingsController extends Controller
{
    public function index(TenantSettingService $settingService): Response
    {
        Gate::authorize('view-webhooks');

        return Inertia::render('Webhooks/Index', [
            'endpoint' => '/api/webhooks/workflows',
            'tokenConfigured' => (bool) $settingService->get('integrations.webhook.token'),
        ]);
    }
}
