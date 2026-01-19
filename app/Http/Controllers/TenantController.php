<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TenantController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('view-tenants');

        $user = $request->user();

        if ($user->isRoot()) {
            $tenants = Tenant::query()
                ->orderBy('name')
                ->paginate(10)
                ->withQueryString();

            return Inertia::render('Companies/Index', [
                'tenants' => $tenants,
                'canCreate' => true,
            ]);
        }

        $tenant = Tenant::find($user->tenant_id);

        return Inertia::render('Companies/Index', [
            'tenants' => $tenant ? collect([$tenant]) : collect(),
            'canCreate' => false,
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('manage-tenants');

        return Inertia::render('Companies/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage-tenants');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:tenants,slug'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $slug = $data['slug'] ?: Str::slug($data['name']);

        Tenant::create([
            'name' => $data['name'],
            'slug' => $slug,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return redirect()->route('companies.index')->with('success', 'Empresa criada com sucesso.');
    }

    public function edit(Tenant $tenant): Response
    {
        Gate::authorize('view-tenant', $tenant);

        return Inertia::render('Companies/Edit', [
            'tenant' => $tenant,
            'canManage' => Gate::allows('manage-tenants'),
        ]);
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        Gate::authorize('view-tenant', $tenant);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];

        if (Gate::allows('manage-tenants')) {
            $rules['slug'] = ['nullable', 'string', 'max:255', 'unique:tenants,slug,'.$tenant->id];
        }

        $data = $request->validate($rules);

        $tenant->update([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? $tenant->slug,
            'is_active' => $data['is_active'] ?? $tenant->is_active,
        ]);

        return redirect()->route('companies.index')->with('success', 'Empresa atualizada com sucesso.');
    }
}
