<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeamMember>
 */
class TeamMemberFactory extends Factory
{
    protected $model = TeamMember::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'team_id' => Team::factory(),
            'user_id' => User::factory(),
            'default_role_id' => null,
        ];
    }
}
