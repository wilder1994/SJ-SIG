<?php

namespace App\Services\Personnel;

use App\Models\Contract;
use App\Models\Person;

final class CreatePersonService
{
    /** @param  array<string, mixed>  $payload */
    public function execute(Contract $contract, array $payload): Person
    {
        $person = Person::query()->updateOrCreate(
            [
                'tenant_id' => $contract->tenant_id,
                'document_number' => $payload['document_number'],
            ],
            [
                ...$payload,
                'tenant_id' => $contract->tenant_id,
            ],
        );

        $person->contracts()->syncWithoutDetaching([
            $contract->id => [
                'tenant_id' => $contract->tenant_id,
                'post_id' => $payload['post_id'] ?? null,
            ],
        ]);

        return $person;
    }
}
