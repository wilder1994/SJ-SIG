<?php

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'contract_id', 'code', 'name', 'city'];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function serviceDeliveries(): HasMany
    {
        return $this->hasMany(ServiceDelivery::class);
    }
}
