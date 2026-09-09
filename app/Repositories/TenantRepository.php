<?php

namespace App\Repositories;

use App\Models\Tenant;
use App\Repositories\Contracts\TenantRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class TenantRepository implements TenantRepositoryInterface
{
    public function paginate(?string $search = null): LengthAwarePaginator
    {
        return Tenant::query()
            ->withCount('users')
            ->with('primaryContract')
            ->when($search, function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('nit', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(24)
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
