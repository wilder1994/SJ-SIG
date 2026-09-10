<?php

namespace App\Enums;

enum SiteServiceEventKind: string
{
    case Inicio = 'inicio';
    case Cambio = 'cambio';

    public function label(): string
    {
        return match ($this) {
            self::Inicio => 'Inicio de servicio',
            self::Cambio => 'Cambio de unidades',
        };
    }
}
