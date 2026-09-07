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
            ['email' => 'cardoso.oliveira@gmail.com'],
            [
                'name' => 'Cardoso Oliveira',
                'password' => bcrypt('123qwe'),
            ]
        );

        $user->organizations()->syncWithoutDetaching([$organization->id]);

        DefaultRolesSeeder::seedForOrganization($organization);
    }
}
