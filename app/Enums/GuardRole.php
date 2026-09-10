<?php

namespace App\Enums;

enum GuardRole: string
{
    case Vigilante = 'vigilante';
    case Escolta = 'escolta';
    case SupervisorPatrulla = 'supervisor_patrulla';
    case OperadorMedios = 'operador_medios';

    public function label(): string
    {
        return match ($this) {
            self::Vigilante => 'Vigilante',
            self::Escolta => 'Escolta',
            self::SupervisorPatrulla => 'Supervisor de patrulla',
            self::OperadorMedios => 'Operador de medios tecnológicos',
        };
    }

    public function plural(int $count): string
    {
        $word = match ($this) {
            self::Vigilante => $count === 1 ? 'vigilante' : 'vigilantes',
            self::Escolta => $count === 1 ? 'escolta' : 'escoltas',
            self::SupervisorPatrulla => $count === 1 ? 'supervisor de patrulla' : 'supervisores de patrulla',
            self::OperadorMedios => $count === 1 ? 'operador de medios tecnológicos' : 'operadores de medios tecnológicos',
        };

        return $count.' '.$word;
    }
}
