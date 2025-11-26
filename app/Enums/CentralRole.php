<?php

namespace App\Enums;

enum CentralRole: string
{
    case CENTRAL_ADMIN = 'central_admin';
    case CENTRAL_USER = 'central_user';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::CENTRAL_ADMIN => 'Central Administrator',
            self::CENTRAL_USER => 'Central User',
        };
    }

    /**
     * Get all permissions for this role
     */
    public function permissions(): array
    {
        return match ($this) {
            self::CENTRAL_ADMIN => [
                // Tenant Management
                'central.tenants.create',
                'central.tenants.view',
                'central.tenants.update',
                'central.tenants.delete',

                // Central User Management
                'central.users.create',
                'central.users.view',
                'central.users.update',
                'central.users.delete',

                // Can create first user in tenants
                'tenant.users.create',
            ],
            self::CENTRAL_USER => [
                // Read-only access
                'central.tenants.view',
                'central.users.view',
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
            self::CENTRAL_ADMIN,
            self::CENTRAL_USER,
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
