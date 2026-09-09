<?php

namespace App\Models;

use App\Enums\NoveltyStatus;
use App\Support\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Novelty extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'contract_id',
        'post_id',
        'opened_by',
        'title',
        'body',
        'status',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => NoveltyStatus::class,
            'closed_at' => 'datetime',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }
}
