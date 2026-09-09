<?php

namespace App\Services\Personnel;

use App\Enums\AffiliationDocumentType;
use App\Enums\CertificateDocumentType;
use App\Enums\ContractingDocumentType;
use App\Enums\CourseDocumentType;
use App\Enums\OtherDocumentType;
use App\Enums\DocumentFolder;
use App\Enums\LaborHistoryDocumentType;
use App\Models\Course;
use App\Models\Person;
use App\Models\PersonDocument;

final class MarkLaborHistoryNotApplicableService
{
    public function execute(Person $person, DocumentFolder $folder, LaborHistoryDocumentType|AffiliationDocumentType|CertificateDocumentType|ContractingDocumentType|CourseDocumentType|OtherDocumentType $type): PersonDocument
    {
        if ($type instanceof CourseDocumentType) {
            Course::query()
                ->where('person_id', $person->id)
                ->where('course_type', $type->value)
                ->delete();
        }

        PersonDocument::query()
            ->where('person_id', $person->id)
            ->where('folder', $folder)
            ->where('document_type', $type->value)
            ->delete();

        return PersonDocument::query()->create([
            'tenant_id' => $person->tenant_id,
            'person_id' => $person->id,
            'folder' => $folder,
            'document_type' => $type->value,
            'display_name' => $type->label(),
            'not_applicable' => true,
            'original_name' => $type->label(),
            'disk_path' => '',
            'mime' => null,
            'size_bytes' => 0,
        ]);
    }
}
