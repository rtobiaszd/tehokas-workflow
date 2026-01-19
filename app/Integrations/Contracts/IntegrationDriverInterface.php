<?php

namespace App\Integrations\Contracts;

interface IntegrationDriverInterface
{
    public function send(array $payload): void;
}
