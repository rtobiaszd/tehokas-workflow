<?php

namespace App\Integrations\Observability\Sentry;

use App\Integrations\Observability\Contracts\LoggerInterface;
use Illuminate\Support\Facades\Log;

class SentryLogger implements LoggerInterface
{
    public function info(string $event, array $context = []): void
    {
        Log::info('sentry_stub:'.$event, $context);
    }

    public function error(string $event, array $context = []): void
    {
        Log::error('sentry_stub:'.$event, $context);
    }
}
