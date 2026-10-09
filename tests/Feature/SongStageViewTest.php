<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Widgets\RecentSongsWidget;
use App\Livewire\Stage\SongStageView;
use App\Models\Organization;
use App\Models\Song;
use App\Models\SongVersion;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SongStageViewTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Organization $organization;

    private Song $song;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['name' => 'Levita Marcos']);
        $this->organization = Organization::factory()->create([
            'name' => 'Igreja Presbiteriana',
            'slug' => 'iprv',
        ]);
        $this->user->organizations()->attach($this->organization, ['role' => Organization::ROLE_ADMIN]);

        $this->song = Song::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Porque Ele Vive',
            'artist' => 'Harpa Cristã',
            'original_key' => 'G',
            'bpm' => 75,
        ]);

        SongVersion::factory()->create([
            'song_id' => $this->song->id,
            'base_key' => 'G',
            'chordpro_content' => "[G]Deus enviou seu [C]Filho amado\n[G]Para salvar e [D]perdoar\n\n[Refrão]\n[G]Porque Ele vive, [C]posso crer no amanhã",
            'is_default' => true,
        ]);

        $this->actingAs($this->user);
        Filament::setCurrentPanel(Filament::getPanel('app'));
        Filament::setTenant($this->organization, isQuiet: true);
    }

    protected function tearDown(): void
    {
        Filament::setTenant(null);

        parent::tearDown();
    }

    public function test_user_can_access_simple_song_stage_view(): void
    {
        $response = $this->get(route('songs.stage', [
            'organization' => $this->organization,
            'song' => $this->song,
        ]));

        $response->assertSuccessful();
        $response->assertSee('Porque Ele Vive');
        $response->assertSee('Harpa Cristã');
        $response->assertSee('75');
        $response->assertDontSee('75 BPM');
    }

    public function test_song_stage_view_transposes_key(): void
    {
        Livewire::test(SongStageView::class, [
            'organization' => $this->organization,
            'song' => $this->song,
        ])
            ->assertSuccessful()
            ->assertSet('currentKey', 'G')
            ->call('transposeUp')
            ->assertSet('currentKey', 'G#')
            ->call('transposeDown')
            ->assertSet('currentKey', 'G')
            ->call('transposeDown')
            ->assertSet('currentKey', 'F#')
            ->call('resetKey')
            ->assertSet('currentKey', 'G');
    }

    public function test_unauthorized_user_cannot_access_song_stage_view(): void
    {
        $otherOrg = Organization::factory()->create(['slug' => 'outra-igreja']);
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);

        $response = $this->get(route('songs.stage', [
            'organization' => $this->organization,
            'song' => $this->song,
        ]));

        $response->assertForbidden();
    }

    public function test_recent_songs_widget_has_stage_record_url(): void
    {
        $expectedUrl = route('songs.stage', [
            'organization' => $this->organization,
            'song' => $this->song,
        ]);

        Livewire::test(RecentSongsWidget::class)
            ->assertSuccessful()
            ->assertSee('Porque Ele Vive');
    }

    public function test_song_stage_view_toggles_two_columns_and_lyrics_only(): void
    {
        Livewire::test(SongStageView::class, [
            'organization' => $this->organization,
            'song' => $this->song,
        ])
            ->assertSuccessful()
            ->assertSet('twoColumns', false)
            ->call('toggleTwoColumns')
            ->assertSet('twoColumns', true)
            ->call('toggleTwoColumns')
            ->assertSet('twoColumns', false)
            ->assertSet('showLyricsOnly', false)
            ->call('toggleLyricsOnly')
            ->assertSet('showLyricsOnly', true);
    }

    public function test_song_stage_view_renders_harmonized_toolbar_and_return_button(): void
    {
        $response = $this->get(route('songs.stage', [
            'organization' => $this->organization,
            'song' => $this->song,
        ]));

        $response->assertSuccessful();
        $response->assertSee('Repertório');
        $response->assertDontSee('Avulsa');
        $response->assertSee('Tela Ativa');
        $response->assertSee('2 Colunas');
        $response->assertSee('Letra');
        $response->assertDontSee('Saltar imediatamente para o Refrão');
        $response->assertDontSee('jumpToChorus');
        $response->assertDontSee('>TOM<', false);
    }

    public function test_song_stage_view_renders_capo_badge_without_hiding_on_mobile(): void
    {
        $this->song->update(['capo_fret' => 2]);

        $response = $this->get(route('songs.stage', [
            'organization' => $this->organization,
            'song' => $this->song,
        ]));

        $response->assertSuccessful();
        $response->assertSee('Capo:');
        $response->assertSee('2ª casa');
        $response->assertDontSee('hidden sm:flex items-center gap-2 text-xs font-mono');
    }

    public function test_song_stage_view_renders_musical_menu_with_shortcuts_and_info(): void
    {
        $this->song->update([
            'youtube_url' => 'https://www.youtube.com/watch?v=sample123',
            'spotify_url' => 'https://open.spotify.com/track/sample456',
        ]);

        $response = $this->get(route('songs.stage', [
            'organization' => $this->organization,
            'song' => $this->song,
        ]));

        $response->assertSuccessful();
        $response->assertSee('Harpa Cristã');
        $response->assertSee('Assistir no YouTube');
        $response->assertSee('https://www.youtube.com/watch?v=sample123');
        $response->assertSee('Ouvir no Spotify');
        $response->assertSee('https://open.spotify.com/track/sample456');
        $response->assertSee('Editar');

        $editUrl = route('filament.app.resources.songs.edit', [
            'tenant' => $this->organization,
            'record' => $this->song,
        ]);
        $response->assertSee($editUrl);
    }

    public function test_song_stage_view_omits_youtube_and_spotify_links_when_not_provided(): void
    {
        $this->song->update([
            'youtube_url' => null,
            'spotify_url' => null,
        ]);

        $response = $this->get(route('songs.stage', [
            'organization' => $this->organization,
            'song' => $this->song,
        ]));

        $response->assertSuccessful();
        $response->assertSee('Harpa Cristã');
        $response->assertDontSee('Assistir no YouTube');
        $response->assertDontSee('Ouvir no Spotify');
        $response->assertSee('Editar');
    }

    public function test_song_stage_view_lists_all_organization_songs_in_dropdown_menu(): void
    {
        $song2 = Song::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Aclame ao Senhor',
            'artist' => 'Diante do Trono',
            'original_key' => 'A',
        ]);

        $song3 = Song::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Vitorioso És',
            'artist' => 'Gabriel Guedes',
            'original_key' => 'E',
        ]);

        $otherOrg = Organization::factory()->create(['slug' => 'outra-org']);
        $otherSong = Song::factory()->create([
            'organization_id' => $otherOrg->id,
            'title' => 'Cifra Proibida de Outra Igreja',
            'artist' => 'Artista Externo',
            'original_key' => 'C',
        ]);

        $response = $this->get(route('songs.stage', [
            'organization' => $this->organization,
            'song' => $this->song,
        ]));

        $response->assertSuccessful();
        $response->assertSee('Músicas Cadastradas');
        $response->assertSee('Aclame ao Senhor');
        $response->assertSee('Diante do Trono');
        $response->assertSee('Vitorioso És');
        $response->assertSee('Gabriel Guedes');
        $response->assertSee('Atual');

        $song2StageUrl = route('songs.stage', [
            'organization' => $this->organization,
            'song' => $song2,
        ]);
        $song3StageUrl = route('songs.stage', [
            'organization' => $this->organization,
            'song' => $song3,
        ]);

        $response->assertSee($song2StageUrl);
        $response->assertSee($song3StageUrl);
        $response->assertDontSee('Cifra Proibida de Outra Igreja');
    }

    public function test_song_stage_view_toggles_capo_and_transposes_chords_to_non_capo_pitch(): void
    {
        $songWithCapo = Song::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Música com Capo',
            'original_key' => 'C',
            'capo_fret' => 2,
        ]);

        SongVersion::factory()->create([
            'song_id' => $songWithCapo->id,
            'base_key' => 'C',
            'capo_fret' => 2,
            'chordpro_content' => "[C]Graça [G]maravilhosa\n[Am]Deus [F]fiel",
            'is_default' => true,
        ]);

        $component = Livewire::test(SongStageView::class, [
            'organization' => $this->organization,
            'song' => $songWithCapo,
        ]);

        $component->assertSet('useCapo', true)
            ->assertSet('currentKey', 'C')
            ->assertSeeHtml('Capo:')
            ->assertSeeHtml('2ª casa')
            ->assertSee('Graça');

        $this->assertStringContainsString('>C</span>', $component->instance()->getFormattedChords()->toHtml());
        $this->assertStringContainsString('>G</span>', $component->instance()->getFormattedChords()->toHtml());

        // Toggle para desativar capo: sobe 2 semitons (C -> D)
        $component->call('toggleCapo')
            ->assertSet('useCapo', false)
            ->assertSet('currentKey', 'D');

        $this->assertStringContainsString('>D</span>', $component->instance()->getFormattedChords()->toHtml());
        $this->assertStringContainsString('>A</span>', $component->instance()->getFormattedChords()->toHtml());
        $this->assertStringContainsString('>Bm</span>', $component->instance()->getFormattedChords()->toHtml());

        // Toggle para reativar capo: volta para C
        $component->call('toggleCapo')
            ->assertSet('useCapo', true)
            ->assertSet('currentKey', 'C');

        $this->assertStringContainsString('>C</span>', $component->instance()->getFormattedChords()->toHtml());
        $this->assertStringContainsString('>G</span>', $component->instance()->getFormattedChords()->toHtml());

        // Resetar tom quando o capo está desativado deve restaurar capo ativo e tom original
        $component->call('toggleCapo')
            ->assertSet('useCapo', false)
            ->assertSet('currentKey', 'D')
            ->call('resetKey')
            ->assertSet('useCapo', true)
            ->assertSet('currentKey', 'C');
    }

    public function test_song_stage_view_renders_capo_on_title_line_toggle_below_and_hides_wake_lock_on_small_screens(): void
    {
        $songWithCapo = Song::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Música Capo Layout',
            'artist' => 'Artista do Louvor',
            'original_key' => 'G',
            'capo_fret' => 4,
        ]);

        $response = $this->get(route('songs.stage', [
            'organization' => $this->organization,
            'song' => $songWithCapo,
        ]));

        $response->assertSuccessful();
        $response->assertSeeHtml('Capo:');
        $response->assertSeeHtml('4ª casa');
        $response->assertDontSee('🎸');
        $response->assertSeeHtml('@click.stop="openMenu = !openMenu"');
        $response->assertSeeHtml('hidden lg:flex items-center gap-1.5 px-2.5 py-1.5 rounded-2xl bg-emerald-500/10');
    }
}
