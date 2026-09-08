<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\Teams\Pages\CreateTeam;
use App\Filament\Resources\Teams\Pages\EditTeam;
use App\Filament\Resources\Teams\Pages\ListTeams;
use App\Filament\Resources\Teams\RelationManagers\MembersRelationManager;
use App\Models\Organization;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TeamResourceTest extends TestCase
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

    public function test_can_render_team_list_page(): void
    {
        Team::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Louvor Domingo Manhã',
        ]);

        $response = $this->get('/app/igreja-central/teams');

        $response->assertStatus(200);
        $response->assertSee('Louvor Domingo Manhã');
    }

    public function test_teams_are_scoped_to_active_organization(): void
    {
        $otherOrg = Organization::factory()->create(['slug' => 'outra-igreja']);

        $teamThisOrg = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Louvor Nossa Igreja',
        ]);

        $teamOtherOrg = Team::factory()->create([
            'organization_id' => $otherOrg->id,
            'name' => 'Louvor Outra Igreja',
        ]);

        Livewire::test(ListTeams::class)
            ->assertCanSeeTableRecords([$teamThisOrg])
            ->assertCanNotSeeTableRecords([$teamOtherOrg]);
    }

    public function test_can_create_team_with_tenant(): void
    {
        Livewire::test(CreateTeam::class)
            ->fillForm([
                'name' => 'Equipe de Transmissão',
                'description' => 'Responsáveis pela live e câmeras',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $team = Team::where('name', 'Equipe de Transmissão')->first();
        $this->assertNotNull($team);
        $this->assertSame($this->organization->id, $team->organization_id);
        $this->assertSame('Responsáveis pela live e câmeras', $team->description);
    }

    public function test_can_edit_team(): void
    {
        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Equipe Antiga',
        ]);

        Livewire::test(EditTeam::class, ['record' => $team->getRouteKey()])
            ->assertFormSet([
                'name' => 'Equipe Antiga',
            ])
            ->fillForm([
                'name' => 'Equipe Renovada',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Equipe Renovada', $team->fresh()->name);
    }

    public function test_can_attach_member_with_default_role_to_team(): void
    {
        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Louvor Quarta-Feira',
        ]);

        $member = User::factory()->create([
            'name' => 'Lucas Baterista',
            'phone' => '(11) 99999-1234',
        ]);
        $member->organizations()->attach($this->organization);

        $role = Role::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Bateria',
            'category' => Role::CATEGORY_MUSICIAN,
        ]);

        Livewire::test(MembersRelationManager::class, [
            'ownerRecord' => $team,
            'pageClass' => EditTeam::class,
        ])
            ->mountTableAction('create')
            ->setTableActionData([
                'user_id' => $member->id,
                'default_role_id' => $role->id,
            ])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertTrue($team->fresh()->members->contains($member));
        $teamMember = $team->fresh()->teamMembers()->where('user_id', $member->id)->first();
        $this->assertNotNull($teamMember);
        $this->assertSame($role->id, $teamMember->default_role_id);
        $this->assertSame($this->organization->id, $teamMember->organization_id);
    }

    public function test_can_edit_member_default_role_in_team(): void
    {
        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Banda Principal',
        ]);

        $member = User::factory()->create(['name' => 'Marcos Musico']);
        $member->organizations()->attach($this->organization);

        $roleGuitar = Role::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Guitarra',
        ]);

        $roleAcoustic = Role::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Violão',
        ]);

        $teamMember = $team->teamMembers()->create([
            'organization_id' => $this->organization->id,
            'user_id' => $member->id,
            'default_role_id' => $roleGuitar->id,
        ]);

        Livewire::test(MembersRelationManager::class, [
            'ownerRecord' => $team,
            'pageClass' => EditTeam::class,
        ])
            ->mountTableAction('edit', $teamMember)
            ->setTableActionData([
                'default_role_id' => $roleAcoustic->id,
            ])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertSame($roleAcoustic->id, $teamMember->fresh()->default_role_id);
    }

    public function test_can_detach_member_from_team(): void
    {
        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Banda Jovem',
        ]);

        $member = User::factory()->create(['name' => 'Ana Vocal']);
        $member->organizations()->attach($this->organization);

        $teamMember = $team->teamMembers()->create([
            'organization_id' => $this->organization->id,
            'user_id' => $member->id,
        ]);

        $this->assertCount(1, $team->fresh()->teamMembers);

        Livewire::test(MembersRelationManager::class, [
            'ownerRecord' => $team,
            'pageClass' => EditTeam::class,
        ])
            ->mountTableAction('delete', $teamMember)
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertCount(0, $team->fresh()->teamMembers);
    }

    public function test_cannot_edit_team_from_another_organization(): void
    {
        $otherOrg = Organization::factory()->create(['slug' => 'outra-igreja']);
        $otherTeam = Team::factory()->create([
            'organization_id' => $otherOrg->id,
            'name' => 'Equipe Outra Igreja',
        ]);

        $response = $this->get("/app/igreja-central/teams/{$otherTeam->id}/edit");
        $response->assertStatus(404);
    }
}
