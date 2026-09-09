<?php

namespace App\Support\Personnel;

use App\Enums\AffiliationDocumentType;
use App\Enums\CertificateDocumentType;
use App\Enums\CourseDocumentType;
use App\Enums\DocumentFolder;
use App\Enums\LaborHistoryDocumentType;
use InvalidArgumentException;

final class IndexedFolder
{
    public static function isIndexed(DocumentFolder $folder): bool
    {
        return $folder->isIndexed();
    }

    /** @return list<LaborHistoryDocumentType|AffiliationDocumentType|CertificateDocumentType|CourseDocumentType> */
    public static function types(DocumentFolder $folder): array
    {
        return match ($folder) {
            DocumentFolder::HojaVida => LaborHistoryDocumentType::cases(),
            DocumentFolder::Afiliaciones => AffiliationDocumentType::cases(),
            DocumentFolder::Certificados => CertificateDocumentType::cases(),
            DocumentFolder::Cursos => CourseDocumentType::cases(),
            default => [],
        };
    }

    public static function resolve(DocumentFolder $folder, string $value): LaborHistoryDocumentType|AffiliationDocumentType|CertificateDocumentType|CourseDocumentType
    {
        $type = match ($folder) {
            DocumentFolder::HojaVida => LaborHistoryDocumentType::tryFrom($value),
            DocumentFolder::Afiliaciones => AffiliationDocumentType::tryFrom($value),
            DocumentFolder::Certificados => CertificateDocumentType::tryFrom($value),
            DocumentFolder::Cursos => CourseDocumentType::tryFrom($value),
            default => null,
        };

        if ($type === null) {
            throw new InvalidArgumentException('Tipo de documento no válido para '.$folder->label().'.');
        }

        return $type;
    }
}
