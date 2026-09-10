<?php

namespace App\Services\Structure;

use App\Models\Contract;
use App\Models\Site;

final class PersistSiteService
{
    /** @param array<string, mixed> $payload */
    public function execute(Contract $contract, array $payload): Site
    {
        return Site::query()->create([
            'tenant_id' => $contract->tenant_id,
            'contract_id' => $contract->id,
            'code' => strtoupper($payload['code']),
            'name' => $payload['name'],
            'city' => $payload['city'] ?: null,
            'address' => $payload['address'] ?? null,
            'department' => $payload['department'] ?? null,
            'lat' => $payload['lat'] ?? null,
            'lng' => $payload['lng'] ?? null,
            'place_id' => $payload['place_id'] ?? null,
        ]);
    }
}
