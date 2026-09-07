<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventSong;
use App\Models\Organization;
use App\Models\Song;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventSong>
 */
class EventSongFactory extends Factory
{
    protected $model = EventSong::class;

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
            'song_id' => Song::factory(),
            'song_version_id' => null,
            'target_key' => 'C',
            'order_index' => 1,
            'arrangement_notes' => fake()->optional()->sentence(),
        ];
    }
}
