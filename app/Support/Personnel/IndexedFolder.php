<?php

namespace App\Support\Personnel;

use App\Enums\AffiliationDocumentType;
use App\Enums\DocumentFolder;
use App\Enums\LaborHistoryDocumentType;
use InvalidArgumentException;

final class IndexedFolder
{
    public static function isIndexed(DocumentFolder $folder): bool
    {
        return $folder === DocumentFolder::HojaVida || $folder === DocumentFolder::Afiliaciones;
    }

    /** @return list<LaborHistoryDocumentType|AffiliationDocumentType> */
    public static function types(DocumentFolder $folder): array
    {
        return match ($folder) {
            DocumentFolder::HojaVida => LaborHistoryDocumentType::cases(),
            DocumentFolder::Afiliaciones => AffiliationDocumentType::cases(),
            default => [],
        };
    }

    public static function resolve(DocumentFolder $folder, string $value): LaborHistoryDocumentType|AffiliationDocumentType
    {
        $type = match ($folder) {
            DocumentFolder::HojaVida => LaborHistoryDocumentType::tryFrom($value),
            DocumentFolder::Afiliaciones => AffiliationDocumentType::tryFrom($value),
            default => null,
        };

        if ($type === null) {
            throw new InvalidArgumentException('Tipo de documento no válido para '.$folder->label().'.');
        }

        return $type;
    }
}
