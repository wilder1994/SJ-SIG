<?php

namespace App\Services\Clients;

use App\Models\Tenant;

final class UpdateClientService
{
    /** @param array{name: string, nit?: string|null} $payload */
    public function execute(Tenant $tenant, array $payload): Tenant
    {
        $tenant->fill([
            'name' => $payload['name'],
            'nit' => $payload['nit'] ?: null,
        ])->save();

        return $tenant;
    }
}
