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

        // 2. Synth PolySynth fattriangle (desktop: 4 osciladores; mobile limitado via oscCount)
        $this->assertStringContainsString('Tone.PolySynth', $view);
        $this->assertStringContainsString("'fattriangle'", $view);
        $this->assertStringContainsString('count: this.oscCount(4)', $view);
        $this->assertStringContainsString('spread: 50', $view);

        // 3. Envelope
        $this->assertStringContainsString('attack: 2.5', $view);
        $this->assertStringContainsString('decay: 2.0', $view);
        $this->assertStringContainsString('sustain: 0.9', $view);
        $this->assertStringContainsString('release: 6.0', $view);

        // 4. Lowpass Filter
        $this->assertStringContainsString('Tone.Filter', $view);
        $this->assertStringContainsString('frequency: 1200', $view);
        $this->assertStringContainsString('rolloff: this.isLowPower ? -12 : -24', $view);
        $this->assertStringContainsString('Q: 0.5', $view);

        // 5. Modulation & Movement (AutoFilter & Chorus)
        $this->assertStringContainsString('Tone.AutoFilter', $view);
        $this->assertStringContainsString('Tone.Chorus', $view);

        // 6. Espacialidade (PingPongDelay & Reverb otimizado)
        $this->assertStringContainsString('Tone.PingPongDelay', $view);
        $this->assertStringContainsString("'4n'", $view);
        $this->assertStringContainsString('wet: 0.4', $view);
        $this->assertStringContainsString('Tone.Freeverb', $view);
        $this->assertStringContainsString('Tone.Reverb', $view);
        $this->assertStringContainsString('decay: 5', $view);
        $this->assertStringContainsString('wet: 0.7', $view);

        // 7. Volume Master + Limiter
        $this->assertStringContainsString('Tone.Volume(-12)', $view);
        $this->assertStringContainsString('Tone.Limiter(-1)', $view);
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

        // 10. Draggable FAB & Botão de Engrenagem (Settings)
        $this->assertStringContainsString('onPointerDown', $view);
        $this->assertStringContainsString('fabContainerStyle', $view);
        $this->assertStringContainsString('openModal()', $view);
        $this->assertStringContainsString('Configurações do Ambient Pad', $view);

        // 11. Modal Overlay de Configuração Completa & Sintetizador
        $this->assertStringContainsString('isModalOpen', $view);
        $this->assertStringContainsString('visualizerCanvas', $view);
        $this->assertStringContainsString('availableNotes', $view);
        $this->assertStringContainsString('setChordType', $view);
        $this->assertStringContainsString('setTimbre', $view);
        $this->assertStringContainsString('setInversion', $view);
        $this->assertStringContainsString('setOctave', $view);
        $this->assertStringContainsString('setAmbience', $view);
        $this->assertStringContainsString('setMovement', $view);
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

    public function test_ambient_pad_fab_stack_includes_stacked_scroll_play_and_lyrics_l_buttons(): void
    {
        $view = view('components.altar-ambient-pad')->render();

        // Botão PAD presente
        $this->assertStringContainsString('handleMainButtonClick()', $view);

        // Botão Play / Auto-Scroll presente no stack
        $this->assertStringContainsString('handleScrollButtonClick()', $view);
        $this->assertStringContainsString('SCROLL', $view);
        $this->assertStringContainsString('isAutoScrolling', $view);

        // Botão Letra com "L" presente no stack
        $this->assertStringContainsString('handleLyricsButtonClick()', $view);
        $this->assertStringContainsString('LETRA', $view);
        $this->assertStringContainsString('>L</span>', $view);
        $this->assertStringContainsString('showLyricsOnly', $view);
    }

    public function test_ambient_pad_engine_uses_lightweight_audio_profile_on_mobile(): void
    {
        $view = view('components.altar-ambient-pad')->render();

        $this->assertStringContainsString('detectLowPowerDevice', $view);
        $this->assertStringContainsString('lookAhead = 0.25', $view);
        $this->assertStringContainsString('Tone.Freeverb', $view);
        $this->assertStringNotContainsString('Tone.setContext', $view);
        $this->assertStringContainsString('this.synth.maxPolyphony = this.isLowPower ? 8 : 12', $view);
        $this->assertStringContainsString('canvas.width = 320', $view);
        $this->assertStringContainsString('setupAudioRecovery', $view);
        $this->assertStringContainsString('altar-pad-toggle-group', $view);
        $this->assertStringContainsString('altar-pad-timbre-group', $view);
    }

    public function test_ambient_pad_fab_stack_includes_metronome_button_with_text_only_labels(): void
    {
        $view = view('components.altar-ambient-pad')->render();

        $this->assertStringContainsString('handleMetronomeButtonClick()', $view);
        $this->assertStringContainsString('METRÔNOMO', $view);
        $this->assertStringContainsString('cifraly:metronome-status', $view);
        $this->assertStringContainsString("'open-altar-metronome'", $view);

        // Labels do topo sem ícones: apenas a descrição em texto
        $this->assertStringContainsString('>PAD</span>', $view);
        $this->assertStringContainsString('>SCROLL</span>', $view);
        $this->assertStringContainsString('>LETRA</span>', $view);
        $this->assertStringNotContainsString('w-2.5 h-2.5', $view);
    }

    public function test_song_stage_view_moves_metronome_from_header_to_floating_stack(): void
    {
        $this->song->update(['bpm' => 72, 'time_signature' => '6/8']);

        $response = $this->get(route('songs.stage', [
            'organization' => $this->organization,
            'song' => $this->song,
        ]));

        $response->assertSuccessful();
        $response->assertSee('data-bpm="72"', false);
        $response->assertSee('data-time-signature="6/8"', false);
        $response->assertDontSee('Abrir Metrônomo ALTAR', false);
        $response->assertDontSee('openMetronome(', false);
        $response->assertDontSee('toggleFullscreen', false);
        $response->assertDontSee('title="Tela Cheia"', false);
    }
}
