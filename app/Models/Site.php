<?php

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'contract_id',
        'code',
        'name',
        'city',
        'address',
        'department',
        'lat',
        'lng',
        'place_id',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'float',
            'lng' => 'float',
        ];
    }

    public function hasCoordinates(): bool
    {
        return $this->lat !== null && $this->lng !== null;
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class)->orderBy('name');
    }
}
