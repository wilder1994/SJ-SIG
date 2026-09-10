<?php

namespace App\Models;

use App\Enums\GuardRole;
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

    public function serviceEvents(): HasMany
    {
        return $this->hasMany(SiteServiceEvent::class)->orderByDesc('effective_on')->orderByDesc('id');
    }

    public function unitsCount(): int
    {
        $total = 0;
        foreach ($this->posts as $post) {
            if ($post->relationLoaded('staffings') && $post->staffings->isNotEmpty()) {
                $total += (int) $post->staffings->sum('slots');

                continue;
            }

            $total += (int) $post->guard_slots;
        }

        return $total;
    }

    public function unitsLabel(): string
    {
        $totals = [];
        foreach ($this->posts as $post) {
            if ($post->relationLoaded('staffings') && $post->staffings->isNotEmpty()) {
                foreach ($post->staffings as $row) {
                    $key = $row->role->value;
                    $totals[$key] = ($totals[$key] ?? 0) + $row->slots;
                }

                continue;
            }

            $totals[GuardRole::Vigilante->value] = ($totals[GuardRole::Vigilante->value] ?? 0) + (int) $post->guard_slots;
        }

        if ($totals === []) {
            return 'Sin unidades';
        }

        return collect($totals)
            ->map(fn (int $slots, string $role) => GuardRole::from($role)->plural($slots))
            ->implode(' · ');
    }

    public function postsLabel(): string
    {
        if ($this->posts->isEmpty()) {
            return 'Sin puestos';
        }

        return $this->posts->map(fn (Post $post) => $post->name)->implode(', ');
    }
}
