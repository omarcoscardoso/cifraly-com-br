<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('+1 day', '+1 month');

        return [
            'organization_id' => Organization::factory(),
            'team_id' => null,
            'title' => fake()->sentence(3),
            'status' => fake()->randomElement([
                Event::STATUS_DRAFT,
                Event::STATUS_PUBLISHED,
                Event::STATUS_COMPLETED,
            ]),
            'starts_at' => $startsAt,
            'rehearsal_at' => (clone $startsAt)->modify('-2 hours'),
            'notes' => fake()->optional()->paragraph(),
        ];
    }
}
