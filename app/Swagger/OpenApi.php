<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\OpenApi(
    info: new OA\Info(
        title: 'Workflow Engine API',
        version: '1.0.0',
        description: 'API documentation for workflow webhooks and admin endpoints'
    ),
    servers: [
        new OA\Server(
            url: '/',
            description: 'Default API Server'
        )
    ]
)]
class OpenApi {}
