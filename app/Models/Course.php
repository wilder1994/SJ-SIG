<?php

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'person_id',
        'title',
        'provider',
        'course_type',
        'person_document_id',
        'taken_on',
    ];

    protected function casts(): array
    {
        return [
            'taken_on' => 'date',
        ];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(PersonDocument::class, 'person_document_id');
    }
}
