<?php

namespace App\Enums;

enum StructureType: string
{
    case Alcaldia = 'alcaldia';
    case Empresa = 'empresa';
    case Conjunto = 'conjunto';
    case Hospital = 'hospital';
    case Educacion = 'educacion';
    case Otro = 'otro';

    public function label(): string
    {
        return match ($this) {
            self::Alcaldia => 'Alcaldía / entidad pública',
            self::Empresa => 'Empresa',
            self::Conjunto => 'Conjunto / copropiedad',
            self::Hospital => 'Hospital / clínica',
            self::Educacion => 'Institución educativa',
            self::Otro => 'Otro',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Alcaldia => 'Alcaldía',
            self::Empresa => 'Empresa',
            self::Conjunto => 'Conjunto',
            self::Hospital => 'Hospital',
            self::Educacion => 'Educación',
            self::Otro => 'Otro',
        };
    }
}
