<?php

namespace App\Repositories;

use App\Models\Tenant;
use App\Repositories\Contracts\TenantRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class TenantRepository implements TenantRepositoryInterface
{
    public function paginate(?string $search = null, int $perPage = 24): LengthAwarePaginator
    {
        return Tenant::query()
            ->withCount('users')
            ->with(['primaryContract' => fn ($query) => $query->withCount(['sites', 'people'])])
            ->when($search, function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('trade_name', 'like', '%'.$search.'%')
                        ->orWhere('legal_name', 'like', '%'.$search.'%')
                        ->orWhere('nit', 'like', '%'.$search.'%')
                        ->orWhere('city', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(max(1, $perPage))
            ->withQueryString();
    }

    public function allOrdered(): Collection
    {
        return Tenant::query()->orderBy('name')->get();
    }

    public function findById(int $id): ?Tenant
    {
        return Tenant::query()->with('primaryContract')->find($id);
    }
}
