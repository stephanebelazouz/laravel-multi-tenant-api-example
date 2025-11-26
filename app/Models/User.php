<?php

namespace App\Models;

use App\Enums\CentralRole;
use App\Enums\TenantRole;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable;

    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's role as CentralRole enum
     */
    public function getCentralRole(): ?CentralRole
    {
        return CentralRole::tryFrom($this->role);
    }

    /**
     * Get the user's role as TenantRole enum
     */
    public function getTenantRole(): ?TenantRole
    {
        return TenantRole::tryFrom($this->role);
    }

    /**
     * Check if user has a specific permission in Central context
     */
    public function canCentral(string $permission): bool
    {
        $role = $this->getCentralRole();
        return $role ? $role->can($permission) : false;
    }

    /**
     * Check if user has a specific permission in Tenant context
     */
    public function canTenant(string $permission): bool
    {
        $role = $this->getTenantRole();
        return $role ? $role->can($permission) : false;
    }

    /**
     * Check if user is central admin
     */
    public function isCentralAdmin(): bool
    {
        return $this->role === CentralRole::CENTRAL_ADMIN->value;
    }

    /**
     * Check if user is central user
     */
    public function isCentralUser(): bool
    {
        return in_array($this->role, [
            CentralRole::CENTRAL_ADMIN->value,
            CentralRole::CENTRAL_USER->value,
        ]);
    }

    /**
     * Check if user is tenant admin
     */
    public function isTenantAdmin(): bool
    {
        return $this->role === TenantRole::TENANT_ADMIN->value;
    }

    public function isTenantUser(): bool
    {
        return in_array($this->role, [
            TenantRole::TENANT_ADMIN->value,
            TenantRole::TENANT_USER->value,
        ]);
    }

    /**
     * Get all permissions for current user based on context
     */
    public function getPermissions(): array
    {
        // Try central role first
        $centralRole = $this->getCentralRole();
        if ($centralRole) {
            return $centralRole->permissions();
        }

        // Try tenant role
        $tenantRole = $this->getTenantRole();
        if ($tenantRole) {
            return $tenantRole->permissions();
        }

        return [];
    }
}
