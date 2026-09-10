<?php

namespace App\Services\Clients;

use App\Models\Tenant;

final class UpdateClientService
{
    /** @param array<string, mixed> $payload */
    public function execute(Tenant $tenant, array $payload): Tenant
    {
        $tenant->fill([
            'name' => $payload['name'],
            'nit' => $payload['nit'] ?: null,
            'person_kind' => $payload['person_kind'] ?? $tenant->person_kind,
            'structure_type' => $payload['structure_type'] ?? null,
            'trade_name' => $payload['trade_name'] ?? null,
            'legal_name' => $payload['legal_name'] ?? $payload['name'],
            'document_type' => $payload['document_type'] ?? $tenant->document_type,
            'contact_email' => $payload['contact_email'] ?? null,
            'phone' => $payload['phone'] ?? null,
            'legal_rep_name' => $payload['legal_rep_name'] ?? null,
            'legal_rep_email' => $payload['legal_rep_email'] ?? null,
            'address' => $payload['address'] ?? null,
            'city' => $payload['city'] ?? null,
            'department' => $payload['department'] ?? null,
            'lat' => $payload['lat'] ?? null,
            'lng' => $payload['lng'] ?? null,
            'place_id' => $payload['place_id'] ?? null,
        ])->save();

        return $tenant;
    }
}
