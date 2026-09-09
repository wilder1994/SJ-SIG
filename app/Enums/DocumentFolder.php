<?php

namespace App\Enums;

enum DocumentFolder: string
{
    case HojaVida = 'hv';
    case Certificados = 'certificados';
    case Cursos = 'cursos';
    case Afiliaciones = 'afiliaciones';
    case Otros = 'otros';

    public function label(): string
    {
        return match ($this) {
            self::HojaVida => 'Historia Laboral',
            self::Certificados => 'Certificados',
            self::Cursos => 'Cursos y capacitación',
            self::Afiliaciones => 'Afiliaciones',
            self::Otros => 'Otros',
        };
    }

    public function hint(): string
    {
        return match ($this) {
            self::HojaVida => 'Checklist indexado de ingreso y selección. Subir PDF y asignar tipo a cada página.',
            self::Certificados => 'Checklist indexado: examen médico de ingreso, psicofísico y psicosensométrico. Subir PDF y asignar tipo a cada página.',
            self::Cursos => 'Catálogo Superintendencia + otro. Cada acta lleva fecha y entidad que dicta el curso.',
            self::Afiliaciones => 'Afiliaciones que hace la empresa al contratar. Subir PDF y asignar tipo a cada página.',
            self::Otros => 'Cédula, foto, RUT u otro soporte que no cabe arriba.',
        };
    }

    public function isIndexed(): bool
    {
        return $this === self::HojaVida
            || $this === self::Certificados
            || $this === self::Cursos
            || $this === self::Afiliaciones;
    }

    public function batchRoute(): ?string
    {
        return match ($this) {
            self::HojaVida => 'documents.history.batch',
            self::Certificados => 'documents.certificates.batch',
            self::Cursos => 'documents.courses.batch',
            self::Afiliaciones => 'documents.affiliations.batch',
            default => null,
        };
    }

    public function indexRoute(): ?string
    {
        return match ($this) {
            self::HojaVida => 'documents.history.index',
            self::Certificados => 'documents.certificates.index',
            self::Cursos => 'documents.courses.index',
            self::Afiliaciones => 'documents.affiliations.index',
            default => null,
        };
    }

    public function storeIndexRoute(): ?string
    {
        return match ($this) {
            self::HojaVida => 'documents.history.store',
            self::Certificados => 'documents.certificates.store',
            self::Cursos => 'documents.courses.store',
            self::Afiliaciones => 'documents.affiliations.store',
            default => null,
        };
    }

    public function previewRoute(): ?string
    {
        return match ($this) {
            self::HojaVida => 'documents.history.preview',
            self::Certificados => 'documents.certificates.preview',
            self::Cursos => 'documents.courses.preview',
            self::Afiliaciones => 'documents.affiliations.preview',
            default => null,
        };
    }

    public function naRoute(): ?string
    {
        return match ($this) {
            self::HojaVida => 'documents.history.na',
            self::Certificados => 'documents.certificates.na',
            self::Cursos => 'documents.courses.na',
            self::Afiliaciones => 'documents.affiliations.na',
            default => null,
        };
    }
}
