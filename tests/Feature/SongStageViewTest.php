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
        $response->assertSee('75 BPM');
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
        $response->assertSee('Avulsa');
        $response->assertSee('Tela Ativa');
        $response->assertSee('2 Colunas');
        $response->assertSee('Letra');
        $response->assertSee('Refrão');
    }
}
