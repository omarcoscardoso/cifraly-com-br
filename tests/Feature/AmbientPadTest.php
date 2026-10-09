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

    public function test_pad_audio_samples_exist_and_are_cached_in_service_worker(): void
    {
        $cMajorPad = public_path('pads/soft_over/soft_over_C.ogg');
        $aMinorPad = public_path('pads/soft_over/soft_over_Am.ogg');
        $this->assertFileExists($cMajorPad);
        $this->assertFileExists($aMinorPad);
        $this->assertGreaterThan(1000000, filesize($cMajorPad));
        $this->assertFileDoesNotExist(public_path('js/tone.js'));

        $swContent = (string) file_get_contents(public_path('sw.js'));
        $this->assertStringContainsString('PADS_CACHE', $swContent);
        $this->assertStringContainsString('/pads/soft_over/soft_over_C.ogg', $swContent);
        $this->assertStringContainsString('PRELOAD_PADS', $swContent);
        $this->assertStringNotContainsString('/js/tone.js', $swContent);
    }

    public function test_ambient_pad_component_contains_exact_audio_chain_and_crossfade_engine(): void
    {
        $view = view('components.altar-ambient-pad')->render();

        // 1. Engine e classe nativa
        $this->assertStringContainsString('class AmbientPadEngine', $view);
        $this->assertStringContainsString('altarAmbientPad', $view);
        $this->assertStringContainsString('PAD_FILES', $view);

        // 2. Dual-deck streaming via HTMLAudioElement e Web Audio API
        $this->assertStringContainsString('this.deckA = { audio: new Audio()', $view);
        $this->assertStringContainsString('this.deckB = { audio: new Audio()', $view);
        $this->assertStringContainsString('createMediaElementSource', $view);
        $this->assertStringContainsString('createGain', $view);

        // 3. Lowpass Filter e Brilho
        $this->assertStringContainsString('createBiquadFilter', $view);
        $this->assertStringContainsString("type = 'lowpass'", $view);
        $this->assertStringContainsString('setBrightness', $view);

        // 4. Analisador de Áudio & Visualizador
        $this->assertStringContainsString('createAnalyser', $view);
        $this->assertStringContainsString('getAnalyserData', $view);
        $this->assertStringContainsString('visualizerCanvas', $view);

        // 5. Volume Master e Crossfade simultâneo
        $this->assertStringContainsString('masterGain', $view);
        $this->assertStringContainsString('crossfadeToKey', $view);
        $this->assertStringContainsString('linearRampToValueAtTime', $view);

        // 6. Floating Action Button UI (Square rounded, multicolor/gradient, mobile)
        $this->assertStringContainsString('togglePad', $view);
        $this->assertStringContainsString('fixed bottom-28 sm:bottom-24 right-4 sm:right-6', $view);
        $this->assertStringContainsString('w-14 h-14 sm:w-16 sm:h-16', $view);
        $this->assertStringContainsString('rounded-2xl', $view);
        $this->assertStringContainsString('bg-gradient-to-br from-fuchsia-600 via-indigo-600 to-cyan-500', $view);
        $this->assertStringContainsString('animate-pulse', $view);

        // 7. Draggable FAB & Botão de Engrenagem (Settings)
        $this->assertStringContainsString('onPointerDown', $view);
        $this->assertStringContainsString('fabContainerStyle', $view);
        $this->assertStringContainsString('openModal()', $view);
        $this->assertStringContainsString('Configurações do Ambient Pad', $view);

        // 8. Modal Overlay & Controles de Tom / PWA Offline
        $this->assertStringContainsString('isModalOpen', $view);
        $this->assertStringContainsString('availableNotes', $view);
        $this->assertStringContainsString('setChordType', $view);
        $this->assertStringContainsString('preloadAllPads', $view);
        $this->assertStringContainsString('checkOfflinePadsCount', $view);
    }

    public function test_song_stage_view_renders_ambient_pad_and_key_detector(): void
    {
        $response = $this->get(route('songs.stage', [
            'organization' => $this->organization,
            'song' => $this->song,
        ]));

        $response->assertSuccessful();
        $response->assertDontSee('/js/tone.js', false);
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
        $response->assertDontSee('/js/tone.js', false);
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
        $this->assertStringContainsString('this.isLowPower ? 64 : 128', $view);
        $this->assertStringNotContainsString('Tone.PolySynth', $view);
        $this->assertStringNotContainsString('Tone.setContext', $view);
        $this->assertStringContainsString('canvas.width = 320', $view);
        $this->assertStringContainsString('altar-pad-toggle-group', $view);
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
