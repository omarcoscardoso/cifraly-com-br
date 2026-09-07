<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;

class TeamPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        $tenant = Filament::getTenant();

        return $tenant instanceof Organization && $user->canAccessTenant($tenant);
    }

    public function view(User $user, Team $team): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $team->organization !== null && $user->canAccessTenant($team->organization);
    }

    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        $tenant = Filament::getTenant();

        return $tenant instanceof Organization && $user->canAccessTenant($tenant);
    }

    public function update(User $user, Team $team): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $team->organization !== null && $user->canAccessTenant($team->organization);
    }

    public function delete(User $user, Team $team): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $team->organization !== null && $user->isOrgAdmin($team->organization);
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
