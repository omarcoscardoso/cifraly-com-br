<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\Events\EventResource;
use App\Filament\Resources\Events\Pages\CreateEvent;
use App\Filament\Resources\Events\Pages\EditEvent;
use App\Filament\Resources\Events\Pages\ListEvents;
use App\Filament\Resources\Events\RelationManagers\RostersRelationManager;
use App\Filament\Resources\Events\RelationManagers\SongsRelationManager;
use App\Models\Event;
use App\Models\EventRoster;
use App\Models\EventSong;
use App\Models\Organization;
use App\Models\Role;
use App\Models\Song;
use App\Models\SongVersion;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Support\Enums\Width;
use Filament\Tables\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EventResourceTest extends TestCase
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

        $this->actingAs($this->user);
        Filament::setCurrentPanel(Filament::getPanel('app'));
        Filament::setTenant($this->organization, isQuiet: true);
    }

    protected function tearDown(): void
    {
        Filament::setTenant(null);

        parent::tearDown();
    }

    public function test_can_render_event_list_page(): void
    {
        Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Culto de Domingo - Manhã',
            'starts_at' => now()->addDays(2),
        ]);

        $response = $this->get('/app/igreja-central/events');

        $response->assertStatus(200);
        $response->assertSee('Culto de Domingo - Manhã');
    }

    public function test_events_are_scoped_to_active_organization(): void
    {
        $otherOrg = Organization::factory()->create(['slug' => 'outra-igreja']);

        $eventThisOrg = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Evento da Nossa Igreja',
            'starts_at' => now()->addDays(3),
        ]);

        $eventOtherOrg = Event::factory()->create([
            'organization_id' => $otherOrg->id,
            'title' => 'Evento da Outra Igreja',
            'starts_at' => now()->addDays(3),
        ]);

        Livewire::test(ListEvents::class)
            ->assertCanSeeTableRecords([$eventThisOrg])
            ->assertCanNotSeeTableRecords([$eventOtherOrg]);
    }

    public function test_can_create_event_with_isolated_tenancy(): void
    {
        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Equipe de Louvor Manhã',
        ]);

        Livewire::test(CreateEvent::class)
            ->fillForm([
                'title' => 'Culto de Celebração e Ceia',
                'team_id' => $team->id,
                'status' => Event::STATUS_PUBLISHED,
                'starts_at' => '2026-10-10 19:00:00',
                'rehearsal_at' => '2026-10-10 17:30:00',
                'notes' => 'Levar vestimenta branca para o momento de comunhão.',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $event = Event::where('title', 'Culto de Celebração e Ceia')->first();

        $this->assertNotNull($event);
        $this->assertSame($this->organization->id, $event->organization_id);
        $this->assertSame($team->id, $event->team_id);
        $this->assertSame(Event::STATUS_PUBLISHED, $event->status);
        $this->assertSame('Levar vestimenta branca para o momento de comunhão.', $event->notes);
    }

    public function test_can_edit_event(): void
    {
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Culto Especial',
            'status' => Event::STATUS_DRAFT,
        ]);

        Livewire::test(EditEvent::class, ['record' => $event->getRouteKey()])
            ->fillForm([
                'title' => 'Culto Especial Publicado',
                'status' => Event::STATUS_PUBLISHED,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Culto Especial Publicado', $event->fresh()->title);
        $this->assertSame(Event::STATUS_PUBLISHED, $event->fresh()->status);
    }

    public function test_creating_event_with_team_automatically_adds_team_members_to_roster(): void
    {
        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Banda Principal',
        ]);

        $roleGuitar = Role::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Guitarrista',
        ]);

        $musician1 = User::factory()->create(['name' => 'Musico 1']);
        $musician1->organizations()->attach($this->organization);
        TeamMember::factory()->create([
            'organization_id' => $this->organization->id,
            'team_id' => $team->id,
            'user_id' => $musician1->id,
            'default_role_id' => $roleGuitar->id,
        ]);

        $musician2 = User::factory()->create(['name' => 'Musico 2']);
        $musician2->organizations()->attach($this->organization);
        TeamMember::factory()->create([
            'organization_id' => $this->organization->id,
            'team_id' => $team->id,
            'user_id' => $musician2->id,
            'default_role_id' => null,
        ]);

        Livewire::test(CreateEvent::class)
            ->fillForm([
                'title' => 'Culto com Equipe Automática',
                'team_id' => $team->id,
                'status' => Event::STATUS_PUBLISHED,
                'starts_at' => '2026-11-15 19:00:00',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $event = Event::where('title', 'Culto com Equipe Automática')->first();
        $this->assertNotNull($event);

        $rosters = $event->rosters;
        $this->assertCount(2, $rosters);
        $this->assertTrue($rosters->contains('user_id', $musician1->id));
        $this->assertTrue($rosters->contains('user_id', $musician2->id));
    }

    public function test_cannot_edit_event_from_another_organization(): void
    {
        $otherOrg = Organization::factory()->create(['slug' => 'outra-igreja']);
        $otherEvent = Event::factory()->create([
            'organization_id' => $otherOrg->id,
            'title' => 'Evento Outra Igreja',
        ]);

        $response = $this->get("/app/igreja-central/events/{$otherEvent->id}/edit");
        $response->assertStatus(404);
    }

    public function test_can_add_volunteer_to_roster_with_unique_confirmation_token(): void
    {
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Noite de Louvor',
            'starts_at' => now()->addDays(5),
        ]);

        $volunteer = User::factory()->create([
            'name' => 'Felipe Baixista',
            'phone' => '(11) 97777-6666',
        ]);
        $volunteer->organizations()->attach($this->organization);

        $role = Role::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Baixo',
            'category' => Role::CATEGORY_MUSICIAN,
        ]);

        Livewire::test(RostersRelationManager::class, [
            'ownerRecord' => $event,
            'pageClass' => EditEvent::class,
        ])
            ->mountTableAction('create')
            ->setTableActionData([
                'user_id' => $volunteer->id,
                'role_id' => $role->id,
                'status' => EventRoster::STATUS_PENDING,
            ])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $roster = EventRoster::where('event_id', $event->id)->where('user_id', $volunteer->id)->first();

        $this->assertNotNull($roster);
        $this->assertSame($this->organization->id, $roster->organization_id);
        $this->assertSame($role->id, $roster->role_id);
        $this->assertSame(EventRoster::STATUS_PENDING, $roster->status);
        $this->assertNotEmpty($roster->confirmation_token);
        $this->assertSame(40, strlen($roster->confirmation_token));
    }

    public function test_can_edit_roster_status(): void
    {
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $volunteer = User::factory()->create(['name' => 'Carla Vocal']);
        $volunteer->organizations()->attach($this->organization);

        $role = Role::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Voz Principal',
        ]);

        $roster = EventRoster::factory()->create([
            'organization_id' => $this->organization->id,
            'event_id' => $event->id,
            'user_id' => $volunteer->id,
            'role_id' => $role->id,
            'status' => EventRoster::STATUS_PENDING,
        ]);

        Livewire::test(RostersRelationManager::class, [
            'ownerRecord' => $event,
            'pageClass' => EditEvent::class,
        ])
            ->mountTableAction('edit', $roster)
            ->setTableActionData([
                'role_id' => $role->id,
                'status' => EventRoster::STATUS_CONFIRMED,
                'decline_reason' => null,
            ])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertSame(EventRoster::STATUS_CONFIRMED, $roster->fresh()->status);
    }

    public function test_can_add_song_to_setlist_with_target_key_different_from_original_key(): void
    {
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Culto de Domingo',
        ]);

        $song = Song::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Grande é o Senhor',
            'original_key' => 'G',
            'bpm' => 128,
        ]);

        $version = SongVersion::factory()->create([
            'song_id' => $song->id,
            'label' => 'Versão Principal',
            'base_key' => 'G',
            'chordpro_content' => '[G]Grande é o [D]Senhor e digno de [C]louvor',
            'is_default' => true,
        ]);

        Livewire::test(SongsRelationManager::class, [
            'ownerRecord' => $event,
            'pageClass' => EditEvent::class,
        ])
            ->mountTableAction('create')
            ->setTableActionData([
                'song_id' => $song->id,
                'song_version_id' => $version->id,
                'target_key' => 'A',
                'order_index' => 1,
                'arrangement_notes' => 'Aumentar 1 tom no refrão final',
            ])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $eventSong = EventSong::where('event_id', $event->id)->where('song_id', $song->id)->first();

        $this->assertNotNull($eventSong);
        $this->assertSame($this->organization->id, $eventSong->organization_id);
        $this->assertSame($version->id, $eventSong->song_version_id);
        $this->assertSame('A', $eventSong->target_key);
        $this->assertSame(1, $eventSong->order_index);
        $this->assertSame('Aumentar 1 tom no refrão final', $eventSong->arrangement_notes);
    }

    public function test_cannot_add_volunteer_from_another_organization_to_roster(): void
    {
        $otherOrg = Organization::factory()->create(['slug' => 'outra-igreja']);

        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $externalUser = User::factory()->create(['name' => 'Estrangeiro']);
        $externalUser->organizations()->attach($otherOrg);

        $role = Role::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        Livewire::test(RostersRelationManager::class, [
            'ownerRecord' => $event,
            'pageClass' => EditEvent::class,
        ])
            ->mountTableAction('create')
            ->setTableActionData([
                'user_id' => $externalUser->id,
                'role_id' => $role->id,
                'status' => EventRoster::STATUS_PENDING,
            ])
            ->callMountedTableAction()
            ->assertHasTableActionErrors(['user_id']);

        $this->assertSame(0, $event->fresh()->rosters()->count());
    }

    public function test_cannot_assign_role_from_another_organization_to_roster(): void
    {
        $otherOrg = Organization::factory()->create(['slug' => 'outra-igreja']);

        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $volunteer = User::factory()->create();
        $volunteer->organizations()->attach($this->organization);

        $externalRole = Role::factory()->create([
            'organization_id' => $otherOrg->id,
            'name' => 'Função de Outra Igreja',
        ]);

        Livewire::test(RostersRelationManager::class, [
            'ownerRecord' => $event,
            'pageClass' => EditEvent::class,
        ])
            ->mountTableAction('create')
            ->setTableActionData([
                'user_id' => $volunteer->id,
                'role_id' => $externalRole->id,
                'status' => EventRoster::STATUS_PENDING,
            ])
            ->callMountedTableAction()
            ->assertHasTableActionErrors(['role_id']);

        $this->assertSame(0, $event->fresh()->rosters()->count());
    }

    public function test_cannot_add_song_from_another_organization_to_setlist(): void
    {
        $otherOrg = Organization::factory()->create(['slug' => 'outra-igreja']);

        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $externalSong = Song::factory()->create([
            'organization_id' => $otherOrg->id,
            'title' => 'Música Estrangeira',
        ]);

        $version = SongVersion::factory()->create([
            'song_id' => $externalSong->id,
        ]);

        Livewire::test(SongsRelationManager::class, [
            'ownerRecord' => $event,
            'pageClass' => EditEvent::class,
        ])
            ->mountTableAction('create')
            ->setTableActionData([
                'song_id' => $externalSong->id,
                'song_version_id' => $version->id,
                'target_key' => 'C',
                'order_index' => 1,
            ])
            ->callMountedTableAction()
            ->assertHasTableActionErrors(['song_id']);

        $this->assertSame(0, $event->fresh()->eventSongs()->count());
    }

    public function test_can_add_entire_team_with_roles_to_event_roster(): void
    {
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Culto de Domingo Noite',
        ]);

        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Banda Principal',
        ]);

        $roleGuitar = Role::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Guitarra Solo',
        ]);

        $roleBass = Role::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Contrabaixo',
        ]);

        $musician1 = User::factory()->create(['name' => 'Guitarrista']);
        $musician1->organizations()->attach($this->organization);
        TeamMember::factory()->create([
            'organization_id' => $this->organization->id,
            'team_id' => $team->id,
            'user_id' => $musician1->id,
            'default_role_id' => $roleGuitar->id,
        ]);

        $musician2 = User::factory()->create(['name' => 'Baixista']);
        $musician2->organizations()->attach($this->organization);
        TeamMember::factory()->create([
            'organization_id' => $this->organization->id,
            'team_id' => $team->id,
            'user_id' => $musician2->id,
            'default_role_id' => $roleBass->id,
        ]);

        Livewire::test(RostersRelationManager::class, [
            'ownerRecord' => $event,
            'pageClass' => EditEvent::class,
        ])
            ->callTableAction('addTeam', data: [
                'team_id' => $team->id,
                'skip_existing' => true,
            ])
            ->assertHasNoTableActionErrors();

        $rosters = $event->fresh()->rosters()->with('user', 'role')->get();
        $this->assertCount(2, $rosters);

        $roster1 = $rosters->firstWhere('user_id', $musician1->id);
        $this->assertNotNull($roster1);
        $this->assertSame($roleGuitar->id, $roster1->role_id);
        $this->assertSame(EventRoster::STATUS_PENDING, $roster1->status);
        $this->assertNotNull($roster1->confirmation_token);

        $roster2 = $rosters->firstWhere('user_id', $musician2->id);
        $this->assertNotNull($roster2);
        $this->assertSame($roleBass->id, $roster2->role_id);
    }

    public function test_adding_team_skips_already_rostered_members(): void
    {
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Banda Jovem',
        ]);

        $role = Role::factory()->create(['organization_id' => $this->organization->id]);

        $musician1 = User::factory()->create();
        $musician1->organizations()->attach($this->organization);
        TeamMember::factory()->create([
            'organization_id' => $this->organization->id,
            'team_id' => $team->id,
            'user_id' => $musician1->id,
            'default_role_id' => $role->id,
        ]);

        $musician2 = User::factory()->create();
        $musician2->organizations()->attach($this->organization);
        TeamMember::factory()->create([
            'organization_id' => $this->organization->id,
            'team_id' => $team->id,
            'user_id' => $musician2->id,
            'default_role_id' => $role->id,
        ]);

        // Pre-roster musician 1
        EventRoster::factory()->create([
            'organization_id' => $this->organization->id,
            'event_id' => $event->id,
            'user_id' => $musician1->id,
            'role_id' => $role->id,
        ]);

        $this->assertCount(1, $event->fresh()->rosters);

        Livewire::test(RostersRelationManager::class, [
            'ownerRecord' => $event,
            'pageClass' => EditEvent::class,
        ])
            ->callTableAction('addTeam', data: [
                'team_id' => $team->id,
                'skip_existing' => true,
            ])
            ->assertHasNoTableActionErrors();

        // Total should be 2, without duplicating musician1
        $this->assertCount(2, $event->fresh()->rosters);
    }

    public function test_events_table_renders_roster_summary_using_with_count(): void
    {
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Culto com Escala Teste',
            'starts_at' => now()->addDays(2),
        ]);

        $role = Role::factory()->create(['organization_id' => $this->organization->id]);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        EventRoster::factory()->create([
            'organization_id' => $this->organization->id,
            'event_id' => $event->id,
            'user_id' => $user1->id,
            'role_id' => $role->id,
            'status' => EventRoster::STATUS_CONFIRMED,
        ]);

        EventRoster::factory()->create([
            'organization_id' => $this->organization->id,
            'event_id' => $event->id,
            'user_id' => $user2->id,
            'role_id' => $role->id,
            'status' => EventRoster::STATUS_CONFIRMED,
        ]);

        EventRoster::factory()->create([
            'organization_id' => $this->organization->id,
            'event_id' => $event->id,
            'user_id' => $user3->id,
            'role_id' => $role->id,
            'status' => EventRoster::STATUS_PENDING,
        ]);

        Livewire::test(ListEvents::class)
            ->assertCanSeeTableRecords([$event])
            ->assertSee('2/3 confirmados');
    }

    public function test_can_create_event_with_songs_and_rosters_in_tabs(): void
    {
        $song1 = Song::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Ao Único',
            'original_key' => 'F',
        ]);

        $song2 = Song::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Vim Para Adorar-te',
            'original_key' => 'E',
        ]);

        $volunteer = User::factory()->create(['name' => 'Marcos Tecladista']);
        $volunteer->organizations()->attach($this->organization);

        $role = Role::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Teclado',
        ]);

        Livewire::test(CreateEvent::class)
            ->fillForm([
                'title' => 'Culto com Repertório e Escala na Criação',
                'status' => Event::STATUS_PUBLISHED,
                'starts_at' => '2026-12-25 19:00:00',
                'eventSongs' => [
                    [
                        'song_id' => $song1->id,
                        'target_key' => 'F',
                        'arrangement_notes' => 'Introdução no piano',
                    ],
                    [
                        'song_id' => $song2->id,
                        'target_key' => 'E',
                        'arrangement_notes' => 'Transição suave',
                    ],
                ],
                'rosters' => [
                    [
                        'user_id' => $volunteer->id,
                        'role_id' => $role->id,
                        'status' => EventRoster::STATUS_PENDING,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $event = Event::where('title', 'Culto com Repertório e Escala na Criação')->first();
        $this->assertNotNull($event);

        // Verify songs saved
        $eventSongs = $event->eventSongs()->with('song')->get();
        $this->assertCount(2, $eventSongs);
        $this->assertSame($song1->id, $eventSongs[0]->song_id);
        $this->assertSame('F', $eventSongs[0]->target_key);
        $this->assertSame('Introdução no piano', $eventSongs[0]->arrangement_notes);
        $this->assertSame($song2->id, $eventSongs[1]->song_id);

        // Verify roster saved
        $rosters = $event->rosters()->with('user', 'role')->get();
        $this->assertCount(1, $rosters);
        $this->assertSame($volunteer->id, $rosters[0]->user_id);
        $this->assertSame($role->id, $rosters[0]->role_id);
        $this->assertNotEmpty($rosters[0]->confirmation_token);
    }

    public function test_edit_event_page_has_combined_tabs_configured(): void
    {
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Culto com Abas no Edit',
        ]);

        $relations = EventResource::getRelations();
        $this->assertSame(
            [
                SongsRelationManager::class,
                RostersRelationManager::class,
            ],
            $relations,
        );

        $component = Livewire::test(EditEvent::class, ['record' => $event->getRouteKey()]);
        $component->assertSuccessful();

        $instance = $component->instance();
        $this->assertTrue($instance->hasCombinedRelationManagerTabsWithContent());
        $this->assertSame('Dados do Evento', $instance->getContentTabLabel());
    }

    public function test_relation_managers_badges_reflect_record_counts(): void
    {
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $song = Song::factory()->create(['organization_id' => $this->organization->id]);
        EventSong::factory()->create([
            'organization_id' => $this->organization->id,
            'event_id' => $event->id,
            'song_id' => $song->id,
            'target_key' => 'C',
        ]);

        $volunteer = User::factory()->create();
        $volunteer->organizations()->attach($this->organization);
        $role = Role::factory()->create(['organization_id' => $this->organization->id]);
        EventRoster::factory()->create([
            'organization_id' => $this->organization->id,
            'event_id' => $event->id,
            'user_id' => $volunteer->id,
            'role_id' => $role->id,
        ]);

        $this->assertSame('1', SongsRelationManager::getBadge($event, EditEvent::class));
        $this->assertSame('1', RostersRelationManager::getBadge($event, EditEvent::class));
    }

    public function test_event_create_and_edit_pages_use_full_content_width(): void
    {
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $createComponent = Livewire::test(CreateEvent::class);
        $this->assertSame(Width::Full, $createComponent->instance()->getMaxContentWidth());

        $editComponent = Livewire::test(EditEvent::class, ['record' => $event->getRouteKey()]);
        $this->assertSame(Width::Full, $editComponent->instance()->getMaxContentWidth());
    }

    public function test_can_reorder_songs_in_setlist_table_using_drag_and_drop(): void
    {
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $song1 = Song::factory()->create(['organization_id' => $this->organization->id, 'title' => 'Música 1']);
        $song2 = Song::factory()->create(['organization_id' => $this->organization->id, 'title' => 'Música 2']);

        $eventSong1 = EventSong::factory()->create([
            'organization_id' => $this->organization->id,
            'event_id' => $event->id,
            'song_id' => $song1->id,
            'target_key' => 'C',
            'order_index' => 1,
        ]);

        $eventSong2 = EventSong::factory()->create([
            'organization_id' => $this->organization->id,
            'event_id' => $event->id,
            'song_id' => $song2->id,
            'target_key' => 'D',
            'order_index' => 2,
        ]);

        Livewire::test(SongsRelationManager::class, [
            'ownerRecord' => $event,
            'pageClass' => EditEvent::class,
        ])
            ->call('reorderTable', [(string) $eventSong2->id, (string) $eventSong1->id])
            ->assertHasNoErrors();

        $this->assertSame(1, $eventSong2->fresh()->order_index);
        $this->assertSame(2, $eventSong1->fresh()->order_index);
    }

    public function test_songs_relation_manager_table_has_only_order_and_title_columns_and_is_not_sortable(): void
    {
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $component = Livewire::test(SongsRelationManager::class, [
            'ownerRecord' => $event,
            'pageClass' => EditEvent::class,
        ]);

        /** @var Table $table */
        $table = $component->instance()->getTable();

        $this->assertFalse($table->isSearchable());

        $columns = $table->getColumns();
        $this->assertArrayHasKey('order_index', $columns);
        $this->assertArrayHasKey('song.title', $columns);
        $this->assertArrayNotHasKey('song.original_key', $columns);
        $this->assertArrayNotHasKey('target_key', $columns);
        $this->assertArrayNotHasKey('song.bpm', $columns);
        $this->assertArrayNotHasKey('arrangement_notes', $columns);

        $this->assertFalse($columns['order_index']->isSortable());
        $this->assertFalse($columns['song.title']->isSortable());
    }
}
