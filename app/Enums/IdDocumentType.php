<?php

namespace App\Enums;

enum IdDocumentType: string
{
    case Nit = 'NIT';
    case Cc = 'CC';
    case Ce = 'CE';
    case Pp = 'PP';
    case Ti = 'TI';

    public function label(): string
    {
        return match ($this) {
            self::Nit => 'NIT',
            self::Cc => 'Cédula de ciudadanía',
            self::Ce => 'Cédula de extranjería',
            self::Pp => 'Pasaporte',
            self::Ti => 'Tarjeta de identidad',
        };
    }
}
