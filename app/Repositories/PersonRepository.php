<?php

namespace App\Repositories;

use App\Models\Person;
use App\Models\PersonDocument;
use App\Repositories\Contracts\PersonRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class PersonRepository implements PersonRepositoryInterface
{
    public function paginateForContract(int $contractId, ?string $search = null, bool $documentStats = false): LengthAwarePaginator
    {
        $query = Person::query()
            ->whereHas('contracts', fn ($q) => $q->where('contracts.id', $contractId))
            ->when($search, function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('full_name', 'like', '%'.$search.'%')
                        ->orWhere('document_number', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('full_name');

        if ($documentStats) {
            $query->select('people.*')
                ->withCount(['documents as documents_count' => fn ($q) => $q->withPdf()])
                ->selectSub(
                    PersonDocument::query()
                        ->selectRaw('count(distinct folder)')
                        ->whereColumn('person_id', 'people.id')
                        ->withPdf(),
                    'folders_count',
                );
        } else {
            $query->withCount('documents');
        }

        return $query->paginate(24)->withQueryString();
    }

    public function findInContract(int $contractId, int $personId): ?Person
    {
        return Person::query()
            ->whereKey($personId)
            ->whereHas('contracts', fn ($q) => $q->where('contracts.id', $contractId))
            ->with(['courses', 'documents'])
            ->first();
    }
}
