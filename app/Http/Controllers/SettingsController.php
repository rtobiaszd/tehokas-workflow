<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;
use App\Services\TenantSettingService;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(Request $request, TenantSettingService $settingService, TenantContext $tenantContext): Response
    {
        Gate::authorize('view-settings');

        return Inertia::render('Settings/Index', [
            'settings' => $this->sanitizeSettings($settingService->forTenant()),
            'integrationsStatus' => $settingService->integrationsStatus(),
            'canEdit' => Gate::allows('manage-settings'),
            'hasTenant' => (bool) $tenantContext->tenant(),
        ]);
    }

    public function update(Request $request, TenantSettingService $settingService): RedirectResponse
    {
        Gate::authorize('manage-settings');

        $data = $request->validate([
            'settings' => ['required', 'array'],
            'settings.notifications' => ['nullable', 'array'],
            'settings.notifications.email.enabled' => ['nullable', 'boolean'],
            'settings.notifications.slack.enabled' => ['nullable', 'boolean'],
            'settings.notifications.whatsapp.enabled' => ['nullable', 'boolean'],
            'settings.integrations' => ['nullable', 'array'],
            'settings.integrations.email.enabled' => ['nullable', 'boolean'],
            'settings.integrations.webhook.enabled' => ['nullable', 'boolean'],
            'settings.integrations.slack.enabled' => ['nullable', 'boolean'],
            'settings.integrations.teams.enabled' => ['nullable', 'boolean'],
            'settings.integrations.whatsapp.enabled' => ['nullable', 'boolean'],
            'settings.integrations.google_sheets.enabled' => ['nullable', 'boolean'],
            'settings.integrations.salesforce.enabled' => ['nullable', 'boolean'],
            'settings.integrations.hubspot.enabled' => ['nullable', 'boolean'],
            'settings.integrations.email.credentials' => ['nullable', 'array'],
            'settings.integrations.email.credentials.host' => ['nullable', 'string', 'max:255'],
            'settings.integrations.email.credentials.port' => ['nullable', 'string', 'max:20'],
            'settings.integrations.email.credentials.encryption' => ['nullable', 'string', 'max:20'],
            'settings.integrations.email.credentials.username' => ['nullable', 'string', 'max:255'],
            'settings.integrations.email.credentials.password' => ['nullable', 'string', 'max:255'],
            'settings.integrations.email.credentials.from_name' => ['nullable', 'string', 'max:255'],
            'settings.integrations.email.credentials.from_email' => ['nullable', 'string', 'max:255'],
            'settings.integrations.webhook.credentials' => ['nullable', 'array'],
            'settings.integrations.webhook.credentials.headers' => ['nullable', function ($attribute, $value, $fail) {
                if ($value === null || $value === '') {
                    return;
                }

                if (is_array($value)) {
                    return;
                }

                if (is_string($value)) {
                    json_decode($value, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $fail('Headers devem estar em JSON valido.');
                    }
                    return;
                }

                $fail('Headers devem estar em formato JSON.');
            }],
            'settings.integrations.webhook.credentials.timeout' => ['nullable', 'integer', 'min:1'],
            'settings.integrations.webhook.credentials.retries' => ['nullable', 'integer', 'min:0'],
            'settings.integrations.slack.credentials' => ['nullable', 'array'],
            'settings.integrations.slack.credentials.bot_token' => ['nullable', 'string', 'max:255'],
            'settings.integrations.slack.credentials.default_channel' => ['nullable', 'string', 'max:255'],
            'settings.integrations.slack.credentials.webhook_url' => ['nullable', 'string', 'max:255'],
            'settings.integrations.whatsapp.credentials' => ['nullable', 'array'],
            'settings.integrations.whatsapp.credentials.provider' => ['nullable', 'string', 'max:255'],
            'settings.integrations.whatsapp.credentials.api_token' => ['nullable', 'string', 'max:255'],
            'settings.integrations.whatsapp.credentials.phone_number_id' => ['nullable', 'string', 'max:255'],
            'settings.integrations.teams.credentials' => ['nullable', 'array'],
            'settings.integrations.teams.credentials.webhook_url' => ['nullable', 'string', 'max:255'],
            'settings.integrations.teams.credentials.tenant_id' => ['nullable', 'string', 'max:255'],
            'settings.integrations.google_sheets.credentials' => ['nullable', 'array'],
            'settings.integrations.google_sheets.credentials.service_account_json' => ['nullable', 'string'],
            'settings.integrations.google_sheets.credentials.spreadsheet_id' => ['nullable', 'string', 'max:255'],
            'settings.integrations.google_sheets.credentials.default_sheet' => ['nullable', 'string', 'max:255'],
            'settings.integrations.salesforce.credentials' => ['nullable', 'array'],
            'settings.integrations.salesforce.credentials.client_id' => ['nullable', 'string', 'max:255'],
            'settings.integrations.salesforce.credentials.client_secret' => ['nullable', 'string', 'max:255'],
            'settings.integrations.salesforce.credentials.refresh_token' => ['nullable', 'string', 'max:255'],
            'settings.integrations.salesforce.credentials.instance_url' => ['nullable', 'string', 'max:255'],
            'settings.integrations.hubspot.credentials' => ['nullable', 'array'],
            'settings.integrations.hubspot.credentials.access_token' => ['nullable', 'string', 'max:255'],
            'settings.integrations.hubspot.credentials.refresh_token' => ['nullable', 'string', 'max:255'],
            'settings.policies' => ['nullable', 'array'],
            'settings.policies.workflow' => ['nullable', 'array'],
            'settings.policies.workflow.rate_limit' => ['nullable', 'integer', 'min:0'],
            'settings.policies.workflow.retry_on_fail' => ['nullable', 'boolean'],
            'settings.observability' => ['nullable', 'array'],
            'settings.observability.logs.enabled' => ['nullable', 'boolean'],
            'settings.observability.external.enabled' => ['nullable', 'boolean'],
            'webhook_token' => ['nullable', 'string', 'max:255'],
        ]);

        $current = $settingService->forTenant();
        $settings = $this->mergeSettings($current, $data['settings']);

        if (! empty($data['webhook_token'])) {
            Arr::set($settings, 'integrations.webhook.token', $data['webhook_token']);
        }

        $headers = Arr::get($settings, 'integrations.webhook.credentials.headers');
        if (is_string($headers)) {
            $decoded = json_decode($headers, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                Arr::set($settings, 'integrations.webhook.credentials.headers', $decoded);
            }
        }

        $settingService->update($settings);

        return back()->with('success', 'Settings atualizados com sucesso.');
    }

    public function regenerateWebhookToken(TenantSettingService $settingService): RedirectResponse
    {
        Gate::authorize('manage-settings');

        $settings = $settingService->forTenant();
        $token = Str::random(48);

        Arr::set($settings, 'integrations.webhook.token', $token);
        $settingService->update($settings);

        return back()
            ->with('success', 'Novo token gerado com sucesso.')
            ->with('token_preview', $token);
    }

    public function testIntegration(Request $request, TenantSettingService $settingService): RedirectResponse
    {
        Gate::authorize('manage-settings');

        $data = $request->validate([
            'integration' => ['required', 'string', 'in:email,webhook,slack,whatsapp,teams,google_sheets,salesforce,hubspot'],
        ]);

        $status = $settingService->integrationsStatus();
        $integration = $data['integration'];
        $state = $status[$integration] ?? ['enabled' => false, 'configured' => false];

        if (! $state['enabled']) {
            return back()->with('error', 'Integracao desativada. Ative para testar.');
        }

        if (! $state['configured']) {
            return back()->with('error', 'Credenciais incompletas para esta integracao.');
        }

        return back()->with('success', 'Conexao validada com sucesso.');
    }

    private function sanitizeSettings(array $settings): array
    {
        $sensitivePaths = [
            'integrations.webhook.token',
            'integrations.email.credentials.password',
            'integrations.slack.credentials.bot_token',
            'integrations.whatsapp.credentials.api_token',
            'integrations.google_sheets.credentials.service_account_json',
            'integrations.salesforce.credentials.client_secret',
            'integrations.salesforce.credentials.refresh_token',
            'integrations.hubspot.credentials.access_token',
            'integrations.hubspot.credentials.refresh_token',
        ];

        foreach ($sensitivePaths as $path) {
            if (Arr::has($settings, $path)) {
                Arr::set($settings, $path, '');
            }
        }

        return $settings;
    }

    private function mergeSettings(array $current, array $incoming): array
    {
        $merged = array_replace_recursive($current, $incoming);
        $sensitivePaths = [
            'integrations.webhook.token',
            'integrations.email.credentials.password',
            'integrations.slack.credentials.bot_token',
            'integrations.whatsapp.credentials.api_token',
            'integrations.google_sheets.credentials.service_account_json',
            'integrations.salesforce.credentials.client_secret',
            'integrations.salesforce.credentials.refresh_token',
            'integrations.hubspot.credentials.access_token',
            'integrations.hubspot.credentials.refresh_token',
        ];

        foreach ($sensitivePaths as $path) {
            $incomingValue = Arr::get($incoming, $path);
            if ($incomingValue === null || $incomingValue === '') {
                Arr::set($merged, $path, Arr::get($current, $path));
            }
        }

        return $merged;
    }
}
