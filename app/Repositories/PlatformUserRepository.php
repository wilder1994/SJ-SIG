<?php

namespace App\Repositories;

use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\Contracts\PlatformUserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class PlatformUserRepository implements PlatformUserRepositoryInterface
{
    public function paginate(?string $search = null): LengthAwarePaginator
    {
        return User::query()
            ->with('tenant')
            ->when($search, function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('document_number', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString();
    }

    public function operationsForTenant(int $tenantId): Collection
    {
        return User::query()
            ->where('tenant_id', $tenantId)
            ->where('role', UserRole::Operaciones)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function emailExists(string $email, ?int $ignoreId = null): bool
    {
        return User::query()
            ->where('email', $email)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists();
    }

    public function documentExists(string $documentNumber, ?int $ignoreId = null): bool
    {
        return User::query()
            ->where('document_number', $documentNumber)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists();
    }
}
