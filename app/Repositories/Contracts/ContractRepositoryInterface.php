<?php

namespace App\Repositories\Contracts;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Support\Collection;

interface ContractRepositoryInterface
{
    public function findAccessibleById(User $user, int $id): ?Contract;

    /** @return Collection<int, Contract> */
    public function listAccessible(User $user): Collection;
}
