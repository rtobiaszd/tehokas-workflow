<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('view-users');

        $user = $request->user();

        $query = User::query()->with('tenant');

        if ($user->isAdmin()) {
            $query->where('tenant_id', $user->tenant_id);
        }

        $users = $query->orderBy('name')->paginate(12)->withQueryString();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'canManage' => Gate::allows('manage-users'),
            'canCreate' => Gate::allows('manage-users'),
        ]);
    }

    public function create(Request $request): Response
    {
        Gate::authorize('manage-users');

        $user = $request->user();

        return Inertia::render('Users/Create', [
            'roles' => $user->isRoot()
                ? ['admin', 'user']
                : ['user'],
            'tenants' => $user->isRoot()
                ? Tenant::orderBy('name')->get(['id', 'name'])
                : [],
            'isRoot' => $user->isRoot(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('manage-users');

        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'string', 'in:admin,user'],
            'tenant_id' => ['nullable', 'integer', 'exists:tenants,id'],
        ]);

        if ($user->isAdmin()) {
            $data['role'] = 'user';
            $data['tenant_id'] = $user->tenant_id;
        }

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'tenant_id' => $data['tenant_id'],
        ]);

        return redirect()->route('users.index')->with('success', 'Usuario criado com sucesso.');
    }
}
