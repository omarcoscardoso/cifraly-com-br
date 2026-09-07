<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organization;
use App\Models\Role;
use App\Models\Song;
use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiTenancyTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Filament::setTenant(null);

        parent::tearDown();
    }

    public function test_admin_panel_has_organization_tenancy_configured(): void
    {
        $panel = Filament::getPanel('admin');

        $this->assertTrue($panel->hasTenancy());
        $this->assertSame(Organization::class, $panel->getTenantModel());
        $this->assertSame('slug', $panel->getTenantSlugAttribute());
    }

    public function test_user_implements_filament_contracts_and_manages_tenants(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(FilamentUser::class, $user);
        $this->assertInstanceOf(HasTenants::class, $user);

        $org1 = Organization::factory()->create(['name' => 'Org 1']);
        $org2 = Organization::factory()->create(['name' => 'Org 2']);

        $user->organizations()->attach($org1);

        $panel = Filament::getPanel('admin');

        $this->assertTrue($user->canAccessPanel($panel));
        $this->assertTrue($user->canAccessTenant($org1));
        $this->assertFalse($user->canAccessTenant($org2));
        $this->assertTrue($user->getTenants($panel)->contains($org1));
        $this->assertFalse($user->getTenants($panel)->contains($org2));
    }

    public function test_models_automatically_assign_organization_id_when_tenant_is_active(): void
    {
        $org = Organization::factory()->create(['name' => 'Active Org']);
        Filament::setTenant($org, isQuiet: true);

        $song = Song::create(['title' => 'Amazing Grace', 'artist' => 'John Newton']);
        $team = Team::create(['name' => 'Worship Team', 'description' => 'Main band']);
        $role = Role::create(['name' => 'Vocalist', 'description' => 'Lead singer']);
        $event = Event::create(['title' => 'Sunday Service']);

        $this->assertSame($org->id, $song->organization_id);
        $this->assertSame($org->id, $team->organization_id);
        $this->assertSame($org->id, $role->organization_id);
        $this->assertSame($org->id, $event->organization_id);

        $this->assertSame($org->id, $song->organization->id);
        $this->assertSame($org->id, $team->organization->id);
        $this->assertSame($org->id, $role->organization->id);
        $this->assertSame($org->id, $event->organization->id);
    }

    public function test_models_are_scoped_to_active_filament_tenant(): void
    {
        $org1 = Organization::factory()->create(['name' => 'Org 1']);
        $org2 = Organization::factory()->create(['name' => 'Org 2']);

        Song::create(['title' => 'Song Org 1', 'organization_id' => $org1->id]);
        Song::create(['title' => 'Song Org 2', 'organization_id' => $org2->id]);

        Team::create(['name' => 'Team Org 1', 'organization_id' => $org1->id]);
        Team::create(['name' => 'Team Org 2', 'organization_id' => $org2->id]);

        Role::create(['name' => 'Role Org 1', 'organization_id' => $org1->id]);
        Role::create(['name' => 'Role Org 2', 'organization_id' => $org2->id]);

        Event::create(['title' => 'Event Org 1', 'organization_id' => $org1->id]);
        Event::create(['title' => 'Event Org 2', 'organization_id' => $org2->id]);

        // When org1 is active
        Filament::setTenant($org1, isQuiet: true);

        $this->assertSame(1, Song::count());
        $this->assertSame('Song Org 1', Song::first()->title);
        $this->assertSame(1, Team::count());
        $this->assertSame('Team Org 1', Team::first()->name);
        $this->assertSame(14, Role::count());
        $this->assertTrue(Role::where('name', 'Role Org 1')->exists());
        $this->assertFalse(Role::where('name', 'Role Org 2')->exists());
        $this->assertSame(1, Event::count());
        $this->assertSame('Event Org 1', Event::first()->title);

        // When org2 is active
        Filament::setTenant($org2, isQuiet: true);

        $this->assertSame(1, Song::count());
        $this->assertSame('Song Org 2', Song::first()->title);
        $this->assertSame(1, Team::count());
        $this->assertSame('Team Org 2', Team::first()->name);
        $this->assertSame(14, Role::count());
        $this->assertTrue(Role::where('name', 'Role Org 2')->exists());
        $this->assertFalse(Role::where('name', 'Role Org 1')->exists());
        $this->assertSame(1, Event::count());
        $this->assertSame('Event Org 2', Event::first()->title);

        // Without tenant scope
        Filament::setTenant(null);

        $this->assertSame(2, Song::withoutGlobalScope('organization')->count());
        $this->assertSame(2, Team::withoutGlobalScope('organization')->count());
        $this->assertSame(28, Role::withoutGlobalScope('organization')->count());
        $this->assertSame(2, Event::withoutGlobalScope('organization')->count());
    }
}
