<?php

namespace App\Support\Personnel;

use App\Enums\DocumentFolder;
use App\Enums\LaborHistoryDocumentType;
use App\Models\Person;
use App\Models\PersonDocument;

final class LaborHistoryChecklist
{
    /** @return list<array{type: LaborHistoryDocumentType, document: ?PersonDocument, status: string}> */
    public static function for(Person $person): array
    {
        return FolderChecklist::for($person, DocumentFolder::HojaVida);
    }

    /** @return array{total: int, loaded: int, required: int, required_loaded: int} */
    public static function summary(Person $person): array
    {
        return FolderChecklist::summary($person, DocumentFolder::HojaVida);
    }
}
