<?php

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Person extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'document_type',
        'document_number',
        'full_name',
        'birth_date',
        'document_issue_place',
        'document_issued_on',
        'residence_city',
        'address',
        'phone',
        'email',
        'blood_type',
        'sex',
        'education',
        'marital_status',
        'children_count',
        'engagement_type',
        'contributor_type',
        'hired_on',
        'labor_contract_ends_on',
        'left_on',
        'job_code',
        'labor_contract_type',
        'eps_code',
        'eps_name',
        'afp_code',
        'afp_name',
        'arl_name',
        'arl_risk_level',
        'compensation_fund',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'document_issued_on' => 'date',
            'hired_on' => 'date',
            'labor_contract_ends_on' => 'date',
            'left_on' => 'date',
            'children_count' => 'integer',
        ];
    }

    public function isActive(): bool
    {
        return $this->left_on === null;
    }

    public function contracts(): BelongsToMany
    {
        return $this->belongsToMany(Contract::class, 'contract_person')
            ->withPivot(['post_id', 'tenant_id'])
            ->withTimestamps();
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PersonDocument::class);
    }
}
