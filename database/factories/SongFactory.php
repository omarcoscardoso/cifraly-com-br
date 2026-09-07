<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Song;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Song>
 */
class SongFactory extends Factory
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
            'title' => fake()->sentence(3),
            'artist' => fake()->name(),
            'original_key' => fake()->randomElement(['C', 'D', 'E', 'F', 'G', 'A', 'B', 'Bb', 'Eb', 'F#']),
            'bpm' => fake()->numberBetween(60, 180),
            'time_signature' => '4/4',
            'spotify_url' => null,
            'youtube_url' => null,
        ];
    }
}
