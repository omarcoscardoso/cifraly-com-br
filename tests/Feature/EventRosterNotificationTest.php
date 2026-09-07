<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\Events\Pages\EditEvent;
use App\Filament\Resources\Events\RelationManagers\RostersRelationManager;
use App\Models\Event;
use App\Models\EventRoster;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use App\Notifications\EventRosterInvitationNotification;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class EventRosterNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->organization = Organization::factory()->create(['slug' => 'igreja-esperanca']);
        $this->admin->organizations()->attach($this->organization, ['role' => Organization::ROLE_ADMIN]);

        $this->actingAs($this->admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::setTenant($this->organization, isQuiet: true);
    }

    protected function tearDown(): void
    {
        Filament::setTenant(null);

        parent::tearDown();
    }

    public function test_notification_mail_content_is_properly_formatted(): void
    {
        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Culto de Domingo - Noite',
            'starts_at' => now()->addDays(2)->setTime(19, 0),
        ]);

        $role = Role::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Bateria',
        ]);

        $volunteer = User::factory()->create([
            'name' => 'Marcos Baterista',
            'email' => 'marcos@example.com',
        ]);
        $volunteer->organizations()->attach($this->organization);

        $roster = EventRoster::factory()->create([
            'organization_id' => $this->organization->id,
            'event_id' => $event->id,
            'user_id' => $volunteer->id,
            'role_id' => $role->id,
            'status' => EventRoster::STATUS_PENDING,
            'confirmation_token' => 'test-token-1234567890',
        ]);

        $notification = new EventRosterInvitationNotification($roster);
        $mail = $notification->toMail($volunteer);

        $this->assertStringContainsString('Culto de Domingo - Noite', $mail->subject);
        $this->assertStringContainsString('Bateria', implode(' ', $mail->introLines));
        $this->assertSame(route('roster.confirm', ['token' => 'test-token-1234567890']), $mail->actionUrl);
    }

    public function test_can_send_email_invite_from_rosters_relation_manager(): void
    {
        Notification::fake();

        $event = Event::factory()->create([
            'organization_id' => $this->organization->id,
            'title' => 'Culto dos Jovens',
        ]);

        $role = Role::factory()->create(['organization_id' => $this->organization->id]);
        $volunteer = User::factory()->create(['email' => 'voluntario@cifraly.test']);
        $volunteer->organizations()->attach($this->organization);

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
            ->callTableAction('sendEmailInvite', $roster)
            ->assertHasNoTableActionErrors();

        Notification::assertSentTo(
            $volunteer,
            EventRosterInvitationNotification::class,
            function (EventRosterInvitationNotification $notification) use ($roster) {
                return $notification->roster->id === $roster->id;
            }
        );
    }
}
