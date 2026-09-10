<?php

namespace App\Models;

use App\Enums\StructureType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'nit',
        'person_kind',
        'structure_type',
        'trade_name',
        'legal_name',
        'document_type',
        'contact_email',
        'phone',
        'legal_rep_name',
        'legal_rep_email',
        'address',
        'city',
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

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function primaryContract(): HasOne
    {
        return $this->hasOne(Contract::class)->orderBy('id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function displayName(): string
    {
        return (string) ($this->trade_name ?: $this->name);
    }

    public function legalLabel(): ?string
    {
        $legal = trim((string) ($this->legal_name ?: $this->name));
        $display = trim($this->displayName());
        if ($legal === '' || strcasecmp($legal, $display) === 0) {
            return null;
        }

        return $legal;
    }

    public function structureShortLabel(): string
    {
        return StructureType::tryFrom((string) $this->structure_type)?->shortLabel() ?? '—';
    }
}
