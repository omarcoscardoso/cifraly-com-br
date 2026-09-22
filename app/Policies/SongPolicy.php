<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Organization;
use App\Models\Song;
use App\Models\User;
use Filament\Facades\Filament;

class SongPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        $tenant = Filament::getTenant();

        return $tenant instanceof Organization && $user->canAccessTenant($tenant);
    }

    public function view(User $user, Song $song): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $song->organization !== null && $user->canAccessTenant($song->organization);
    }

    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        $tenant = Filament::getTenant();

        return $tenant instanceof Organization && $user->canAccessTenant($tenant);
    }

    public function update(User $user, Song $song): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $song->organization !== null && $user->canAccessTenant($song->organization);
    }

    public function delete(User $user, Song $song): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $song->organization !== null && $user->isOrgAdmin($song->organization);
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
