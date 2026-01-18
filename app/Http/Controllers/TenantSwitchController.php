<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TenantSwitchController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        Gate::authorize('switch-tenant');

        $data = $request->validate([
            'tenant_id' => ['nullable', 'integer', 'exists:tenants,id'],
        ]);

        if (empty($data['tenant_id'])) {
            $request->session()->forget('impersonated_tenant_id');
        } else {
            $request->session()->put('impersonated_tenant_id', (int) $data['tenant_id']);
        }

        return back();
    }
}
