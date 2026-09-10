<?php

namespace App\Services\Personnel;

use App\Models\Contract;
use App\Models\Person;
use Illuminate\Http\UploadedFile;

final class CreatePersonService
{
    public function __construct(private readonly StorePersonPhotoService $photos) {}

    /** @param  array<string, mixed>  $payload */
    public function execute(Contract $contract, array $payload, ?UploadedFile $photo = null): Person
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

        if ($photo !== null) {
            $this->photos->execute($person, $photo);
        }

        return $person;
    }

    /** @param  array<string, mixed>  $payload */
    public function update(Person $person, array $payload, ?UploadedFile $photo = null): Person
    {
        unset($payload['post_id'], $payload['tenant_id']);
        $person->fill($payload)->save();

        if ($photo !== null) {
            $this->photos->execute($person, $photo);
        }

        return $person->fresh() ?? $person;
    }
}
