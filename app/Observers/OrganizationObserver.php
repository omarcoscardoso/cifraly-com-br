<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Organization;
use Database\Seeders\DefaultRolesSeeder;

class OrganizationObserver
{
    /**
     * Handle the Organization "creating" event.
     */
    public function creating(Organization $organization): void
    {
        if (! $organization->invite_code) {
            $organization->invite_code = Organization::generateUniqueInviteCode();
        }
    }

    /**
     * Handle the Organization "created" event.
     */
    public function created(Organization $organization): void
    {
        DefaultRolesSeeder::seedForOrganization($organization);
    }
}
