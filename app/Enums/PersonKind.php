<?php

namespace App\Enums;

enum PersonKind: string
{
    case Juridica = 'juridica';
    case Natural = 'natural';

    public function label(): string
    {
        return match ($this) {
            self::Juridica => 'Persona jurídica',
            self::Natural => 'Persona natural',
        };
    }
}
