<?php

namespace App\Models;

use App\Enums\SiteServiceEventKind;
use App\Support\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteServiceEvent extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'contract_id',
        'site_id',
        'kind',
        'effective_on',
        'last_shift_on',
        'requested_by',
        'reason',
        'from_summary',
        'to_summary',
        'snapshot',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'kind' => SiteServiceEventKind::class,
            'effective_on' => 'date',
            'last_shift_on' => 'date',
            'snapshot' => 'array',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopesLabel(): ?string
    {
        $scopes = $this->snapshot['scopes'] ?? [];
        if (! is_array($scopes) || $scopes === []) {
            return null;
        }

        $labels = [
            'instalacion' => 'instalación',
            'puesto' => 'puesto',
            'personal' => 'personal',
        ];
        $words = array_values(array_filter(array_map(
            fn (mixed $scope) => $labels[(string) $scope] ?? null,
            $scopes,
        )));

        if ($words === []) {
            return null;
        }
        if (count($words) === 1) {
            return 'Cambio en '.$words[0];
        }
        if (count($words) === 2) {
            return 'Cambio en '.$words[0].' y '.$words[1];
        }

        return 'Cambio en '.$words[0].', '.$words[1].' y '.$words[2];
    }
}
