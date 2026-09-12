<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Widgets\OrganizationHeaderWidget;
use App\Filament\Widgets\RosterConfirmationAlertWidget;
use App\Filament\Widgets\UpcomingEventsWidget;
use App\Models\Event;
use App\Models\EventRoster;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MobileLayoutTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['name' => 'Marcos Cardoso']);
        $this->organization = Organization::factory()->create([
            'name' => 'Comunidade da Graça',
            'slug' => 'comunidade-da-graca',
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

    public function test_mobile_bottom_nav_renders_on_dashboard_for_authenticated_tenant(): void
    {
        $response = $this->get('/app/comunidade-da-graca');

        $response->assertSuccessful();
        $response->assertSee('Início');
        $response->assertSee('Cifras');
        $response->assertSee('Eventos');
        $response->assertSee('Perfil');
        $response->assertSee('Ações Rápidas');
        $response->assertSee('Novo Evento');
        $response->assertSee('Nova Cifra');
        $response->assertSee('Metrônomo');
    }

    public function test_mobile_hero_header_renders_greeting_and_first_name(): void
    {
        Livewire::test(OrganizationHeaderWidget::class)
            ->assertSuccessful()
            ->assertSee('Marcos')
            ->assertSee('Comunidade da Graça')
            ->assertSee('Evento')
            ->assertSee('Cifra')
            ->assertSee('SetList')
            ->assertDontSee('Buscar cifras...')
            ->assertDontSee('Metrônomo');
    }

    public function test_upcoming_events_widget_renders_mobile_carousel_cards(): void
    {
        $event1 = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Culto de Celebração',
            'starts_at' => now()->addDays(2)->setTime(19, 30),
            'status' => Event::STATUS_PUBLISHED,
        ]);

        $event2 = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Culto de Oração',
            'starts_at' => now()->addDays(4)->setTime(20, 0),
            'status' => Event::STATUS_PUBLISHED,
        ]);

        Livewire::test(UpcomingEventsWidget::class)
            ->assertSuccessful()
            ->assertSee('Cultos')
            ->assertSee('Eventos')
            ->assertSee('Culto de Celebração')
            ->assertSee('Culto de Oração')
            ->assertSee('Modo Palco')
            ->assertSee('Ver todos');
    }

    public function test_roster_confirmation_widget_renders_mobile_task_list(): void
    {
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Culto de Domingo',
            'starts_at' => now()->addDays(3)->setTime(18, 0),
            'status' => Event::STATUS_PUBLISHED,
        ]);

        $role = Role::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Bateria',
        ]);

        $roster = EventRoster::factory()->create([
            'organization_id' => $this->organization->id,
            'event_id' => $event->id,
            'user_id' => $this->user->id,
            'role_id' => $role->id,
            'status' => EventRoster::STATUS_PENDING,
        ]);

        Livewire::test(RosterConfirmationAlertWidget::class)
            ->assertSuccessful()
            ->assertSee('Minhas Escalas')
            ->assertSee('Culto de Domingo')
            ->assertSee('Bateria')
            ->assertSee('Confirmar')
            ->call('confirmAttendance', $roster->id)
            ->assertNotified('Presença confirmada!');
    }
}
