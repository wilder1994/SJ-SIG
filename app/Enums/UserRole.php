<?php

namespace App\Enums;

enum UserRole: string
{
    case AdminEmpresa = 'admin_empresa';
    case Operador = 'operador';
    case TecnicoElectronica = 'tecnico_electronica';
    case SupervisorEntidad = 'supervisor_entidad';
    case ConsultaEntidad = 'consulta_entidad';

    public function label(): string
    {
        return match ($this) {
            self::AdminEmpresa => 'Administración SJ',
            self::Operador => 'Operador',
            self::TecnicoElectronica => 'Técnico electrónica',
            self::SupervisorEntidad => 'Supervisor entidad',
            self::ConsultaEntidad => 'Consulta entidad',
        };
    }

    public function isInternal(): bool
    {
        return in_array($this, [self::AdminEmpresa, self::Operador, self::TecnicoElectronica], true);
    }

    public function isEntity(): bool
    {
        return in_array($this, [self::SupervisorEntidad, self::ConsultaEntidad], true);
    }

    public function canMutateNovelties(): bool
    {
        return in_array($this, [self::AdminEmpresa, self::Operador, self::SupervisorEntidad], true);
    }

    public function canImportPersonnel(): bool
    {
        return in_array($this, [self::AdminEmpresa, self::Operador], true);
    }

    public function canRegisterMaintenance(): bool
    {
        return in_array($this, [self::AdminEmpresa, self::TecnicoElectronica], true);
    }
}
