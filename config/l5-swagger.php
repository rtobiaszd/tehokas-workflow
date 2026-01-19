<?php

return [

    'default' => 'default',

    'documentations' => [
        'default' => [
            'api' => [
                'title' => 'Workflow Engine API',
                'description' => 'API documentation for workflow webhooks and admin endpoints.',
            ],

            'routes' => [
                'api' => 'api/documentation',
            ],

            'paths' => [
                'docs_json' => 'api-docs.json',
                'annotations' => [
                    base_path('app/Swagger'),
                    base_path('app/Http/Controllers'),
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Definitions (FORA do documentations)
    |--------------------------------------------------------------------------
    */
    'securityDefinitions' => [

        'WebhookToken' => [
            'type' => 'apiKey',
            'in'   => 'header',
            'name' => 'X-Webhook-Token',
        ],

        'bearerAuth' => [
            'type' => 'http',
            'scheme' => 'bearer',
            'bearerFormat' => 'JWT',
        ],
    ],
];
