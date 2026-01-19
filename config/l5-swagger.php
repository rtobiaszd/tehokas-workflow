<?php

return [
    'default' => [
        'api' => [
            'title' => 'Workflow Engine API',
            'description' => 'API documentation for workflow webhooks and admin endpoints.',
        ],
        'routes' => [
            'api' => 'docs',
            'docs' => 'api/documentation',
        ],
        'paths' => [
            'annotations' => [
                base_path('app/Swagger'),
                base_path('app/Http/Controllers'),
            ],
            'docs' => storage_path('api-docs'),
        ],
    ],
];

