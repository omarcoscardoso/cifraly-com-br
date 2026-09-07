<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\Songs\Pages\CreateSong;
use App\Filament\Resources\Songs\Pages\EditSong;
use App\Filament\Resources\Songs\Pages\ListSongs;
use App\Models\Organization;
use App\Models\Song;
use App\Models\SongVersion;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SongResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->organization = Organization::factory()->create(['slug' => 'igreja-central']);
        $this->user->organizations()->attach($this->organization);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::setTenant($this->organization, isQuiet: true);
    }

    protected function tearDown(): void
    {
        Filament::setTenant(null);

        parent::tearDown();
    }

    public function test_can_render_song_list_page(): void
    {
        Song::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Graça Maravilhosa',
            'artist' => 'John Newton',
            'original_key' => 'G',
        ]);

        $response = $this->actingAs($this->user)->get('/admin/igreja-central/songs');

        $response->assertStatus(200);
        $response->assertSee('Graça Maravilhosa');
        $response->assertSee('John Newton');
    }

    public function test_songs_are_scoped_to_active_organization_tenant(): void
    {
        $otherOrg = Organization::factory()->create(['slug' => 'outra-igreja']);

        $songThisOrg = Song::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Música Nossa Igreja',
        ]);

        $songOtherOrg = Song::factory()->create([
            'organization_id' => $otherOrg->id,
            'title' => 'Música Outra Igreja',
        ]);

        Livewire::actingAs($this->user)
            ->test(ListSongs::class)
            ->assertCanSeeTableRecords([$songThisOrg])
            ->assertCanNotSeeTableRecords([$songOtherOrg]);
    }

    public function test_can_create_song_with_default_song_version(): void
    {
        Livewire::actingAs($this->user)
            ->test(CreateSong::class)
            ->fillForm([
                'title' => 'Porque Ele Vive',
                'artist' => 'Matt Maher',
                'original_key' => 'G',
                'bpm' => 74,
                'time_signature' => '4/4',
                'spotify_url' => 'https://open.spotify.com/track/123',
                'youtube_url' => 'https://youtube.com/watch?v=123',
                'chordpro_content' => "[Intro]\n[G] [C] [G] [D]\n\n[G]Deus enviou [C]Seu Filho amado",
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $song = Song::where('title', 'Porque Ele Vive')->first();
        $this->assertNotNull($song);
        $this->assertSame($this->organization->id, $song->organization_id);
        $this->assertSame('G', $song->original_key);
        $this->assertSame(74, $song->bpm);

        $defaultVersion = $song->defaultVersion;
        $this->assertNotNull($defaultVersion);
        $this->assertSame('Padrão', $defaultVersion->label);
        $this->assertSame('G', $defaultVersion->base_key);
        $this->assertTrue($defaultVersion->is_default);
        $this->assertStringContainsString('[G]Deus enviou', $defaultVersion->chordpro_content);
    }

    public function test_can_edit_song_and_update_default_version(): void
    {
        $song = Song::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Música Original',
            'original_key' => 'C',
        ]);

        $version = SongVersion::factory()->create([
            'song_id' => $song->id,
            'label' => 'Padrão',
            'base_key' => 'C',
            'chordpro_content' => '[C] Cifra antiga',
            'is_default' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(EditSong::class, ['record' => $song->getRouteKey()])
            ->assertFormSet([
                'title' => 'Música Original',
                'chordpro_content' => '[C] Cifra antiga',
            ])
            ->fillForm([
                'title' => 'Música Atualizada',
                'original_key' => 'D',
                'chordpro_content' => '[D] Cifra atualizada',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Música Atualizada', $song->fresh()->title);
        $this->assertSame('D', $song->fresh()->original_key);
        $this->assertSame('[D] Cifra atualizada', $version->fresh()->chordpro_content);
        $this->assertSame('D', $version->fresh()->base_key);
    }

    public function test_transpose_action_modal_updates_chord_reactively(): void
    {
        $song = Song::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Quão Grande É o Meu Deus',
            'original_key' => 'C',
        ]);

        SongVersion::factory()->create([
            'song_id' => $song->id,
            'label' => 'Padrão',
            'base_key' => 'C',
            'chordpro_content' => '[C] Quão grande [G] és Tu',
            'is_default' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(ListSongs::class)
            ->mountTableAction('transposePreview', $song)
            ->assertTableActionDataSet([
                'target_key' => 'C',
                'preview_content' => '[C] Quão grande [G] és Tu',
            ]);
    }

    public function test_can_filter_songs_by_original_key(): void
    {
        $songInG = Song::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Música em G',
            'original_key' => 'G',
        ]);

        $songInC = Song::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Música em C',
            'original_key' => 'C',
        ]);

        Livewire::actingAs($this->user)
            ->test(ListSongs::class)
            ->filterTable('original_key', 'G')
            ->assertCanSeeTableRecords([$songInG])
            ->assertCanNotSeeTableRecords([$songInC]);
    }
}
