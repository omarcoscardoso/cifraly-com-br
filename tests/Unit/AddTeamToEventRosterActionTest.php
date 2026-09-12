<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\Events\AddTeamToEventRosterAction;
use App\Models\Event;
use App\Models\EventRoster;
use App\Models\Organization;
use App\Models\Role;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddTeamToEventRosterActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_adds_all_team_members_with_roles_to_event_roster(): void
    {
        $org = Organization::factory()->create();
        $event = Event::factory()->create(['organization_id' => $org->id]);
        $team = Team::factory()->create(['organization_id' => $org->id]);
        $role = Role::factory()->create(['organization_id' => $org->id]);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        TeamMember::factory()->create([
            'organization_id' => $org->id,
            'team_id' => $team->id,
            'user_id' => $user1->id,
            'default_role_id' => $role->id,
        ]);

        TeamMember::factory()->create([
            'organization_id' => $org->id,
            'team_id' => $team->id,
            'user_id' => $user2->id,
            'default_role_id' => $role->id,
        ]);

        $action = new AddTeamToEventRosterAction;
        $result = $action->execute($event, $team, null, true);

        $this->assertSame(2, $result['added']);
        $this->assertSame(0, $result['skipped']);
        $this->assertCount(2, EventRoster::where('event_id', $event->id)->get());
    }

    public function test_skips_existing_rostered_members(): void
    {
        $org = Organization::factory()->create();
        $event = Event::factory()->create(['organization_id' => $org->id]);
        $team = Team::factory()->create(['organization_id' => $org->id]);
        $role = Role::factory()->create(['organization_id' => $org->id]);

        $user1 = User::factory()->create();
        TeamMember::factory()->create([
            'organization_id' => $org->id,
            'team_id' => $team->id,
            'user_id' => $user1->id,
            'default_role_id' => $role->id,
        ]);

        // Pre-create roster for user1
        EventRoster::factory()->create([
            'organization_id' => $org->id,
            'event_id' => $event->id,
            'user_id' => $user1->id,
            'role_id' => $role->id,
        ]);

        $action = new AddTeamToEventRosterAction;
        $result = $action->execute($event, $team, null, true);

        $this->assertSame(0, $result['added']);
        $this->assertSame(1, $result['skipped']);
    }

    public function test_falls_back_to_organization_role_when_member_and_fallback_role_are_null(): void
    {
        $org = Organization::factory()->create();
        $event = Event::factory()->create(['organization_id' => $org->id]);
        $team = Team::factory()->create(['organization_id' => $org->id]);
        $expectedRole = Role::where('organization_id', $org->id)->firstOrFail();

        $user1 = User::factory()->create();
        TeamMember::factory()->create([
            'organization_id' => $org->id,
            'team_id' => $team->id,
            'user_id' => $user1->id,
            'default_role_id' => null,
        ]);

        $action = new AddTeamToEventRosterAction;
        $result = $action->execute($event, $team, null, true);

        $this->assertSame(1, $result['added']);
        $this->assertSame(0, $result['skipped']);
        $this->assertDatabaseHas('event_rosters', [
            'event_id' => $event->id,
            'user_id' => $user1->id,
            'role_id' => $expectedRole->id,
        ]);
    }
}
