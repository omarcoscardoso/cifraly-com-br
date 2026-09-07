<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        $tenant = Filament::getTenant();

        return $tenant instanceof Organization && $user->canAccessTenant($tenant);
    }

    public function view(User $user, Role $role): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $role->organization !== null && $user->canAccessTenant($role->organization);
    }

    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        $tenant = Filament::getTenant();

        return $tenant instanceof Organization && $user->canAccessTenant($tenant);
    }

    public function update(User $user, Role $role): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $role->organization !== null && $user->canAccessTenant($role->organization);
    }

    public function delete(User $user, Role $role): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $role->organization !== null && $user->isOrgAdmin($role->organization);
    }

    public function deleteAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        $tenant = Filament::getTenant();

        return $tenant instanceof Organization && $user->isOrgAdmin($tenant);
    }
}
