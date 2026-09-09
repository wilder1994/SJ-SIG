<?php

namespace App\Support\Personnel;

use App\Enums\AffiliationDocumentType;
use App\Enums\DocumentFolder;
use App\Enums\DocumentRequirement;
use App\Enums\LaborHistoryDocumentType;
use App\Models\Person;
use App\Models\PersonDocument;

final class FolderChecklist
{
    /** @return list<array{type: LaborHistoryDocumentType|AffiliationDocumentType, document: ?PersonDocument, status: string}> */
    public static function for(Person $person, DocumentFolder $folder): array
    {
        $files = $person->documents
            ->filter(fn (PersonDocument $doc) => $doc->folder === $folder)
            ->values();

        $rows = [];
        foreach (IndexedFolder::types($folder) as $type) {
            $match = $files->first(fn (PersonDocument $doc) => $doc->document_type === $type->value);

            if ($match === null) {
                $match = self::legacyMatch($folder, $type, $files);
            }

            $status = 'missing';
            if ($match?->not_applicable) {
                $status = 'na';
            } elseif ($match?->hasFile()) {
                $status = 'loaded';
            }

            $rows[] = [
                'type' => $type,
                'document' => $match,
                'status' => $status,
            ];
        }

        return $rows;
    }

    /** @return array{total: int, loaded: int, required: int, required_loaded: int} */
    public static function summary(Person $person, DocumentFolder $folder): array
    {
        $rows = self::for($person, $folder);
        $loaded = count(array_filter($rows, fn (array $row) => $row['status'] === 'loaded'));
        $required = count(array_filter($rows, fn (array $row) => $row['type']->requirement() === DocumentRequirement::Required));
        $requiredLoaded = count(array_filter(
            $rows,
            fn (array $row) => $row['type']->requirement() === DocumentRequirement::Required && $row['status'] === 'loaded',
        ));

        return [
            'total' => count($rows),
            'loaded' => $loaded,
            'required' => $required,
            'required_loaded' => $requiredLoaded,
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, PersonDocument>  $files
     */
    private static function legacyMatch(DocumentFolder $folder, LaborHistoryDocumentType|AffiliationDocumentType $type, $files): ?PersonDocument
    {
        if ($folder === DocumentFolder::HojaVida && $type === LaborHistoryDocumentType::HojaVida) {
            return $files->first(fn (PersonDocument $doc) => $doc->document_type === null && $doc->hasFile());
        }

        if ($folder !== DocumentFolder::Afiliaciones || ! $type instanceof AffiliationDocumentType) {
            return null;
        }

        $needle = match ($type) {
            AffiliationDocumentType::Estado => 'estado',
            AffiliationDocumentType::Eps => 'eps',
            AffiliationDocumentType::Cesantias => 'cesant',
            AffiliationDocumentType::Afp => 'pension',
            AffiliationDocumentType::Arl => 'arl',
            AffiliationDocumentType::Caja => 'caja',
            AffiliationDocumentType::SeguroVida => 'vida',
            AffiliationDocumentType::Exequial => 'exequial',
        };

        return $files->first(function (PersonDocument $doc) use ($needle): bool {
            return $doc->document_type === null
                && $doc->hasFile()
                && str_contains(strtolower($doc->original_name), $needle);
        });
    }
}
