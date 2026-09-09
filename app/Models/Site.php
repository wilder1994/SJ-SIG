<?php

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'contract_id', 'code', 'name', 'city'];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class)->orderBy('name');
    }
}
