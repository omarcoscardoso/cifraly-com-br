<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Event;
use App\Models\EventRoster;
use App\Models\Organization;
use App\Models\Role;
use App\Models\Song;
use App\Models\User;
use App\Tenancy\TenancyContext;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenancyContextTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        TenancyContext::clear();
        Filament::setTenant(null);

        parent::tearDown();
    }

    public function test_tenancy_context_scopes_models_without_filament(): void
    {
        Filament::setTenant(null); // Ensure Filament has no tenant

        $org1 = Organization::factory()->create(['name' => 'Org 1']);
        $org2 = Organization::factory()->create(['name' => 'Org 2']);

        $song1 = Song::factory()->create(['organization_id' => $org1->id, 'title' => 'Música Org 1']);
        $song2 = Song::factory()->create(['organization_id' => $org2->id, 'title' => 'Música Org 2']);

        // Outside context: sees all
        $this->assertCount(2, Song::all());

        // Inside Org 1 context: sees only Org 1
        TenancyContext::run($org1, function () use ($song1, $song2) {
            $songs = Song::all();
            $this->assertCount(1, $songs);
            $this->assertTrue($songs->contains($song1));
            $this->assertFalse($songs->contains($song2));
        });

        // Outside context again: restored
        $this->assertNull(TenancyContext::get());
        $this->assertCount(2, Song::all());
    }

    public function test_tenancy_context_automatically_assigns_organization_id_on_create(): void
    {
        Filament::setTenant(null);

        $org = Organization::factory()->create();

        TenancyContext::run($org, function () use ($org) {
            $song = Song::create([
                'title' => 'Música Sem Org Explícita',
                'original_key' => 'C',
            ]);

            $this->assertSame($org->id, $song->organization_id);
        });
    }

    public function test_event_roster_obeys_tenancy_context_global_scope(): void
    {
        Filament::setTenant(null);

        $org1 = Organization::factory()->create();
        $org2 = Organization::factory()->create();

        $event1 = Event::factory()->create(['organization_id' => $org1->id]);
        $event2 = Event::factory()->create(['organization_id' => $org2->id]);

        $role1 = Role::factory()->create(['organization_id' => $org1->id]);
        $role2 = Role::factory()->create(['organization_id' => $org2->id]);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $roster1 = EventRoster::factory()->create([
            'organization_id' => $org1->id,
            'event_id' => $event1->id,
            'role_id' => $role1->id,
            'user_id' => $user1->id,
        ]);

        $roster2 = EventRoster::factory()->create([
            'organization_id' => $org2->id,
            'event_id' => $event2->id,
            'role_id' => $role2->id,
            'user_id' => $user2->id,
        ]);

        $this->assertCount(2, EventRoster::all());

        TenancyContext::run($org1, function () use ($roster1, $roster2): void {
            $rosters = EventRoster::all();
            $this->assertCount(1, $rosters);
            $this->assertTrue($rosters->contains($roster1));
            $this->assertFalse($rosters->contains($roster2));
        });
    }

    public function test_event_roster_automatically_assigns_organization_id(): void
    {
        Filament::setTenant(null);

        $org = Organization::factory()->create();
        $event = Event::factory()->create(['organization_id' => $org->id]);
        $role = Role::factory()->create(['organization_id' => $org->id]);
        $user = User::factory()->create();

        TenancyContext::run($org, function () use ($org, $event, $role, $user): void {
            $roster = EventRoster::create([
                'event_id' => $event->id,
                'role_id' => $role->id,
                'user_id' => $user->id,
            ]);

            $this->assertSame($org->id, $roster->organization_id);
            $this->assertNotEmpty($roster->confirmation_token);
        });
    }
}
