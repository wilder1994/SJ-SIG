<?php

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Maintenance extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'electronic_asset_id',
        'performed_on',
        'next_due_on',
        'summary',
        'evidence_path',
    ];

    protected function casts(): array
    {
        return [
            'performed_on' => 'date',
            'next_due_on' => 'date',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(ElectronicAsset::class, 'electronic_asset_id');
    }

    public function isOverdue(): bool
    {
        return $this->next_due_on !== null && $this->next_due_on->isPast();
    }

    public function hasEvidence(): bool
    {
        return filled($this->evidence_path);
    }
}
