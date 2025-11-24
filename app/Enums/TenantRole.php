<?php

namespace App\Enums;

enum TenantRole: string
{
    case TENANT_ADMIN = 'tenant_admin';
    case TENANT_USER = 'tenant_user';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::TENANT_ADMIN => 'Tenant Administrator',
            self::TENANT_USER => 'Tenant User',
        };
    }

    /**
     * Get all permissions for this role
     */
    public function permissions(): array
    {
        return match ($this) {
            self::TENANT_ADMIN => [
                // User Management
                'tenant.users.create',
                'tenant.users.view',
                'tenant.users.update',
                'tenant.users.delete',

                // Tenant Settings
                'tenant.settings.view',
                'tenant.settings.update',

                // Own Profile
                'profile.view',
                'profile.update',
            ],
            self::TENANT_USER => [
                // View only
                'tenant.users.view',

                // Own Profile
                'profile.view',
                'profile.update',
            ],
        };
    }

    /**
     * Check if role has a specific permission
     */
    public function can(string $permission): bool
    {
        return in_array($permission, $this->permissions());
    }

    /**
     * Get all available roles
     */
    public static function all(): array
    {
        return [
            self::TENANT_ADMIN,
            self::TENANT_USER,
        ];
    }

    /**
     * Get roles as array for validation
     */
    public static function values(): array
    {
        return array_map(fn($role) => $role->value, self::all());
    }
}
