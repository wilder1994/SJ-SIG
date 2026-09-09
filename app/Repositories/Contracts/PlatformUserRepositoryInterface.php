<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PlatformUserRepositoryInterface
{
    public function paginate(?string $search = null): LengthAwarePaginator;

    /** @return Collection<int, \App\Models\User> */
    public function operationsForTenant(int $tenantId): Collection;

    public function emailExists(string $email, ?int $ignoreId = null): bool;

    public function documentExists(string $documentNumber, ?int $ignoreId = null): bool;
}
