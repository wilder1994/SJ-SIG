<?php

namespace App\Support\Personnel;

use App\Enums\DocumentFolder;
use App\Enums\DocumentRequirement;
use App\Enums\LaborHistoryDocumentType;
use App\Models\Person;
use App\Models\PersonDocument;

final class LaborHistoryChecklist
{
    /** @return list<array{type: LaborHistoryDocumentType, document: ?PersonDocument, status: string}> */
    public static function for(Person $person): array
    {
        $files = $person->documents
            ->filter(fn (PersonDocument $doc) => $doc->folder === DocumentFolder::HojaVida)
            ->values();

        $rows = [];
        foreach (LaborHistoryDocumentType::cases() as $type) {
            $match = $files->first(function (PersonDocument $doc) use ($type): bool {
                return $doc->document_type === $type;
            });

            if ($match === null && $type === LaborHistoryDocumentType::HojaVida) {
                $match = $files->first(fn (PersonDocument $doc) => $doc->document_type === null && $doc->hasFile());
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
    public static function summary(Person $person): array
    {
        $rows = self::for($person);
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
}
