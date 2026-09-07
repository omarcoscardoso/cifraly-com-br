<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Widgets\OrganizationHeaderWidget;
use App\Filament\Widgets\OrganizationStatsOverviewWidget;
use App\Filament\Widgets\RecentSongsWidget;
use App\Filament\Widgets\UpcomingEventsWidget;
use App\Models\Event;
use App\Models\EventRoster;
use App\Models\Organization;
use App\Models\Role;
use App\Models\Song;
use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardWidgetsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['name' => 'Pastor João']);
        $this->organization = Organization::factory()->create([
            'name' => 'Igreja Vida Nova',
            'slug' => 'igreja-vida-nova',
            'invite_code' => 'VIDA2026',
        ]);
        $this->user->organizations()->attach($this->organization, ['role' => Organization::ROLE_ADMIN]);

        $this->actingAs($this->user);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::setTenant($this->organization, isQuiet: true);
    }

    protected function tearDown(): void
    {
        Filament::setTenant(null);

        parent::tearDown();
    }

    public function test_default_widgets_are_replaced_in_admin_panel(): void
    {
        $panel = Filament::getPanel('admin');
        $widgets = $panel->getWidgets();

        $this->assertNotContains(AccountWidget::class, $widgets);
        $this->assertNotContains(FilamentInfoWidget::class, $widgets);

        $this->assertContains(OrganizationHeaderWidget::class, $widgets);
        $this->assertContains(OrganizationStatsOverviewWidget::class, $widgets);
        $this->assertContains(UpcomingEventsWidget::class, $widgets);
        $this->assertContains(RecentSongsWidget::class, $widgets);
    }

    public function test_organization_header_widget_renders_correctly(): void
    {
        Livewire::test(OrganizationHeaderWidget::class)
            ->assertSuccessful()
            ->assertSee('Pastor João')
            ->assertSee('Igreja Vida Nova')
            ->assertSee('VIDA2026')
            ->assertSee('Novo Evento')
            ->assertSee('Nova Cifra')
            ->assertSee('Repertório')
            ->assertSee('Equipes');
    }

    public function test_organization_stats_overview_widget_calculates_metrics(): void
    {
        // 1. Create songs
        Song::factory()->count(5)->create([
            'organization_id' => $this->organization->id,
        ]);

        // 2. Create teams
        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Banda Principal',
        ]);

        // 3. Create upcoming events
        $event1 = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Culto de Celebração',
            'starts_at' => now()->addDays(2)->setTime(19, 30),
            'status' => Event::STATUS_PUBLISHED,
        ]);

        $role = Role::factory()->create(['organization_id' => $this->organization->id]);
        $musician = User::factory()->create();
        $musician->organizations()->attach($this->organization);

        EventRoster::factory()->create([
            'organization_id' => $this->organization->id,
            'event_id' => $event1->id,
            'user_id' => $musician->id,
            'role_id' => $role->id,
            'status' => EventRoster::STATUS_CONFIRMED,
        ]);

        Livewire::test(OrganizationStatsOverviewWidget::class)
            ->assertSuccessful()
            ->assertSee('Próximos Eventos')
            ->assertSee('Músicas no Repertório')
            ->assertSee('Voluntários & Equipes')
            ->assertSee('Presença em Escalas')
            ->assertSee('5')
            ->assertSee('100%');
    }

    public function test_upcoming_events_widget_displays_events_and_stage_link(): void
    {
        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Banda Alpha',
        ]);

        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'team_id' => $team->id,
            'title' => 'Vigília Jovem',
            'starts_at' => now()->addDays(1)->setTime(22, 0),
            'status' => Event::STATUS_PUBLISHED,
        ]);

        Livewire::test(UpcomingEventsWidget::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$event])
            ->assertSee('Vigília Jovem')
            ->assertSee('Banda Alpha')
            ->assertTableActionExists('stageView')
            ->assertTableActionExists('editEvent');
    }

    public function test_recent_songs_widget_displays_songs(): void
    {
        $song = Song::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Bondade de Deus',
            'artist' => 'Isaias Saad',
            'original_key' => 'G',
            'bpm' => 70,
        ]);

        Livewire::test(RecentSongsWidget::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$song])
            ->assertSee('Bondade de Deus')
            ->assertSee('Isaias Saad')
            ->assertSee('G')
            ->assertSee('70')
            ->assertTableActionExists('editSong');
    }

    public function test_stats_widget_shows_pending_rosters_warning(): void
    {
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Ensaio Geral',
            'starts_at' => now()->addDays(1),
            'status' => Event::STATUS_PUBLISHED,
        ]);

        $role = Role::factory()->create(['organization_id' => $this->organization->id]);
        $musician = User::factory()->create();
        $musician->organizations()->attach($this->organization);

        EventRoster::factory()->create([
            'organization_id' => $this->organization->id,
            'event_id' => $event->id,
            'user_id' => $musician->id,
            'role_id' => $role->id,
            'status' => EventRoster::STATUS_PENDING,
        ]);

        Livewire::test(OrganizationStatsOverviewWidget::class)
            ->assertSuccessful()
            ->assertSee('1 pendentes de resposta');
    }

    public function test_empty_widgets_render_friendly_empty_states(): void
    {
        Livewire::test(UpcomingEventsWidget::class)
            ->assertSuccessful()
            ->assertSee('Nenhum evento agendado');

        Livewire::test(RecentSongsWidget::class)
            ->assertSuccessful()
            ->assertSee('Nenhuma música cadastrada');
    }

    public function test_dashboard_page_renders_with_widgets(): void
    {
        $response = $this->get('/admin/igreja-vida-nova');
        $response->assertSuccessful();
        $response->assertSee('Olá, Pastor João!');
        $response->assertSee('Igreja Vida Nova');
    }
}
