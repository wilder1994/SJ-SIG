<?php

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'code', 'name', 'starts_on', 'ends_on'];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class)->orderBy('name');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function people(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'contract_person')
            ->withPivot(['post_id', 'tenant_id'])
            ->withTimestamps();
    }

    public function novelties(): HasMany
    {
        return $this->hasMany(Novelty::class);
    }
}
