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
            self::HojaVida => 'Hoja de vida',
            self::Certificados => 'Certificados',
            self::Cursos => 'Cursos',
            self::Afiliaciones => 'Afiliaciones',
            self::Otros => 'Otros',
        };
    }
}
