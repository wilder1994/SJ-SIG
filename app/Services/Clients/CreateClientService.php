<?php

namespace App\Services\Clients;

use App\Models\Contract;
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
