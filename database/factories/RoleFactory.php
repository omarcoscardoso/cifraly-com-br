<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'name' => fake()->jobTitle(),
            'category' => fake()->randomElement([
                Role::CATEGORY_MUSICIAN,
                Role::CATEGORY_VOCAL,
                Role::CATEGORY_TECHNICIAN,
            ]),
            'description' => fake()->sentence(),
        ];
    }
}
