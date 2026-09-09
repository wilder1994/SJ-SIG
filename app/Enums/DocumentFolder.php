<?php

namespace App\Enums;

enum DocumentFolder: string
{
    case HojaVida = 'hv';
    case Contratacion = 'contratacion';
    case Certificados = 'certificados';
    case Cursos = 'cursos';
    case Afiliaciones = 'afiliaciones';
    case Otros = 'otros';

    public function label(): string
    {
        return match ($this) {
            self::HojaVida => 'Historia Laboral',
            self::Contratacion => 'Contratación',
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
            self::Contratacion => 'Checklist indexado de vinculación: contrato, ética, inducción, carné y carta de presentación.',
            self::Certificados => 'Checklist indexado: examen médico de ingreso, psicofísico y psicosensométrico. Subir PDF y asignar tipo a cada página.',
            self::Cursos => 'Catálogo Superintendencia + otro. Cada acta lleva fecha y entidad que dicta el curso.',
            self::Afiliaciones => 'Afiliaciones que hace la empresa al contratar. Subir PDF y asignar tipo a cada página.',
            self::Otros => 'Soportes que no caben arriba. Escriba el tipo; máximo 20 por trabajador. No use un tipo de otra carpeta.',
        };
    }

    public function isIndexed(): bool
    {
        return true;
    }

    public function batchRoute(): ?string
    {
        return match ($this) {
            self::HojaVida => 'documents.history.batch',
            self::Contratacion => 'documents.contracting.batch',
            self::Certificados => 'documents.certificates.batch',
            self::Cursos => 'documents.courses.batch',
            self::Afiliaciones => 'documents.affiliations.batch',
            self::Otros => 'documents.others.batch',
        };
    }

    public function indexRoute(): ?string
    {
        return match ($this) {
            self::HojaVida => 'documents.history.index',
            self::Contratacion => 'documents.contracting.index',
            self::Certificados => 'documents.certificates.index',
            self::Cursos => 'documents.courses.index',
            self::Afiliaciones => 'documents.affiliations.index',
            self::Otros => 'documents.others.index',
        };
    }

    public function storeIndexRoute(): ?string
    {
        return match ($this) {
            self::HojaVida => 'documents.history.store',
            self::Contratacion => 'documents.contracting.store',
            self::Certificados => 'documents.certificates.store',
            self::Cursos => 'documents.courses.store',
            self::Afiliaciones => 'documents.affiliations.store',
            self::Otros => 'documents.others.store',
        };
    }

    public function previewRoute(): ?string
    {
        return match ($this) {
            self::HojaVida => 'documents.history.preview',
            self::Contratacion => 'documents.contracting.preview',
            self::Certificados => 'documents.certificates.preview',
            self::Cursos => 'documents.courses.preview',
            self::Afiliaciones => 'documents.affiliations.preview',
            self::Otros => 'documents.others.preview',
        };
    }

    public function naRoute(): ?string
    {
        return match ($this) {
            self::HojaVida => 'documents.history.na',
            self::Contratacion => 'documents.contracting.na',
            self::Certificados => 'documents.certificates.na',
            self::Cursos => 'documents.courses.na',
            self::Afiliaciones => 'documents.affiliations.na',
            self::Otros => null,
        };
    }
}
