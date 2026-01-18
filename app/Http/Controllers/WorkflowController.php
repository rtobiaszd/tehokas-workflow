<?php

namespace App\Http\Controllers;

use App\Models\Workflow;
use App\Repositories\WorkflowRepository;
use App\Services\TenantSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class WorkflowController extends Controller
{
    public function index(Request $request, WorkflowRepository $workflowRepository): Response
    {
        Gate::authorize('view-workflows');

        $filters = [
            'search' => $request->string('search')->toString(),
            'status' => $request->string('status')->toString() ?: 'all',
        ];

        return Inertia::render('Workflows/Index', [
            'filters' => $filters,
            'workflows' => $workflowRepository->paginate(
                $filters['search'] ?: null,
                $filters['status'] !== 'all' ? $filters['status'] : null
            ),
        ]);
    }

    public function create(TenantSettingService $settingService): Response
    {
        Gate::authorize('manage-workflows');

        return Inertia::render('Workflows/Create', [
            'integrations' => $settingService->integrationsStatus(),
        ]);
    }

    public function edit(Workflow $workflow, TenantSettingService $settingService): Response
    {
        Gate::authorize('manage-workflows');

        return Inertia::render('Workflows/Edit', [
            'workflow' => $workflow->load(['triggers', 'conditions', 'actions']),
            'integrations' => $settingService->integrationsStatus(),
        ]);
    }

    public function store(Request $request, WorkflowRepository $workflowRepository): RedirectResponse
    {
        Gate::authorize('manage-workflows');

        $definition = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:active,draft'],
            'is_active' => ['nullable', 'boolean'],
            'trigger' => ['required', 'array'],
            'trigger.type' => ['required', 'string', 'max:100'],
            'trigger.config' => ['nullable', 'array'],
            'conditions' => ['nullable', 'array'],
            'conditions.*.field' => ['nullable', 'string', 'max:255'],
            'conditions.*.operator' => ['nullable', 'string', 'max:50'],
            'conditions.*.value' => ['nullable'],
            'conditions.*.payload' => ['nullable', function ($attribute, $value, $fail) {
                if ($value === null || $value === '') {
                    return;
                }

                if (is_string($value)) {
                    json_decode($value);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $fail('Payload precisa ser um JSON valido.');
                    }
                    return;
                }
            }],
            'actions' => ['required', 'array', 'min:1'],
            'actions.*.type' => ['required', 'string', 'max:100'],
            'actions.*.config' => ['nullable', 'array'],
            'actions.*.is_active' => ['nullable', 'boolean'],
            'actions.*.payload' => ['nullable', function ($attribute, $value, $fail) {
                if ($value === null || $value === '') {
                    return;
                }

                if (is_string($value)) {
                    json_decode($value);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $fail('Payload precisa ser um JSON valido.');
                    }
                    return;
                }
            }],
        ]);

        $definition = $this->normalizeDefinitionPayloads($definition);
        $definition['created_by'] = $request->user()?->name ?? 'System';

        $workflowRepository->createFromDefinition($definition);

        return redirect()->route('workflows.index')
            ->with('success', 'Workflow criado com sucesso.');
    }

    public function update(Request $request, Workflow $workflow, WorkflowRepository $workflowRepository): RedirectResponse
    {
        Gate::authorize('manage-workflows');

        $definition = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:active,draft'],
            'is_active' => ['nullable', 'boolean'],
            'trigger' => ['required', 'array'],
            'trigger.type' => ['required', 'string', 'max:100'],
            'trigger.config' => ['nullable', 'array'],
            'conditions' => ['nullable', 'array'],
            'conditions.*.field' => ['nullable', 'string', 'max:255'],
            'conditions.*.operator' => ['nullable', 'string', 'max:50'],
            'conditions.*.value' => ['nullable'],
            'conditions.*.payload' => ['nullable', function ($attribute, $value, $fail) {
                if ($value === null || $value === '') {
                    return;
                }

                if (is_string($value)) {
                    json_decode($value);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $fail('Payload precisa ser um JSON valido.');
                    }
                    return;
                }
            }],
            'actions' => ['required', 'array', 'min:1'],
            'actions.*.type' => ['required', 'string', 'max:100'],
            'actions.*.config' => ['nullable', 'array'],
            'actions.*.is_active' => ['nullable', 'boolean'],
            'actions.*.payload' => ['nullable', function ($attribute, $value, $fail) {
                if ($value === null || $value === '') {
                    return;
                }

                if (is_string($value)) {
                    json_decode($value);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $fail('Payload precisa ser um JSON valido.');
                    }
                    return;
                }
            }],
        ]);

        $definition = $this->normalizeDefinitionPayloads($definition);
        $definition['created_by'] = data_get($workflow->definition, 'created_by', $request->user()?->name ?? 'System');

        $workflowRepository->updateFromDefinition($workflow, $definition);

        return redirect()->route('workflows.index')
            ->with('success', 'Workflow atualizado com sucesso.');
    }

    private function normalizeDefinitionPayloads(array $definition): array
    {
        $definition['conditions'] = collect($definition['conditions'] ?? [])
            ->map(fn ($condition) => $this->normalizePayloadEntry($condition))
            ->all();

        $definition['actions'] = collect($definition['actions'] ?? [])
            ->map(fn ($action) => $this->normalizePayloadEntry($action))
            ->all();

        return $definition;
    }

    private function normalizePayloadEntry(array $entry): array
    {
        $payload = $entry['payload'] ?? null;

        if (is_string($payload)) {
            $decoded = json_decode($payload, true);
            $payload = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }

        $entry['payload'] = $payload;

        unset($entry['payload_text'], $entry['payload_error']);

        return $entry;
    }

    public function toggle(Workflow $workflow, WorkflowRepository $workflowRepository): RedirectResponse
    {
        Gate::authorize('manage-workflows');

        $workflowRepository->toggle($workflow);

        return back()->with('success', 'Status do workflow atualizado.');
    }
}
