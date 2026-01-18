<?php

namespace App\Services;

use App\Models\Tenant;

class TenantContext
{
    private ?Tenant $tenant = null;
    private ?int $tenantId = null;

    public function setTenant(?Tenant $tenant): void
    {
        $this->tenant = $tenant;
        $this->tenantId = $tenant?->id;
    }

    public function setTenantId(?int $tenantId): void
    {
        $this->tenantId = $tenantId;

        if ($tenantId === null) {
            $this->tenant = null;
        }
    }

    public function tenant(): ?Tenant
    {
        return $this->tenant;
    }

    public function tenantId(): ?int
    {
        return $this->tenant?->id ?? $this->tenantId;
    }
}
