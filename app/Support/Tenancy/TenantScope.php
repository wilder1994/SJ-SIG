<?php

namespace App\Support\Tenancy;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return;
        }

        if ($user->role->isInternal() && $user->role === \App\Enums\UserRole::AdminEmpresa) {
            return;
        }

        if ($user->tenant_id !== null) {
            $builder->where($model->qualifyColumn('tenant_id'), $user->tenant_id);
        }
    }
}
