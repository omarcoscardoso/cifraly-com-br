<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Roles\Pages\ListRoles;
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

class TeamAndRoleResourceTest extends TestCase
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
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::setTenant($this->organization, isQuiet: true);
    }

    protected function tearDown(): void
    {
        Filament::setTenant(null);

        parent::tearDown();
    }

    public function test_role_resource_lists_only_roles_of_active_organization(): void
    {
        $otherOrg = Organization::factory()->create(['slug' => 'outra-igreja']);

        $roleThisOrg = Role::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Guitarra Solo Principal',
            'category' => Role::CATEGORY_MUSICIAN,
        ]);

        $roleOtherOrg = Role::factory()->create([
            'organization_id' => $otherOrg->id,
            'name' => 'Guitarra Solo de Outra Igreja',
            'category' => Role::CATEGORY_MUSICIAN,
        ]);

        Livewire::test(ListRoles::class)
            ->searchTable('Guitarra Solo')
            ->assertCanSeeTableRecords([$roleThisOrg])
            ->assertCanNotSeeTableRecords([$roleOtherOrg]);
    }

    public function test_role_resource_creates_role_scoped_to_active_organization(): void
    {
        Livewire::test(CreateRole::class)
            ->fillForm([
                'name' => 'Operador de Câmera',
                'category' => Role::CATEGORY_TECHNICIAN,
                'description' => 'Responsável pela câmera 1 do streaming',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $role = Role::where('name', 'Operador de Câmera')->first();

        $this->assertNotNull($role);
        $this->assertSame($this->organization->id, $role->organization_id);
        $this->assertSame(Role::CATEGORY_TECHNICIAN, $role->category);
        $this->assertSame('Responsável pela câmera 1 do streaming', $role->description);
    }

    public function test_cannot_edit_role_from_another_organization(): void
    {
        $otherOrg = Organization::factory()->create(['slug' => 'outra-igreja']);
        $otherRole = Role::factory()->create([
            'organization_id' => $otherOrg->id,
            'name' => 'Role Estrangeira',
        ]);

        $response = $this->get("/admin/igreja-central/roles/{$otherRole->id}/edit");
        $response->assertStatus(404);
    }

    public function test_team_resource_lists_only_teams_of_active_organization(): void
    {
        $otherOrg = Organization::factory()->create(['slug' => 'outra-igreja']);

        $teamThisOrg = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Louvor Domingo Manhã',
        ]);

        $teamOtherOrg = Team::factory()->create([
            'organization_id' => $otherOrg->id,
            'name' => 'Louvor Outra Igreja',
        ]);

        Livewire::test(ListTeams::class)
            ->assertCanSeeTableRecords([$teamThisOrg])
            ->assertCanNotSeeTableRecords([$teamOtherOrg]);
    }

    public function test_team_resource_creates_team_scoped_to_active_organization(): void
    {
        Livewire::test(CreateTeam::class)
            ->fillForm([
                'name' => 'Equipe de Mídia',
                'description' => 'Equipe responsável por projeção e transmissão',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $team = Team::where('name', 'Equipe de Mídia')->first();

        $this->assertNotNull($team);
        $this->assertSame($this->organization->id, $team->organization_id);
        $this->assertSame('Equipe responsável por projeção e transmissão', $team->description);
    }

    public function test_cannot_edit_team_from_another_organization(): void
    {
        $otherOrg = Organization::factory()->create(['slug' => 'outra-igreja']);
        $otherTeam = Team::factory()->create([
            'organization_id' => $otherOrg->id,
            'name' => 'Equipe Outra Igreja',
        ]);

        $response = $this->get("/admin/igreja-central/teams/{$otherTeam->id}/edit");
        $response->assertStatus(404);
    }

    public function test_members_relation_manager_allows_linking_user_with_default_role(): void
    {
        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Banda Principal',
        ]);

        $member = User::factory()->create([
            'name' => 'Mateus Tecladista',
            'email' => 'mateus@cifraly.test',
            'phone' => '(11) 98888-7777',
        ]);
        $member->organizations()->attach($this->organization);

        $role = Role::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Teclado Solo',
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

        $teamMember = $team->fresh()->teamMembers()->where('user_id', $member->id)->first();
        $this->assertNotNull($teamMember);
        $this->assertSame($this->organization->id, $teamMember->organization_id);
        $this->assertSame($role->id, $teamMember->default_role_id);
        $this->assertTrue($team->fresh()->members->contains($member));
    }

    public function test_members_relation_manager_allows_linking_user_without_default_role(): void
    {
        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Equipe Geral',
        ]);

        $member = User::factory()->create([
            'name' => 'Voluntário Apoio',
            'email' => 'voluntario@cifraly.test',
        ]);
        $member->organizations()->attach($this->organization);

        Livewire::test(MembersRelationManager::class, [
            'ownerRecord' => $team,
            'pageClass' => EditTeam::class,
        ])
            ->mountTableAction('create')
            ->setTableActionData([
                'user_id' => $member->id,
                'default_role_id' => null,
            ])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $teamMember = $team->fresh()->teamMembers()->where('user_id', $member->id)->first();
        $this->assertNotNull($teamMember);
        $this->assertNull($teamMember->default_role_id);
    }

    public function test_cannot_associate_user_from_another_tenant_to_team(): void
    {
        $otherOrg = Organization::factory()->create(['slug' => 'outra-igreja']);

        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Louvor Juventude',
        ]);

        $externalUser = User::factory()->create([
            'name' => 'Usuario Outro Tenant',
            'email' => 'outro@tenant.com',
        ]);
        $externalUser->organizations()->attach($otherOrg);

        Livewire::test(MembersRelationManager::class, [
            'ownerRecord' => $team,
            'pageClass' => EditTeam::class,
        ])
            ->mountTableAction('create')
            ->setTableActionData([
                'user_id' => $externalUser->id,
                'default_role_id' => null,
            ])
            ->callMountedTableAction()
            ->assertHasTableActionErrors(['user_id']);

        $this->assertSame(0, $team->fresh()->teamMembers()->count());
    }

    public function test_cannot_associate_role_from_another_tenant_to_team_member(): void
    {
        $otherOrg = Organization::factory()->create(['slug' => 'outra-igreja']);

        $team = Team::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Louvor Sênior',
        ]);

        $member = User::factory()->create([
            'name' => 'Gabriel Vocal',
            'email' => 'gabriel@cifraly.test',
        ]);
        $member->organizations()->attach($this->organization);

        $externalRole = Role::factory()->create([
            'organization_id' => $otherOrg->id,
            'name' => 'Role de Outro Tenant',
            'category' => Role::CATEGORY_VOCAL,
        ]);

        Livewire::test(MembersRelationManager::class, [
            'ownerRecord' => $team,
            'pageClass' => EditTeam::class,
        ])
            ->mountTableAction('create')
            ->setTableActionData([
                'user_id' => $member->id,
                'default_role_id' => $externalRole->id,
            ])
            ->callMountedTableAction()
            ->assertHasTableActionErrors(['default_role_id']);

        $this->assertSame(0, $team->fresh()->teamMembers()->count());
    }
}
