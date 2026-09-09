<?php

namespace App\Models;

use App\Enums\AffiliationDocumentType;
use App\Enums\CertificateDocumentType;
use App\Enums\ContractingDocumentType;
use App\Enums\CourseDocumentType;
use App\Enums\OtherDocumentType;
use App\Enums\DocumentFolder;
use App\Enums\LaborHistoryDocumentType;
use App\Support\Personnel\IndexedFolder;
use App\Support\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonDocument extends Model
{
    use BelongsToTenant;

    public const DELETE_GRACE_HOURS = 12;

    protected $fillable = [
        'tenant_id',
        'person_id',
        'folder',
        'document_type',
        'display_name',
        'page_from',
        'page_to',
        'pages',
        'not_applicable',
        'original_name',
        'disk_path',
        'mime',
        'size_bytes',
        'expires_on',
        'taken_on',
        'provider',
    ];

    protected function casts(): array
    {
        return [
            'folder' => DocumentFolder::class,
            'document_type' => 'string',
            'not_applicable' => 'boolean',
            'page_from' => 'integer',
            'page_to' => 'integer',
            'pages' => 'array',
            'expires_on' => 'date',
            'taken_on' => 'date',
            'size_bytes' => 'integer',
        ];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function typed(): LaborHistoryDocumentType|AffiliationDocumentType|CertificateDocumentType|ContractingDocumentType|CourseDocumentType|OtherDocumentType|null
    {
        if (! is_string($this->document_type) || $this->document_type === '' || $this->folder === null) {
            return null;
        }

        try {
            return IndexedFolder::resolve($this->folder, $this->document_type);
        } catch (\InvalidArgumentException) {
            return null;
        }
    }

    public function label(): string
    {
        return $this->display_name
            ?: $this->typed()?->label()
            ?: $this->original_name;
    }

    public function hasFile(): bool
    {
        return ! $this->not_applicable && is_string($this->disk_path) && $this->disk_path !== '';
    }

    public function scopeWithPdf(Builder $query): void
    {
        $query->where('not_applicable', false)
            ->whereNotNull('disk_path')
            ->where('disk_path', '!=', '');
    }

    public function canDelete(): bool
    {
        if (! $this->hasFile() || $this->created_at === null) {
            return false;
        }

        return $this->created_at->gt(now()->subHours(self::DELETE_GRACE_HOURS));
    }
}
