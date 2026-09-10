<?php

namespace App\Models;

use App\Enums\GuardRole;
use App\Support\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostStaffing extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'contract_id',
        'post_id',
        'role',
        'slots',
    ];

    protected function casts(): array
    {
        return [
            'role' => GuardRole::class,
            'slots' => 'integer',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
