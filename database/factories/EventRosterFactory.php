<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventRoster;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<EventRoster>
 */
class EventRosterFactory extends Factory
{
    protected $model = EventRoster::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'event_id' => Event::factory(),
            'user_id' => User::factory(),
            'role_id' => Role::factory(),
            'status' => EventRoster::STATUS_PENDING,
            'confirmation_token' => Str::random(40),
            'responded_at' => null,
            'decline_reason' => null,
        ];
    }
}
