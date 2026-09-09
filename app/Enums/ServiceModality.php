<?php

namespace App\Enums;

enum ServiceModality: int
{
    case Hours8 = 8;
    case Hours12 = 12;
    case Hours24 = 24;

    public function label(): string
    {
        return $this->value.' horas';
    }
}
