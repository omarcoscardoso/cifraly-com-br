<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Song;
use App\Models\SongVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SongVersion>
 */
class SongVersionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'song_id' => Song::factory(),
            'label' => 'Padrão',
            'base_key' => 'C',
            'chordpro_content' => "[Intro]\n[C] [G] [Am] [F]\n\n[C]Graça soberana [G]que me alcançou\n[Am]Mesmo indigno [F]me resgatou",
            'is_default' => true,
        ];
    }
}
