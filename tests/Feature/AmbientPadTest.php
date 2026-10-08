<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventSong;
use App\Models\Organization;
use App\Models\Song;
use App\Models\SongVersion;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AmbientPadTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Organization $organization;

    private Song $song;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['name' => 'Worship Leader']);
        $this->organization = Organization::factory()->create(['slug' => 'pad-church']);
        $this->user->organizations()->attach($this->organization, ['role' => Organization::ROLE_ADMIN]);

        $this->song = Song::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Oceanos',
            'artist' => 'Hillsong',
            'original_key' => 'D',
        ]);

        SongVersion::factory()->create([
            'song_id' => $this->song->id,
            'base_key' => 'D',
            'chordpro_content' => '[D]Tua graça me [A]basta',
            'is_default' => true,
        ]);

        $this->actingAs($this->user);
        Filament::setCurrentPanel(Filament::getPanel('app'));
        Filament::setTenant($this->organization, isQuiet: true);
    }

    public function test_tone_js_bundle_exists_and_is_precached_in_service_worker(): void
    {
        $tonePath = public_path('js/tone.js');
        $this->assertFileExists($tonePath);
        $this->assertGreaterThan(100000, filesize($tonePath));

        $swContent = (string) file_get_contents(public_path('sw.js'));
        $this->assertStringContainsString('/js/tone.js', $swContent);
    }

    public function test_ambient_pad_component_contains_exact_audio_chain_and_crossfade_engine(): void
    {
        $view = view('components.altar-ambient-pad')->render();

        // 1. Engine e classe Tone.js
        $this->assertStringContainsString('class AmbientPadEngine', $view);
        $this->assertStringContainsString('altarAmbientPad', $view);

        // 2. Synth PolySynth fattriangle
        $this->assertStringContainsString('Tone.PolySynth', $view);
        $this->assertStringContainsString("'fattriangle'", $view);
        $this->assertStringContainsString('count: 4', $view);
        $this->assertStringContainsString('spread: 50', $view);

        // 3. Envelope
        $this->assertStringContainsString('attack: 2.5', $view);
        $this->assertStringContainsString('decay: 2.0', $view);
        $this->assertStringContainsString('sustain: 0.9', $view);
        $this->assertStringContainsString('release: 6.0', $view);

        // 4. Lowpass Filter
        $this->assertStringContainsString('Tone.Filter', $view);
        $this->assertStringContainsString('frequency: 1200', $view);
        $this->assertStringContainsString('rolloff: -24', $view);
        $this->assertStringContainsString('Q: 0.5', $view);

        // 5. Modulation & Movement (AutoFilter & Chorus)
        $this->assertStringContainsString('Tone.AutoFilter', $view);
        $this->assertStringContainsString('Tone.Chorus', $view);

        // 6. Espacialidade (PingPongDelay & Reverb 12s)
        $this->assertStringContainsString('Tone.PingPongDelay', $view);
        $this->assertStringContainsString("'4n'", $view);
        $this->assertStringContainsString('wet: 0.4', $view);
        $this->assertStringContainsString('Tone.Reverb', $view);
        $this->assertStringContainsString('decay: 12', $view);
        $this->assertStringContainsString('wet: 0.7', $view);

        // 7. Volume Master
        $this->assertStringContainsString('Tone.Volume(-12)', $view);
        $this->assertStringContainsString('Tone.Destination', $view);

        // 8. Crossfade simultâneo
        $this->assertStringContainsString('crossfadeToKey', $view);
        $this->assertStringContainsString('triggerRelease(oldNotes)', $view);
        $this->assertStringContainsString('triggerAttack(newNotes)', $view);

        // 9. Floating Action Button UI (Square rounded, multicolor/gradient, mobile)
        $this->assertStringContainsString('togglePad', $view);
        $this->assertStringContainsString('fixed bottom-28 sm:bottom-24 right-4 sm:right-6', $view);
        $this->assertStringContainsString('w-14 h-14 sm:w-16 sm:h-16', $view);
        $this->assertStringContainsString('rounded-2xl', $view);
        $this->assertStringContainsString('bg-gradient-to-br from-fuchsia-600 via-indigo-600 to-cyan-500', $view);
        $this->assertStringContainsString('animate-pulse', $view);
    }

    public function test_song_stage_view_renders_ambient_pad_and_key_detector(): void
    {
        $response = $this->get(route('songs.stage', [
            'organization' => $this->organization,
            'song' => $this->song,
        ]));

        $response->assertSuccessful();
        $response->assertSee('/js/tone.js', false);
        $response->assertSee('id="stage-current-key"', false);
        $response->assertSee('data-key="D"', false);
        $response->assertSee('altarAmbientPad()', false);
        $response->assertSee('PAD', false);
    }

    public function test_event_setlist_stage_view_renders_ambient_pad_and_key_detector(): void
    {
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Culto com Pad',
        ]);

        EventSong::factory()->create([
            'organization_id' => $this->organization->id,
            'event_id' => $event->id,
            'song_id' => $this->song->id,
            'target_key' => 'G',
            'order_index' => 1,
        ]);

        $response = $this->get("/app/{$this->organization->slug}/events/{$event->id}/stage");

        $response->assertSuccessful();
        $response->assertSee('/js/tone.js', false);
        $response->assertSee('id="stage-current-key"', false);
        $response->assertSee('data-key="G"', false);
        $response->assertSee('altarAmbientPad()', false);
        $response->assertSee('PAD', false);
    }
}
