<?php

namespace App\Repositories\Contracts;

use App\Models\Person;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PersonRepositoryInterface
{
    public function paginateForContract(int $contractId, ?string $search = null, bool $documentStats = false): LengthAwarePaginator;

    public function findInContract(int $contractId, int $personId): ?Person;
}
