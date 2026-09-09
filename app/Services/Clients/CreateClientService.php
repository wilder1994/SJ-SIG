<?php

namespace App\Services\Clients;

use App\Models\Contract;
use App\Models\Site;
use App\Models\Tenant;
use Illuminate\Support\Str;

final class CreateClientService
{
    /** @param array{name: string, nit?: string|null, starts_on?: string|null, ends_on?: string|null} $payload */
    public function execute(array $payload): Tenant
    {
        $base = Str::slug($payload['name']);
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
        ]);

        $year = now()->year;
        $contract = Contract::query()->create([
            'tenant_id' => $tenant->id,
            'code' => strtoupper(Str::limit(str_replace('-', '', $slug), 12, '')).'-'.$year,
            'name' => 'Servicio de vigilancia '.$year,
            'starts_on' => $payload['starts_on'] ?? now()->toDateString(),
            'ends_on' => $payload['ends_on'] ?? now()->endOfYear()->toDateString(),
        ]);

        Site::query()->create([
            'tenant_id' => $tenant->id,
            'contract_id' => $contract->id,
            'code' => 'S01',
            'name' => 'Instalación principal',
            'city' => null,
        ]);

        return $tenant->load('primaryContract');
    }
}
