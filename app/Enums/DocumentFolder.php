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

    public function hint(): string
    {
        return match ($this) {
            self::HojaVida => 'Hoja de vida del vigilante (PDF).',
            self::Certificados => 'Aptitud, armas, escolta, policía u otros certificados de idoneidad.',
            self::Cursos => 'Acta o diploma del curso (el título y la fecha van en la tabla Cursos).',
            self::Afiliaciones => 'Certificado PDF de EPS, pensión y caja. Es lo que consulta la entidad.',
            self::Otros => 'Cédula, foto, RUT u otro soporte que no cabe arriba.',
        };
    }
}
