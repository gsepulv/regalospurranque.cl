<?php
namespace App\Services;

/**
 * Servicio de permisos ACL
 * Verifica si un rol tiene acceso a un módulo
 */
class Permission
{
    private array $permissions;

    /** Módulos que requieren superadmin explícito */
    private array $superadminOnly = ['sitios'];

    public function __construct()
    {
        $this->permissions = require BASE_PATH . '/config/permissions.php';
    }

    /**
     * Verificar si el rol puede acceder al módulo
     */
    public function can(string $role, string $module): bool
    {
        // Módulos solo para superadmin
        if (in_array($module, $this->superadminOnly, true) && $role !== 'superadmin') {
            return false;
        }

        $allowed = $this->permissions[$role] ?? [];

        // Wildcard: acceso total
        if (in_array('*', $allowed, true)) {
            return true;
        }

        return in_array($module, $allowed, true);
    }

    /**
     * Obtener módulos permitidos para un rol
     */
    public function modules(string $role): array
    {
        $modules = $this->permissions[$role] ?? [];

        if (in_array('*', $modules, true)) {
            return ['*'];
        }

        return $modules;
    }

    /**
     * Verificar si un rol tiene acceso total
     */
    public function isAdmin(string $role): bool
    {
        $allowed = $this->permissions[$role] ?? [];
        return in_array('*', $allowed, true);
    }
}
