<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessWorkflowJob;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

class WebhookController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/webhooks/workflows",
     *     tags={"Webhooks"},
     *     summary="Receives workflow events",
     *     security={{"WebhookToken": {}}},
     *     @OA\Parameter(
     *         name="X-Tenant-ID",
     *         in="header",
     *         description="Tenant ID (optional if resolved by session)",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="X-Tenant-Slug",
     *         in="header",
     *         description="Tenant slug (optional if resolved by session)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"event","data"},
     *             @OA\Property(property="event", type="string", example="project.status_changed"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 example={
     *                     "project_id":123,
     *                     "project_name":"Website Redesign",
     *                     "old_status":"In Progress",
     *                     "new_status":"Delayed",
     *                     "manager_email":"manager@company.com"
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Queued"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=422, description="Tenant not resolved")
     * )
     */
    public function handle(Request $request, TenantContext $tenantContext): JsonResponse
    {
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
