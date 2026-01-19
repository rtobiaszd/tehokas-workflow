<?php

namespace App\Integrations\CRM\Contracts;

interface CrmDriverInterface
{
    public function upsertLead(array $payload): void;

    public function updateDeal(array $payload): void;
}
