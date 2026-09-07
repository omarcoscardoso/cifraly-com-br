<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Event;
use App\Models\Organization;
use App\Models\User;
use Filament\Facades\Filament;

class EventPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        $tenant = Filament::getTenant();

        return $tenant instanceof Organization && $user->canAccessTenant($tenant);
    }

    public function view(User $user, Event $event): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $event->organization !== null && $user->canAccessTenant($event->organization);
    }

    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        $tenant = Filament::getTenant();

        return $tenant instanceof Organization && $user->canAccessTenant($tenant);
    }

    public function update(User $user, Event $event): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $event->organization !== null && $user->canAccessTenant($event->organization);
    }

    public function delete(User $user, Event $event): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $event->organization !== null && $user->isOrgAdmin($event->organization);
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
