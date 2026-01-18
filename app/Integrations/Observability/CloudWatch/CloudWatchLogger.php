<?php

namespace App\Integrations\Observability\CloudWatch;

use App\Integrations\Observability\Contracts\LoggerInterface;
use Illuminate\Support\Facades\Log;

class CloudWatchLogger implements LoggerInterface
{
    public function info(string $event, array $context = []): void
    {
        Log::info('cloudwatch_stub:'.$event, $context);
    }

    public function error(string $event, array $context = []): void
    {
        Log::error('cloudwatch_stub:'.$event, $context);
    }
}
