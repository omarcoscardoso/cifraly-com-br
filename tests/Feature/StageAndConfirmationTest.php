<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Public\RosterConfirmation;
use App\Livewire\Stage\StageView;
use App\Models\Event;
use App\Models\EventRoster;
use App\Models\EventSong;
use App\Models\Organization;
use App\Models\Role;
use App\Models\Song;
use App\Models\SongVersion;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StageAndConfirmationTest extends TestCase
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
    }

    public function test_can_access_roster_confirmation_page_with_valid_token(): void
    {
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Culto de Celebração',
            'starts_at' => now()->addDays(3),
        ]);

        $role = Role::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Violão',
        ]);

        $volunteer = User::factory()->create(['name' => 'Davi Músico']);
        $volunteer->organizations()->attach($this->organization);

        $roster = EventRoster::factory()->create([
            'organization_id' => $this->organization->id,
            'event_id' => $event->id,
            'user_id' => $volunteer->id,
            'role_id' => $role->id,
            'status' => EventRoster::STATUS_PENDING,
            'confirmation_token' => 'test-token-1234567890abcdefghijklmnopqrstuv',
        ]);

        $response = $this->get("/r/{$roster->confirmation_token}");

        $response->assertStatus(200);
        $response->assertSee('Culto de Celebração');
        $response->assertSee('Davi Músico');
        $response->assertSee('Violão');
        $response->assertSee('Confirmar Presença');
    }

    public function test_can_confirm_presence_via_livewire(): void
    {
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Culto Jovem',
        ]);

        $role = Role::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Bateria',
        ]);

        $volunteer = User::factory()->create(['name' => 'Pedro Baterista']);
        $volunteer->organizations()->attach($this->organization);

        $roster = EventRoster::factory()->create([
            'organization_id' => $this->organization->id,
            'event_id' => $event->id,
            'user_id' => $volunteer->id,
            'role_id' => $role->id,
            'status' => EventRoster::STATUS_PENDING,
            'confirmation_token' => 'token-confirmar-1234567890abcdefghijklmnopqr',
        ]);

        Livewire::test(RosterConfirmation::class, ['token' => $roster->confirmation_token])
            ->call('confirm')
            ->assertSee('Sua presença foi confirmada com sucesso');

        $this->assertSame(EventRoster::STATUS_CONFIRMED, $roster->fresh()->status);
        $this->assertNotNull($roster->fresh()->responded_at);
        $this->assertNull($roster->fresh()->decline_reason);
    }

    public function test_can_decline_presence_with_reason_via_livewire(): void
    {
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Culto de Oração',
        ]);

        $role = Role::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Teclado',
        ]);

        $volunteer = User::factory()->create(['name' => 'Sara Tecladista']);
        $volunteer->organizations()->attach($this->organization);

        $roster = EventRoster::factory()->create([
            'organization_id' => $this->organization->id,
            'event_id' => $event->id,
            'user_id' => $volunteer->id,
            'role_id' => $role->id,
            'status' => EventRoster::STATUS_PENDING,
            'confirmation_token' => 'token-recusar-1234567890abcdefghijklmnopqrst',
        ]);

        Livewire::test(RosterConfirmation::class, ['token' => $roster->confirmation_token])
            ->set('declineReason', 'Estarei viajando a trabalho nesta data.')
            ->call('decline')
            ->assertSee('Sua ausência foi registrada');

        $this->assertSame(EventRoster::STATUS_DECLINED, $roster->fresh()->status);
        $this->assertNotNull($roster->fresh()->responded_at);
        $this->assertSame('Estarei viajando a trabalho nesta data.', $roster->fresh()->decline_reason);
    }

    public function test_invalid_confirmation_token_returns_404(): void
    {
        $response = $this->get('/r/token-inexistente-12345');

        $response->assertStatus(404);
    }

    public function test_stage_view_requires_authentication(): void
    {
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->get("/app/{$this->organization->slug}/events/{$event->id}/stage");

        $response->assertRedirect('/app/login');
    }

    public function test_stage_view_renders_event_setlist_and_transposed_chords(): void
    {
        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Banda Central',
        ]);

        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'team_id' => $team->id,
            'title' => 'Noite de Louvor e Adoração',
        ]);

        $song = Song::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Santo Espírito',
            'artist' => 'Laura Souguellis',
            'original_key' => 'E',
            'bpm' => 72,
        ]);

        $version = SongVersion::factory()->create([
            'song_id' => $song->id,
            'label' => 'Versão Padrão',
            'base_key' => 'E',
            'chordpro_content' => "E             B\nSanto Espírito és bem-vindo aqui\nC#m           A\nVem inundar e encher este lugar",
            'is_default' => true,
        ]);

        $eventSong = EventSong::factory()->create([
            'organization_id' => $this->organization->id,
            'event_id' => $event->id,
            'song_id' => $song->id,
            'song_version_id' => $version->id,
            'target_key' => 'G', // Transposto de E para G (+3 semitons: E -> F -> F# -> G)
            'order_index' => 1,
            'arrangement_notes' => 'Tocar suave na primeira estrofe',
        ]);

        $response = $this->actingAs($this->user)->get("/app/{$this->organization->slug}/events/{$event->id}/stage");

        $response->assertStatus(200);
        $response->assertSee('Noite de Louvor e Adoração');
        $response->assertSee('Santo Espírito');
        $response->assertSee('Laura Souguellis');
        $response->assertSee('Tocar suave na primeira estrofe');

        // Test Livewire component transposition from E to G
        // E -> G, B -> D, C#m -> Em, A -> C
        Livewire::actingAs($this->user)
            ->test(StageView::class, [
                'organization' => $this->organization,
                'event' => $event,
            ])
            ->assertSet('selectedEventSongId', $eventSong->id)
            ->assertSet('currentKey', 'G')
            ->assertSee('G')
            ->assertSee('D')
            ->assertSee('Em')
            ->assertSee('C')
            ->assertSee('Santo')
            ->assertSee('Espírito')
            ->assertSee('bem-vindo');
    }

    public function test_stage_view_interactive_transposition_and_song_navigation(): void
    {
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Culto Especial',
        ]);

        $song1 = Song::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Música Um',
            'original_key' => 'C',
        ]);
        $version1 = SongVersion::factory()->create([
            'song_id' => $song1->id,
            'base_key' => 'C',
            'chordpro_content' => 'C   G   Am   F',
            'is_default' => true,
        ]);
        $eventSong1 = EventSong::factory()->create([
            'organization_id' => $this->organization->id,
            'event_id' => $event->id,
            'song_id' => $song1->id,
            'song_version_id' => $version1->id,
            'target_key' => 'C',
            'order_index' => 1,
        ]);

        $song2 = Song::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Música Dois',
            'original_key' => 'D',
        ]);
        $version2 = SongVersion::factory()->create([
            'song_id' => $song2->id,
            'base_key' => 'D',
            'chordpro_content' => 'D   A   Bm   G',
            'is_default' => true,
        ]);
        $eventSong2 = EventSong::factory()->create([
            'organization_id' => $this->organization->id,
            'event_id' => $event->id,
            'song_id' => $song2->id,
            'song_version_id' => $version2->id,
            'target_key' => 'D',
            'order_index' => 2,
        ]);

        Livewire::actingAs($this->user)
            ->test(StageView::class, [
                'organization' => $this->organization,
                'event' => $event,
            ])
            ->assertSet('selectedEventSongId', $eventSong1->id)
            ->assertSet('currentKey', 'C')
            // Transpose up (+1 semitone: C -> C#)
            ->call('transposeUp')
            ->assertSet('currentKey', 'C#')
            ->assertSee('C#')
            // Transpose down (-1 semitone: C# -> C)
            ->call('transposeDown')
            ->assertSet('currentKey', 'C')
            // Navigate to next song (song 2)
            ->call('nextSong')
            ->assertSet('selectedEventSongId', $eventSong2->id)
            ->assertSet('currentKey', 'D')
            ->assertSee('Música Dois')
            // Navigate back to previous song (song 1)
            ->call('previousSong')
            ->assertSet('selectedEventSongId', $eventSong1->id)
            ->assertSet('currentKey', 'C');
    }

    public function test_stage_view_denies_access_to_unauthorized_organization_members(): void
    {
        $otherOrg = Organization::factory()->create(['slug' => 'outra-igreja']);
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $unauthorizedUser = User::factory()->create();
        $unauthorizedUser->organizations()->attach($otherOrg);

        $response = $this->actingAs($unauthorizedUser)->get("/app/{$this->organization->slug}/events/{$event->id}/stage");
        $response->assertStatus(403);
    }

    public function test_decline_validates_maximum_length_of_reason(): void
    {
        $event = Event::factory()->create(['organization_id' => $this->organization->id]);
        $role = Role::factory()->create(['organization_id' => $this->organization->id]);
        $roster = EventRoster::factory()->create([
            'organization_id' => $this->organization->id,
            'event_id' => $event->id,
            'user_id' => $this->user->id,
            'role_id' => $role->id,
            'status' => EventRoster::STATUS_PENDING,
        ]);

        Livewire::test(RosterConfirmation::class, ['token' => $roster->confirmation_token])
            ->set('declineReason', str_repeat('a', 501))
            ->call('decline')
            ->assertHasErrors(['declineReason' => 'max']);
    }

    public function test_stage_view_renders_critical_chord_styling_and_section_badges(): void
    {
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Culto de Domingo',
        ]);

        $song = Song::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Vim Para Adorar-te',
            'original_key' => 'E',
        ]);

        $version = SongVersion::factory()->create([
            'song_id' => $song->id,
            'label' => 'Versão Padrão',
            'base_key' => 'E',
            'chordpro_content' => "[Intro]\nE   B   C#m   A\n\n[Verso 1]\nE              B\nLuz do mundo desceste à terra\nC#m            A\nPra que eu pudesse te ver",
            'is_default' => true,
        ]);

        EventSong::factory()->create([
            'organization_id' => $this->organization->id,
            'event_id' => $event->id,
            'song_id' => $song->id,
            'song_version_id' => $version->id,
            'target_key' => 'E',
            'order_index' => 1,
        ]);

        Livewire::actingAs($this->user)
            ->test(StageView::class, [
                'organization' => $this->organization,
                'event' => $event,
            ])
            ->assertSeeHtml('stage-section-badge')
            ->assertSeeHtml('stage-chord-line')
            ->assertSeeHtml('stage-chord-pair')
            ->assertSeeHtml('stage-chord text-amber-400')
            ->assertSee('[Intro]')
            ->assertSee('[Verso 1]');
    }

    public function test_stage_view_renders_toolbar_controls_capo_badge_and_lyrics_toggle(): void
    {
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Culto de Louvor',
        ]);

        $song = Song::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Ao Único',
            'original_key' => 'C',
            'capo_fret' => 3,
        ]);

        $version = SongVersion::factory()->create([
            'song_id' => $song->id,
            'label' => 'Versão Padrão',
            'base_key' => 'C',
            'chordpro_content' => "C   G\nAo único que é digno",
            'is_default' => true,
        ]);

        EventSong::factory()->create([
            'organization_id' => $this->organization->id,
            'event_id' => $event->id,
            'song_id' => $song->id,
            'song_version_id' => $version->id,
            'target_key' => 'C',
            'order_index' => 1,
        ]);

        Livewire::actingAs($this->user)
            ->test(StageView::class, [
                'organization' => $this->organization,
                'event' => $event,
            ])
            ->assertDontSeeHtml('>TOM<')
            ->assertDontSee('jumpToChorus')
            ->assertDontSee('Saltar imediatamente para o Refrão')
            ->assertSeeHtml('Capo:')
            ->assertSeeHtml('3ª casa')
            ->assertDontSeeHtml('hidden sm:flex items-center gap-2 text-xs font-mono')
            ->assertSeeHtml('id="stage-current-key"');
    }

    public function test_stage_view_exit_button_links_to_application_home_dashboard(): void
    {
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Culto de Adoração',
        ]);

        $dashboardUrl = route('filament.app.pages.dashboard', ['tenant' => $this->organization]);

        Livewire::actingAs($this->user)
            ->test(StageView::class, [
                'organization' => $this->organization,
                'event' => $event,
            ])
            ->assertSeeHtml('href="'.$dashboardUrl.'"')
            ->assertDontSeeHtml('/events/'.$event->id.'/edit');
    }

    public function test_scoped_bindings_prevent_accessing_event_from_another_organization(): void
    {
        $otherOrg = Organization::factory()->create(['slug' => 'outra-igreja']);
        $otherEvent = Event::factory()->create([
            'organization_id' => $otherOrg->id,
            'title' => 'Evento de Outra Igreja',
        ]);

        $response = $this->actingAs($this->user)
            ->get("/app/{$this->organization->slug}/events/{$otherEvent->id}/stage");

        $response->assertNotFound();
    }

    public function test_scoped_bindings_prevent_accessing_song_from_another_organization(): void
    {
        $otherOrg = Organization::factory()->create(['slug' => 'outra-igreja']);
        $otherSong = Song::factory()->create([
            'organization_id' => $otherOrg->id,
            'title' => 'Música de Outra Igreja',
        ]);

        $response = $this->actingAs($this->user)
            ->get("/app/{$this->organization->slug}/songs/{$otherSong->id}/stage");

        $response->assertNotFound();
    }

    public function test_stage_view_toggles_two_columns_and_lyrics_only(): void
    {
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Ensaio Geral',
        ]);

        Livewire::actingAs($this->user)
            ->test(StageView::class, [
                'organization' => $this->organization,
                'event' => $event,
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

    public function test_stage_view_renders_two_columns_and_wake_lock_badges(): void
    {
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Culto de Celebração',
        ]);

        $response = $this->actingAs($this->user)
            ->get("/app/{$this->organization->slug}/events/{$event->id}/stage");

        $response->assertSuccessful();
        $response->assertSee('Tela Ativa');
        $response->assertSee('2 Colunas');
        $response->assertSee('Letra');
    }

    public function test_stage_view_renders_song_dropdown_panel_with_info_links_and_edit_action(): void
    {
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Culto de Celebração',
        ]);

        $song = Song::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Grande É o Senhor',
            'artist' => 'Adhemar de Campos',
            'original_key' => 'A',
            'youtube_url' => 'https://youtube.com/watch?v=123',
            'spotify_url' => 'https://open.spotify.com/track/123',
        ]);

        $version = SongVersion::factory()->create([
            'song_id' => $song->id,
            'label' => 'Versão Padrão',
            'base_key' => 'A',
            'chordpro_content' => "A   D\nGrande é o Senhor",
            'is_default' => true,
        ]);

        EventSong::factory()->create([
            'organization_id' => $this->organization->id,
            'event_id' => $event->id,
            'song_id' => $song->id,
            'song_version_id' => $version->id,
            'target_key' => 'A',
            'order_index' => 1,
        ]);

        $stageUrl = route('events.stage', [
            'organization' => $this->organization,
            'event' => $event,
        ], absolute: false);

        Livewire::actingAs($this->user)
            ->test(StageView::class, [
                'organization' => $this->organization,
                'event' => $event,
            ])
            ->assertSee('Grande É o Senhor')
            ->assertSee('Adhemar de Campos')
            ->assertDontSeeHtml('• <span class="text-slate-400">Culto de Celebração</span>')
            ->assertSee('Tom Orig:')
            ->assertSee('Assistir no YouTube')
            ->assertSee('Ouvir no Spotify')
            ->assertSee('Editar')
            ->assertSeeHtml(urlencode($stageUrl))
            ->assertSee('Músicas Cadastradas');
    }

    public function test_stage_view_allows_selecting_ad_hoc_organization_song_not_in_setlist(): void
    {
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Culto de Louvor',
        ]);

        $setlistSong = Song::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Música no Setlist',
            'artist' => 'Artista Setlist',
            'original_key' => 'C',
        ]);

        $setlistVersion = SongVersion::factory()->create([
            'song_id' => $setlistSong->id,
            'base_key' => 'C',
            'chordpro_content' => "C   G\nLetra do setlist",
            'is_default' => true,
        ]);

        EventSong::factory()->create([
            'organization_id' => $this->organization->id,
            'event_id' => $event->id,
            'song_id' => $setlistSong->id,
            'song_version_id' => $setlistVersion->id,
            'target_key' => 'C',
            'order_index' => 1,
        ]);

        $adHocSong = Song::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Música Avulsa Fora do Setlist',
            'artist' => 'Artista Avulso',
            'original_key' => 'G',
        ]);

        SongVersion::factory()->create([
            'song_id' => $adHocSong->id,
            'base_key' => 'G',
            'chordpro_content' => "G   D\nLetra da música avulsa",
            'is_default' => true,
        ]);

        Livewire::actingAs($this->user)
            ->test(StageView::class, [
                'organization' => $this->organization,
                'event' => $event,
            ])
            ->assertSee('Música no Setlist')
            ->assertSee('Artista Setlist')
            ->call('selectOrganizationSong', $adHocSong->id)
            ->assertSet('adHocSongId', $adHocSong->id)
            ->assertSee('Música Avulsa Fora do Setlist')
            ->assertSee('Artista Avulso')
            ->assertSee('Voltar ao Setlist');
    }

    public function test_stage_view_capo_toggle_transposes_chords_and_resets_on_song_change(): void
    {
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Culto Capo Teste',
        ]);

        $song = Song::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Música com Capo',
            'original_key' => 'C',
            'capo_fret' => 3,
        ]);

        $version = SongVersion::factory()->create([
            'song_id' => $song->id,
            'base_key' => 'C',
            'capo_fret' => 3,
            'chordpro_content' => '[C]Tu és [G]bom',
            'is_default' => true,
        ]);

        $eventSong = EventSong::factory()->create([
            'organization_id' => $this->organization->id,
            'event_id' => $event->id,
            'song_id' => $song->id,
            'song_version_id' => $version->id,
            'target_key' => 'C',
            'order_index' => 1,
        ]);

        $song2 = Song::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Música Sem Capo',
            'original_key' => 'D',
            'capo_fret' => null,
        ]);

        $version2 = SongVersion::factory()->create([
            'song_id' => $song2->id,
            'base_key' => 'D',
            'chordpro_content' => '[D]Deus é [A]fiel',
            'is_default' => true,
        ]);

        $eventSong2 = EventSong::factory()->create([
            'organization_id' => $this->organization->id,
            'event_id' => $event->id,
            'song_id' => $song2->id,
            'song_version_id' => $version2->id,
            'target_key' => 'D',
            'order_index' => 2,
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(StageView::class, [
                'organization' => $this->organization,
                'event' => $event,
            ]);

        // Capo ativo inicialmente
        $component->assertSet('useCapo', true)
            ->assertSet('currentKey', 'C')
            ->assertSeeHtml('Capo:')
            ->assertSeeHtml('3ª casa')
            ->assertSee('Tu')
            ->assertSee('bom');

        $this->assertStringContainsString('>C</span>', $component->instance()->getFormattedChords()->toHtml());
        $this->assertStringContainsString('>G</span>', $component->instance()->getFormattedChords()->toHtml());

        // Desativa capo: C + 3 semitons = D# (ou Eb)
        $component->call('toggleCapo')
            ->assertSet('useCapo', false)
            ->assertSet('currentKey', 'D#');

        $this->assertStringContainsString('>D#</span>', $component->instance()->getFormattedChords()->toHtml());
        $this->assertStringContainsString('>A#</span>', $component->instance()->getFormattedChords()->toHtml());

        // Reativa capo
        $component->call('toggleCapo')
            ->assertSet('useCapo', true)
            ->assertSet('currentKey', 'C');

        $this->assertStringContainsString('>C</span>', $component->instance()->getFormattedChords()->toHtml());
        $this->assertStringContainsString('>G</span>', $component->instance()->getFormattedChords()->toHtml());

        // Alterna para próxima música: useCapo deve resetar para true
        $component->call('toggleCapo')
            ->assertSet('useCapo', false)
            ->call('selectSong', $eventSong2->id)
            ->assertSet('useCapo', true)
            ->assertSet('currentKey', 'D');
    }

    public function test_stage_view_renders_hamburger_only_on_small_screens_and_capo_on_title_line(): void
    {
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Culto Teste Responsivo',
        ]);

        $song = Song::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Música Palco Teste',
            'artist' => 'Artista Palco',
            'original_key' => 'E',
            'capo_fret' => 2,
        ]);

        $version = SongVersion::factory()->create([
            'song_id' => $song->id,
            'base_key' => 'E',
            'capo_fret' => 2,
            'chordpro_content' => '[E]Louve ao [B]Senhor',
            'is_default' => true,
        ]);

        EventSong::factory()->create([
            'organization_id' => $this->organization->id,
            'event_id' => $event->id,
            'song_id' => $song->id,
            'song_version_id' => $version->id,
            'target_key' => 'E',
            'order_index' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->get("/app/{$this->organization->slug}/events/{$event->id}/stage");

        $response->assertSuccessful();
        $response->assertSeeHtml('w-10 h-10 md:w-auto px-0 md:px-3 py-2 rounded-2xl');
        $response->assertSeeHtml('hidden md:inline-block');
        $response->assertSeeHtml('Capo:');
        $response->assertSeeHtml('2ª casa');
        $response->assertDontSee('🎸');
        $response->assertSeeHtml('@click.stop="openMenu = !openMenu"');
        $response->assertSeeHtml('hidden lg:flex items-center gap-1.5 px-2.5 py-1.5 rounded-2xl bg-emerald-500/10');
    }
}
