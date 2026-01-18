<?php

namespace App\Repositories;

use App\Models\Tenant;
use App\Models\TenantSetting;
use Illuminate\Support\Arr;

class TenantSettingRepository
{
    /**
     * Retorna settings resolvidos (defaults + overrides do tenant)
     */
    public function getForTenant(Tenant $tenant): array
    {
        $defaults = $this->defaults();

        // Busca todas as settings do tenant (key/value)
        $records = TenantSetting::where('tenant_id', $tenant->id)->get();

        $overrides = [];

        foreach ($records as $record) {
            Arr::set($overrides, $record->key, $record->value);
        }

        // Merge: defaults <- overrides
        return array_replace_recursive($defaults, $overrides);
    }

    /**
     * Salva settings do tenant (sobrescrevendo ou criando keys)
     */
    public function updateForTenant(Tenant $tenant, array $settings): array
    {
        $flat = $this->flatten($settings);

        foreach ($flat as $key => $value) {
            TenantSetting::updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'key' => $key,
                ],
                [
                    'value' => $value,
                ]
            );
        }

        return $this->getForTenant($tenant);
    }

    /**
     * Defaults globais do sistema
     */
    private function defaults(): array
    {
        return config('tenant_settings.defaults', []);
    }

    /**
     * Converte array aninhado em dot-notation
     *
     * Ex:
     * integrations => webhook => token
     * vira:
     * integrations.webhook.token
     */
    private function flatten(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value)) {
                $result += $this->flatten($value, $newKey);
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }
}
