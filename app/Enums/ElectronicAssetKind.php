<?php

namespace App\Enums;

enum ElectronicAssetKind: string
{
    case Cctv = 'cctv';
    case Alarm = 'alarm';
    case Access = 'access';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Cctv => 'CCTV',
            self::Alarm => 'Alarma',
            self::Access => 'Control de acceso',
            self::Other => 'Otro',
        };
    }
}
