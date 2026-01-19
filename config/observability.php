<?php

use App\Integrations\Observability\CloudWatch\CloudWatchLogger;
use App\Integrations\Observability\LogLogger;
use App\Integrations\Observability\Sentry\SentryLogger;

return [
    'default' => env('OBSERVABILITY_DRIVER', 'log'),

    'drivers' => [
        'log' => LogLogger::class,
        'sentry' => SentryLogger::class,
        'cloudwatch' => CloudWatchLogger::class,
    ],
];
