<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $organization = Organization::firstOrCreate(
            ['slug' => 'igreja-central'],
            ['name' => 'Igreja Central']
        );

        $user = User::firstOrCreate(
            ['email' => env('SEEDER_ADMIN_EMAIL', 'admin@cifraly.local')],
            [
                'name' => env('SEEDER_ADMIN_NAME', 'Administrador Local'),
                'password' => bcrypt(env('SEEDER_ADMIN_PASSWORD', 'password')),
                'is_super_admin' => true,
            ]
        );

        $user->organizations()->syncWithoutDetaching([
            $organization->id => ['role' => Organization::ROLE_ADMIN],
        ]);

        DefaultRolesSeeder::seedForOrganization($organization);
    }
}
