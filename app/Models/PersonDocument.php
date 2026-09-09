<?php

namespace App\Models;

use App\Enums\DocumentFolder;
use App\Enums\LaborHistoryDocumentType;
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
        'document_type',
        'display_name',
        'page_from',
        'page_to',
        'not_applicable',
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
            'document_type' => LaborHistoryDocumentType::class,
            'not_applicable' => 'boolean',
            'page_from' => 'integer',
            'page_to' => 'integer',
            'expires_on' => 'date',
            'size_bytes' => 'integer',
        ];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function label(): string
    {
        return $this->display_name
            ?: $this->document_type?->label()
            ?: $this->original_name;
    }

    public function hasFile(): bool
    {
        return ! $this->not_applicable && is_string($this->disk_path) && $this->disk_path !== '';
    }
}
