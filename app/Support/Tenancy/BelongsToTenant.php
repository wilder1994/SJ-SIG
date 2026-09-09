<?php

namespace App\Support\Tenancy;

use Illuminate\Database\Eloquent\Model;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Model $model): void {
            $user = auth()->user();

            if ($user === null) {
                return;
            }

            if ($model->getAttribute('tenant_id') === null && $user->tenant_id !== null) {
                $model->setAttribute('tenant_id', $user->tenant_id);
            }
        });
    }
}
