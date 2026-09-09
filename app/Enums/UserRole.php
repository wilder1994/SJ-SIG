<?php

namespace App\Enums;

enum UserRole: string
{
    case AdminEmpresa = 'admin_empresa';
    case Interno = 'interno';
    case Operaciones = 'operaciones';
    case TecnicoElectronica = 'tecnico_electronica';
    case SupervisorEntidad = 'supervisor_entidad';
    case ConsultaEntidad = 'consulta_entidad';

    public function label(): string
    {
        return match ($this) {
            self::AdminEmpresa => 'Administración',
            self::Interno => 'Usuario interno',
            self::Operaciones => 'Operaciones',
            self::TecnicoElectronica => 'Técnico',
            self::SupervisorEntidad => 'Supervisor de cliente',
            self::ConsultaEntidad => 'Consulta de cliente',
        };
    }

    public function hint(): string
    {
        return match ($this) {
            self::AdminEmpresa => 'Usuarios, clientes y toda la operación.',
            self::Interno => 'Empleado SJ: todos los clientes, carga documental. Sin módulo de usuarios.',
            self::Operaciones => 'Asignado a un cliente. Visible para la entidad. Consulta y seguimiento.',
            self::TecnicoElectronica => 'Solo mantenimientos de electrónica del cliente asignado.',
            self::SupervisorEntidad => 'Usuario del cliente. Solo su universo. Puede registrar novedades.',
            self::ConsultaEntidad => 'Usuario del cliente. Solo consulta, sin novedades.',
        };
    }

    public function seesAllClients(): bool
    {
        return in_array($this, [self::AdminEmpresa, self::Interno], true);
    }

    public function requiresClient(): bool
    {
        return ! $this->seesAllClients();
    }

    public function isListedOnClientOpsTeam(): bool
    {
        return $this === self::Operaciones;
    }

    public function canManageUsers(): bool
    {
        return $this === self::AdminEmpresa;
    }

    public function canManageClients(): bool
    {
        return $this === self::AdminEmpresa;
    }

    public function canUploadEvidence(): bool
    {
        return in_array($this, [self::AdminEmpresa, self::Interno], true);
    }

    public function canImportPersonnel(): bool
    {
        return $this->canUploadEvidence();
    }

    public function canRegisterMaintenance(): bool
    {
        return in_array($this, [self::AdminEmpresa, self::Interno, self::TecnicoElectronica], true);
    }

    public function canMutateNovelties(): bool
    {
        return in_array($this, [
            self::AdminEmpresa,
            self::Interno,
            self::Operaciones,
            self::SupervisorEntidad,
        ], true);
    }

    public function canManageStructure(): bool
    {
        return $this->canUploadEvidence();
    }

    public function canAccessHr(): bool
    {
        return $this !== self::TecnicoElectronica;
    }

    public function canAccessElectronics(): bool
    {
        return in_array($this, [self::AdminEmpresa, self::Interno, self::TecnicoElectronica], true);
    }

    public function canAccessOpsTeam(): bool
    {
        return in_array($this, [
            self::AdminEmpresa,
            self::Interno,
            self::Operaciones,
            self::SupervisorEntidad,
            self::ConsultaEntidad,
        ], true);
    }

    /** @return list<array{key: string, label: string, on: bool}> */
    public function permissionPreview(): array
    {
        return [
            ['key' => 'all_clients', 'label' => 'Todos los clientes', 'on' => $this->seesAllClients()],
            ['key' => 'users', 'label' => 'Usuarios', 'on' => $this->canManageUsers()],
            ['key' => 'clients', 'label' => 'Alta de clientes', 'on' => $this->canManageClients()],
            ['key' => 'structure', 'label' => 'Instalaciones y puestos', 'on' => $this->canManageStructure()],
            ['key' => 'upload', 'label' => 'Carga PDF / Excel', 'on' => $this->canUploadEvidence()],
            ['key' => 'docs', 'label' => 'Ver / descargar documentos', 'on' => $this->canAccessHr()],
            ['key' => 'novelties', 'label' => 'Novedades', 'on' => $this->canMutateNovelties()],
            ['key' => 'maint', 'label' => 'Mantenimientos', 'on' => $this->canRegisterMaintenance()],
        ];
    }
}
