<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organization;
use App\Models\Role;
use App\Models\Song;
use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ResourceAuthorizationPoliciesTest extends TestCase
{
    use RefreshDatabase;

    private Organization $orgA;

    private Organization $orgB;

    private User $adminA;

    private User $memberA;

    private User $userB;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orgA = Organization::factory()->create(['name' => 'Igreja A', 'slug' => 'igreja-a']);
        $this->orgB = Organization::factory()->create(['name' => 'Igreja B', 'slug' => 'igreja-b']);

        $this->adminA = User::factory()->create(['name' => 'Admin Org A']);
        $this->adminA->organizations()->attach($this->orgA, ['role' => Organization::ROLE_ADMIN]);

        $this->memberA = User::factory()->create(['name' => 'Membro Org A']);
        $this->memberA->organizations()->attach($this->orgA, ['role' => Organization::ROLE_MEMBER]);

        $this->userB = User::factory()->create(['name' => 'User Org B']);
        $this->userB->organizations()->attach($this->orgB, ['role' => Organization::ROLE_MEMBER]);

        $this->superAdmin = User::factory()->create(['is_super_admin' => true]);
    }

    protected function tearDown(): void
    {
        Filament::setTenant(null);

        parent::tearDown();
    }

    public function test_song_policy_permissions(): void
    {
        Filament::setTenant($this->orgA, isQuiet: true);
        $song = Song::factory()->create(['organization_id' => $this->orgA->id]);

        // Member of Org A can view and update, but NOT delete
        $this->assertTrue(Gate::forUser($this->memberA)->allows('view', $song));
        $this->assertTrue(Gate::forUser($this->memberA)->allows('update', $song));
        $this->assertFalse(Gate::forUser($this->memberA)->allows('delete', $song));

        // Admin of Org A can delete
        $this->assertTrue(Gate::forUser($this->adminA)->allows('delete', $song));

        // User of Org B cannot view, update, or delete
        $this->assertFalse(Gate::forUser($this->userB)->allows('view', $song));
        $this->assertFalse(Gate::forUser($this->userB)->allows('update', $song));
        $this->assertFalse(Gate::forUser($this->userB)->allows('delete', $song));

        // Super Admin can do everything
        $this->assertTrue(Gate::forUser($this->superAdmin)->allows('view', $song));
        $this->assertTrue(Gate::forUser($this->superAdmin)->allows('delete', $song));
    }

    public function test_event_policy_permissions(): void
    {
        Filament::setTenant($this->orgA, isQuiet: true);
        $event = Event::factory()->create(['organization_id' => $this->orgA->id]);

        // Member of Org A can view, but NOT delete
        $this->assertTrue(Gate::forUser($this->memberA)->allows('view', $event));
        $this->assertFalse(Gate::forUser($this->memberA)->allows('delete', $event));

        // Admin of Org A can delete
        $this->assertTrue(Gate::forUser($this->adminA)->allows('delete', $event));

        // User B cannot access
        $this->assertFalse(Gate::forUser($this->userB)->allows('view', $event));
        $this->assertFalse(Gate::forUser($this->userB)->allows('delete', $event));
    }

    public function test_team_and_role_policy_permissions(): void
    {
        Filament::setTenant($this->orgA, isQuiet: true);
        $team = Team::factory()->create(['organization_id' => $this->orgA->id]);
        $role = Role::factory()->create(['organization_id' => $this->orgA->id]);

        // Member cannot delete teams or roles
        $this->assertFalse(Gate::forUser($this->memberA)->allows('delete', $team));
        $this->assertFalse(Gate::forUser($this->memberA)->allows('delete', $role));

        // Admin can delete
        $this->assertTrue(Gate::forUser($this->adminA)->allows('delete', $team));
        $this->assertTrue(Gate::forUser($this->adminA)->allows('delete', $role));

        // User B cannot view or delete
        $this->assertFalse(Gate::forUser($this->userB)->allows('view', $team));
        $this->assertFalse(Gate::forUser($this->userB)->allows('delete', $team));
    }
}
