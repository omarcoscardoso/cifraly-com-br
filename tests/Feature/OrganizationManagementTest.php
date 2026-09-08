<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\Organizations\Pages\CreateOrganization;
use App\Filament\Resources\Organizations\Pages\EditOrganization;
use App\Filament\Resources\Organizations\Pages\ListOrganizations;
use App\Filament\Resources\Organizations\RelationManagers\TeamsRelationManager;
use App\Filament\Resources\Organizations\RelationManagers\UsersRelationManager;
use App\Models\Organization;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrganizationManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private User $regularUser;

    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create(['name' => 'Igreja Central', 'slug' => 'igreja-central']);

        $this->adminUser = User::factory()->admin()->create([
            'name' => 'Super Admin',
            'email' => 'admin@cifraly.com',
        ]);
        $this->adminUser->organizations()->attach($this->organization);

        $this->regularUser = User::factory()->create([
            'name' => 'Usuário Normal',
            'email' => 'user@cifraly.com',
            'is_super_admin' => false,
        ]);
        $this->regularUser->organizations()->attach($this->organization);

        Filament::setCurrentPanel(Filament::getPanel('app'));
        Filament::setTenant($this->organization, isQuiet: true);
    }

    protected function tearDown(): void
    {
        Filament::setTenant(null);

        parent::tearDown();
    }

    public function test_regular_user_cannot_access_organizations_management(): void
    {
        $response = $this->actingAs($this->regularUser)->get('/app/igreja-central/organizations');

        $response->assertStatus(403);
    }

    public function test_super_admin_can_access_organizations_management(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/app/igreja-central/organizations');

        $response->assertStatus(200);
        $response->assertSee('Igreja Central');
    }

    public function test_super_admin_can_view_all_organizations_in_the_list(): void
    {
        $otherOrg = Organization::factory()->create(['name' => 'Comunidade da Fé', 'slug' => 'comunidade-da-fe']);

        Livewire::actingAs($this->adminUser)
            ->test(ListOrganizations::class)
            ->assertCanSeeTableRecords([$this->organization, $otherOrg]);
    }

    public function test_super_admin_can_create_organization_with_auto_seeded_roles(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(CreateOrganization::class)
            ->fillForm([
                'name' => 'Igreja Nova Aliança',
                'slug' => 'igreja-nova-alianca',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $newOrg = Organization::where('slug', 'igreja-nova-alianca')->first();
        $this->assertNotNull($newOrg);
        $this->assertSame('Igreja Nova Aliança', $newOrg->name);

        // Verify creator was attached
        $this->assertTrue($newOrg->users->contains($this->adminUser));

        // Verify default roles were automatically seeded by OrganizationObserver
        $this->assertGreaterThan(0, Role::withoutGlobalScopes()->where('organization_id', $newOrg->id)->count());
    }

    public function test_super_admin_can_update_organization(): void
    {
        $targetOrg = Organization::factory()->create([
            'name' => 'Nome Antigo',
            'slug' => 'nome-antigo',
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(EditOrganization::class, ['record' => $targetOrg->getKey()])
            ->fillForm([
                'name' => 'Nome Atualizado',
                'slug' => 'nome-atualizado',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $targetOrg->refresh();
        $this->assertSame('Nome Atualizado', $targetOrg->name);
        $this->assertSame('nome-atualizado', $targetOrg->slug);
    }

    public function test_super_admin_can_delete_organization_and_cascades_safely(): void
    {
        $orgToDelete = Organization::factory()->create([
            'name' => 'Organização Para Deletar',
            'slug' => 'org-para-deletar',
        ]);

        $team = Team::factory()->create(['organization_id' => $orgToDelete->id, 'name' => 'Equipe Deletada']);

        Livewire::actingAs($this->adminUser)
            ->test(ListOrganizations::class)
            ->callTableAction('delete', $orgToDelete)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('organizations', ['id' => $orgToDelete->id]);
        $this->assertDatabaseMissing('teams', ['id' => $team->id]);
    }

    public function test_super_admin_can_manage_members_in_users_relation_manager(): void
    {
        $volunteer = User::factory()->create(['name' => 'Voluntário João']);

        // Test attaching user to organization
        Livewire::actingAs($this->adminUser)
            ->test(UsersRelationManager::class, [
                'ownerRecord' => $this->organization,
                'pageClass' => EditOrganization::class,
            ])
            ->callTableAction('attach', data: [
                'recordId' => $volunteer->id,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertTrue($this->organization->users()->where('users.id', $volunteer->id)->exists());

        // Test detaching user from organization
        Livewire::actingAs($this->adminUser)
            ->test(UsersRelationManager::class, [
                'ownerRecord' => $this->organization,
                'pageClass' => EditOrganization::class,
            ])
            ->callTableAction('detach', $volunteer)
            ->assertHasNoTableActionErrors();

        $this->assertFalse($this->organization->users()->where('users.id', $volunteer->id)->exists());
    }

    public function test_super_admin_can_manage_teams_in_teams_relation_manager(): void
    {
        // Test creating team under organization
        Livewire::actingAs($this->adminUser)
            ->test(TeamsRelationManager::class, [
                'ownerRecord' => $this->organization,
                'pageClass' => EditOrganization::class,
            ])
            ->callTableAction('create', data: [
                'name' => 'Ministério de Louvor Central',
                'description' => 'Banda principal dos cultos de domingo',
            ])
            ->assertHasNoTableActionErrors();

        $team = Team::where('organization_id', $this->organization->id)
            ->where('name', 'Ministério de Louvor Central')
            ->first();

        $this->assertNotNull($team);
        $this->assertSame('Banda principal dos cultos de domingo', $team->description);

        // Test editing team
        Livewire::actingAs($this->adminUser)
            ->test(TeamsRelationManager::class, [
                'ownerRecord' => $this->organization,
                'pageClass' => EditOrganization::class,
            ])
            ->callTableAction('edit', $team, data: [
                'name' => 'Ministério de Louvor Central - Atualizado',
            ])
            ->assertHasNoTableActionErrors();

        $team->refresh();
        $this->assertSame('Ministério de Louvor Central - Atualizado', $team->name);

        // Test deleting team
        Livewire::actingAs($this->adminUser)
            ->test(TeamsRelationManager::class, [
                'ownerRecord' => $this->organization,
                'pageClass' => EditOrganization::class,
            ])
            ->callTableAction('delete', $team)
            ->assertHasNoTableActionErrors();

        $this->assertSoftDeleted('teams', ['id' => $team->id]);
    }
}
