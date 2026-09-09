<?php

namespace App\Models;

use App\Enums\DocumentFolder;
use App\Support\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonDocument extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'person_id',
        'folder',
        'original_name',
        'disk_path',
        'mime',
        'size_bytes',
        'expires_on',
    ];

    protected function casts(): array
    {
        return [
            'folder' => DocumentFolder::class,
            'expires_on' => 'date',
            'size_bytes' => 'integer',
        ];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
