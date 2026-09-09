<?php

namespace App\Services\Personnel;

use App\Enums\DocumentFolder;
use App\Enums\LaborHistoryDocumentType;
use App\Models\Person;
use App\Models\PersonDocument;

final class MarkLaborHistoryNotApplicableService
{
    public function execute(Person $person, LaborHistoryDocumentType $type): PersonDocument
    {
        PersonDocument::query()
            ->where('person_id', $person->id)
            ->where('folder', DocumentFolder::HojaVida)
            ->where('document_type', $type)
            ->delete();

        return PersonDocument::query()->create([
            'tenant_id' => $person->tenant_id,
            'person_id' => $person->id,
            'folder' => DocumentFolder::HojaVida,
            'document_type' => $type,
            'display_name' => $type->label(),
            'not_applicable' => true,
            'original_name' => $type->label(),
            'disk_path' => '',
            'mime' => null,
            'size_bytes' => 0,
        ]);
    }
}
