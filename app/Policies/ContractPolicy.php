<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;

final class ContractPolicy
{
    public function view(User $user, Contract $contract): bool
    {
        if ($user->role->seesAllClients()) {
            return true;
        }

        if ($user->boundContractId() !== null) {
            return $user->boundContractId() === (int) $contract->id;
        }

        return (int) $user->tenant_id === (int) $contract->tenant_id;
    }
}
