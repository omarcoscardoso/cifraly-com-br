<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RoleResourceTest extends TestCase
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

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::setTenant($this->organization, isQuiet: true);
    }

    protected function tearDown(): void
    {
        Filament::setTenant(null);

        parent::tearDown();
    }

    public function test_can_render_role_list_page(): void
    {
        $response = $this->actingAs($this->user)->get('/admin/igreja-central/roles');

        $response->assertStatus(200);
        $response->assertSee('Violão');
        $response->assertSee('Guitarra');
    }

    public function test_roles_are_scoped_to_active_organization(): void
    {
        $otherOrg = Organization::factory()->create(['slug' => 'outra-igreja']);

        $roleThisOrg = Role::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Bateria Nossa Igreja',
        ]);

        $roleOtherOrg = Role::factory()->create([
            'organization_id' => $otherOrg->id,
            'name' => 'Bateria Outra Igreja',
        ]);

        Livewire::actingAs($this->user)
            ->test(ListRoles::class)
            ->searchTable('Nossa Igreja')
            ->assertCanSeeTableRecords([$roleThisOrg])
            ->assertCanNotSeeTableRecords([$roleOtherOrg]);
    }

    public function test_can_create_role_with_tenant(): void
    {
        Livewire::actingAs($this->user)
            ->test(CreateRole::class)
            ->fillForm([
                'name' => 'Técnico de Som',
                'category' => Role::CATEGORY_TECHNICIAN,
                'description' => 'Responsável pela mesa digital',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $role = Role::where('name', 'Técnico de Som')->first();
        $this->assertNotNull($role);
        $this->assertSame($this->organization->id, $role->organization_id);
        $this->assertSame(Role::CATEGORY_TECHNICIAN, $role->category);
        $this->assertSame('Responsável pela mesa digital', $role->description);
    }

    public function test_can_edit_role(): void
    {
        $role = Role::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Vocalista Antigo',
            'category' => Role::CATEGORY_VOCAL,
        ]);

        Livewire::actingAs($this->user)
            ->test(EditRole::class, ['record' => $role->getRouteKey()])
            ->assertFormSet([
                'name' => 'Vocalista Antigo',
                'category' => Role::CATEGORY_VOCAL,
            ])
            ->fillForm([
                'name' => 'Ministro de Louvor',
                'category' => Role::CATEGORY_VOCAL,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Ministro de Louvor', $role->fresh()->name);
    }

    public function test_can_filter_roles_by_category(): void
    {
        $vocalRole = Role::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Backing Vocal',
            'category' => Role::CATEGORY_VOCAL,
        ]);

        $musicianRole = Role::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Teclado',
            'category' => Role::CATEGORY_MUSICIAN,
        ]);

        Livewire::actingAs($this->user)
            ->test(ListRoles::class)
            ->filterTable('category', Role::CATEGORY_VOCAL)
            ->assertCanSeeTableRecords([$vocalRole])
            ->assertCanNotSeeTableRecords([$musicianRole]);
    }

    public function test_cannot_edit_role_from_another_organization(): void
    {
        $otherOrg = Organization::factory()->create(['slug' => 'outra-igreja']);
        $otherRole = Role::factory()->create([
            'organization_id' => $otherOrg->id,
            'name' => 'Role Outra Igreja',
        ]);

        $response = $this->actingAs($this->user)->get("/admin/igreja-central/roles/{$otherRole->id}/edit");
        $response->assertStatus(404);
    }
}
