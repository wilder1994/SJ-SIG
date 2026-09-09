<?php

namespace App\Models;

use App\Enums\ServiceModality;
use App\Support\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'contract_id',
        'site_id',
        'code',
        'name',
        'city',
        'shift_hours',
        'guard_slots',
    ];

    protected function casts(): array
    {
        return [
            'shift_hours' => ServiceModality::class,
            'guard_slots' => 'integer',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function serviceDeliveries(): HasMany
    {
        return $this->hasMany(ServiceDelivery::class);
    }
}
