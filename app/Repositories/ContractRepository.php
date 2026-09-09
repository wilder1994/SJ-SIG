<?php

namespace App\Repositories;

use App\Enums\UserRole;
use App\Models\Contract;
use App\Models\User;
use App\Repositories\Contracts\ContractRepositoryInterface;
use Illuminate\Support\Collection;

final class ContractRepository implements ContractRepositoryInterface
{
    public function findAccessibleById(User $user, int $id): ?Contract
    {
        $query = Contract::query()->with('tenant');

        if ($user->role === UserRole::AdminEmpresa) {
            return $query->find($id);
        }

        if ($user->boundContractId() !== null) {
            return $query->whereKey($user->boundContractId())->find($id);
        }

        return $query->where('tenant_id', $user->tenant_id)->find($id);
    }

    public function listAccessible(User $user): Collection
    {
        $query = Contract::query()->with('tenant')->orderBy('name');

        if ($user->role === UserRole::AdminEmpresa) {
            return $query->get();
        }

        if ($user->boundContractId() !== null) {
            return $query->whereKey($user->boundContractId())->get();
        }

        return $query->where('tenant_id', $user->tenant_id)->get();
    }
}
