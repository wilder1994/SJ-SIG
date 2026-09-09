<?php

namespace App\Repositories\Contracts;

use App\Models\Tenant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface TenantRepositoryInterface
{
    public function paginate(?string $search = null): LengthAwarePaginator;

    /** @return Collection<int, Tenant> */
    public function allOrdered(): Collection;

    public function findById(int $id): ?Tenant;
}
