<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Widgets\OrganizationHeaderWidget;
use App\Filament\Widgets\OrganizationStatsOverviewWidget;
use App\Filament\Widgets\RecentSongsWidget;
use App\Filament\Widgets\RosterConfirmationAlertWidget;
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
        Filament::setCurrentPanel(Filament::getPanel('app'));
        Filament::setTenant($this->organization, isQuiet: true);
    }

    protected function tearDown(): void
    {
        Filament::setTenant(null);

        parent::tearDown();
    }

    public function test_default_widgets_are_replaced_in_admin_panel(): void
    {
        $panel = Filament::getPanel('app');
        $widgets = $panel->getWidgets();

        $this->assertNotContains(AccountWidget::class, $widgets);
        $this->assertNotContains(FilamentInfoWidget::class, $widgets);

        $this->assertContains(RosterConfirmationAlertWidget::class, $widgets);
        $this->assertContains(OrganizationHeaderWidget::class, $widgets);
        $this->assertContains(OrganizationStatsOverviewWidget::class, $widgets);
        $this->assertContains(UpcomingEventsWidget::class, $widgets);
        $this->assertContains(RecentSongsWidget::class, $widgets);
    }

    public function test_organization_header_widget_renders_correctly(): void
    {
        Livewire::test(OrganizationHeaderWidget::class)
            ->assertSuccessful()
            ->assertDontSee('Olá, Pastor João!')
            ->assertDontSee('VIDA2026')
            ->assertDontSee('Configurações')
            ->assertSee('Novo Evento')
            ->assertSee('Nova Cifra')
            ->assertDontSee('Repertório')
            ->assertDontSee('Equipes');
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
            ->assertSee('100%')
            ->assertSeeHtml('cifraly-stat-card');
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
            ->assertTableActionExists('stageView');
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
            ->assertSee('G');
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
        $response = $this->get('/app/igreja-vida-nova');
        $response->assertSuccessful();
        $response->assertSee('Igreja Vida Nova');
        $response->assertSee('Novo Evento');
    }

    public function test_roster_confirmation_alert_widget_renders_for_scheduled_user(): void
    {
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Culto de Domingo',
            'starts_at' => now()->addDays(2)->setTime(18, 0),
            'status' => Event::STATUS_PUBLISHED,
        ]);

        $role = Role::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Guitarra Elétrica',
        ]);

        $roster = EventRoster::factory()->create([
            'organization_id' => $this->organization->id,
            'event_id' => $event->id,
            'user_id' => $this->user->id,
            'role_id' => $role->id,
            'status' => EventRoster::STATUS_PENDING,
        ]);

        $this->assertTrue(RosterConfirmationAlertWidget::canView());

        Livewire::test(RosterConfirmationAlertWidget::class)
            ->assertSuccessful()
            ->assertSee('Você tem 1 escala aguardando confirmação')
            ->assertSee('Responder Escala')
            ->call('openModal')
            ->assertSee('Culto de Domingo')
            ->assertSee('Guitarra Elétrica')
            ->assertSee('Confirmar Presença')
            ->assertSee('Não poderei ir');
    }

    public function test_user_can_confirm_attendance_from_dashboard_widget(): void
    {
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Culto da Família',
            'starts_at' => now()->addDays(1)->setTime(19, 30),
            'status' => Event::STATUS_PUBLISHED,
        ]);

        $roster = EventRoster::factory()->create([
            'organization_id' => $this->organization->id,
            'event_id' => $event->id,
            'user_id' => $this->user->id,
            'status' => EventRoster::STATUS_PENDING,
        ]);

        Livewire::test(RosterConfirmationAlertWidget::class)
            ->call('openModal')
            ->call('confirmAttendance', $roster->id)
            ->assertNotified('Presença confirmada!')
            ->assertDispatched('roster-updated')
            ->assertRedirect(Filament::getUrl($this->organization));

        $this->assertEquals(EventRoster::STATUS_CONFIRMED, $roster->fresh()->status);
        $this->assertNotNull($roster->fresh()->responded_at);

        // Depois que o usuário confirmou, não há mais pendências e o card desaparece da tela inicial
        $this->assertFalse(RosterConfirmationAlertWidget::canView());
    }

    public function test_user_can_decline_attendance_from_dashboard_widget(): void
    {
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Ensaio Geral Banda',
            'starts_at' => now()->addDays(3)->setTime(20, 0),
            'status' => Event::STATUS_PUBLISHED,
        ]);

        $roster = EventRoster::factory()->create([
            'organization_id' => $this->organization->id,
            'event_id' => $event->id,
            'user_id' => $this->user->id,
            'status' => EventRoster::STATUS_PENDING,
        ]);

        Livewire::test(RosterConfirmationAlertWidget::class)
            ->call('openModal')
            ->call('startDecline', $roster->id)
            ->set('declineReason', 'Viagem a trabalho')
            ->call('submitDecline', $roster->id)
            ->assertNotified('Ausência informada')
            ->assertDispatched('roster-updated')
            ->assertRedirect(Filament::getUrl($this->organization));

        $this->assertEquals(EventRoster::STATUS_DECLINED, $roster->fresh()->status);
        $this->assertEquals('Viagem a trabalho', $roster->fresh()->decline_reason);
        $this->assertNotNull($roster->fresh()->responded_at);

        // Depois que o usuário informou ausência, não há mais pendências e o card desaparece da tela inicial
        $this->assertFalse(RosterConfirmationAlertWidget::canView());
    }

    public function test_stats_widget_refreshes_on_roster_updated_event(): void
    {
        Livewire::test(OrganizationStatsOverviewWidget::class)
            ->dispatch('roster-updated')
            ->assertSuccessful();
    }

    public function test_roster_confirmation_widget_hidden_when_user_has_no_upcoming_roster(): void
    {
        // Without any roster, canView() should return false
        $this->assertFalse(RosterConfirmationAlertWidget::canView());
    }
}
