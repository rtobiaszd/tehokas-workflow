<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Workflow Engine API",
 *     description="API for the multi-tenant workflow automation engine."
 * )
 * @OA\Server(
 *     url="/",
 *     description="Default server"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="WebhookToken",
 *     type="apiKey",
 *     in="header",
 *     name="X-WEBHOOK-TOKEN"
 * )
 * @OA\Tag(
 *     name="Webhooks",
 *     description="Workflow webhook endpoints"
 * )
 */
class OpenApi
{
}
