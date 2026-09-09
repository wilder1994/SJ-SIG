<?php

namespace App\Models;

use App\Enums\ElectronicAssetKind;
use App\Support\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ElectronicAsset extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'contract_id', 'post_id', 'kind', 'name', 'code'];

    protected function casts(): array
    {
        return [
            'kind' => ElectronicAssetKind::class,
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }
}
