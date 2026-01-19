<?php

namespace App\Integrations\Observability\Contracts;

interface LoggerInterface
{
    public function info(string $event, array $context = []): void;

    public function error(string $event, array $context = []): void;
}
