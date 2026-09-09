<?php

namespace App\Policies;

use App\Models\Person;
use App\Models\User;

final class PersonPolicy
{
    public function view(User $user, Person $person): bool
    {
        if ($user->role->seesAllClients()) {
            return true;
        }

        return (int) $user->tenant_id === (int) $person->tenant_id;
    }
}
