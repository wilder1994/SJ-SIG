<?php

namespace App\Services\Clients;

use App\Models\Contract;
use App\Models\Tenant;
use Illuminate\Support\Str;

final class CreateClientService
{
    /** @param array<string, mixed> $payload */
    public function execute(array $payload): Tenant
    {
        $base = Str::slug((string) $payload['name']);
        $slug = $base !== '' ? $base : 'cliente';
        $suffix = 1;
        while (Tenant::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        $tenant = Tenant::query()->create([
            'name' => $payload['name'],
            'slug' => $slug,
            'nit' => $payload['nit'] ?: null,
            'person_kind' => $payload['person_kind'] ?? 'juridica',
            'structure_type' => $payload['structure_type'] ?? null,
            'trade_name' => $payload['trade_name'] ?? null,
            'legal_name' => $payload['legal_name'] ?? $payload['name'],
            'document_type' => $payload['document_type'] ?? 'NIT',
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
        ]);

        $year = now()->year;
        Contract::query()->create([
            'tenant_id' => $tenant->id,
            'code' => strtoupper(Str::limit(str_replace('-', '', $slug), 12, '')).'-'.$year,
            'name' => 'Servicio de vigilancia '.$year,
            'starts_on' => $payload['starts_on'] ?? now()->toDateString(),
            'ends_on' => $payload['ends_on'] ?? now()->endOfYear()->toDateString(),
        ]);

        return $tenant->load('primaryContract');
    }
}
