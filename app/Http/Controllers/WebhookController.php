<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessWorkflowJob;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Webhooks',
    description: 'Workflow webhooks'
)]
class WebhookController extends Controller
{
    #[OA\Post(
        path: '/api/webhooks/workflows',
        summary: 'Receives workflow events',
        tags: ['Webhooks'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['event', 'data'],
                properties: [
                    new OA\Property(
                        property: 'event',
                        type: 'string',
                        example: 'project.status_changed'
                    ),
                    new OA\Property(
                        property: 'data',
                        type: 'object'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Queued'
            ),
            new OA\Response(
                response: 422,
                description: 'Tenant not resolved'
            ),
        ]
    )]
    public function handle(
        Request $request,
        TenantContext $tenantContext
    ): JsonResponse {
        $payload = $request->validate([
            'event' => ['required', 'string'],
            'data' => ['required', 'array'],
        ]);

        $tenantId = $tenantContext->tenantId();

        if (! $tenantId) {
            return response()->json(['message' => 'Tenant not resolved'], 422);
        }

        ProcessWorkflowJob::dispatch($tenantId, $payload)
            ->onQueue('workflows');

        Log::info('Workflow webhook queued.', [
            'tenant_id' => $tenantId,
            'event' => $payload['event'],
        ]);

        return response()->json(['status' => 'queued']);
    }
}
