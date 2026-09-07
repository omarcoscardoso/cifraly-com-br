<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Organization;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private User $regularUser;

    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create(['slug' => 'igreja-central']);

        $this->adminUser = User::factory()->admin()->create([
            'name' => 'Admin Geral',
            'email' => 'admin@cifraly.com',
        ]);
        $this->adminUser->organizations()->attach($this->organization);

        $this->regularUser = User::factory()->create([
            'name' => 'Usuário Comum',
            'email' => 'comum@cifraly.com',
            'is_super_admin' => false,
        ]);
        $this->regularUser->organizations()->attach($this->organization);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::setTenant($this->organization, isQuiet: true);
    }

    protected function tearDown(): void
    {
        Filament::setTenant(null);

        parent::tearDown();
    }

    public function test_regular_user_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->regularUser)->get('/admin/igreja-central/users');

        $response->assertStatus(403);
    }

    public function test_super_admin_can_access_user_management(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/igreja-central/users');

        $response->assertStatus(200);
        $response->assertSee('Admin Geral');
        $response->assertSee('Usuário Comum');
    }

    public function test_super_admin_can_view_all_users_in_the_list(): void
    {
        $anotherUser = User::factory()->create([
            'name' => 'Novo Membro',
            'email' => 'novo@cifraly.com',
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ListUsers::class)
            ->assertCanSeeTableRecords([$this->adminUser, $this->regularUser, $anotherUser]);
    }

    public function test_super_admin_can_create_user_with_organizations(): void
    {
        $org2 = Organization::factory()->create(['name' => 'Igreja Secundária', 'slug' => 'igreja-secundaria']);

        Livewire::actingAs($this->adminUser)
            ->test(CreateUser::class)
            ->fillForm([
                'name' => 'Novo Tecladista',
                'email' => 'tecladista@cifraly.com',
                'phone' => '(11) 98888-7777',
                'password' => 'secret12345',
                'organizations' => [$this->organization->id, $org2->id],
                'is_super_admin' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::where('email', 'tecladista@cifraly.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('Novo Tecladista', $user->name);
        $this->assertSame('(11) 98888-7777', $user->phone);
        $this->assertTrue(Hash::check('secret12345', $user->password));
        $this->assertFalse($user->isSuperAdmin());

        $this->assertCount(2, $user->organizations);
        $this->assertTrue($user->organizations->contains($this->organization));
        $this->assertTrue($user->organizations->contains($org2));
    }

    public function test_super_admin_can_update_user_details(): void
    {
        $targetUser = User::factory()->create([
            'name' => 'Nome Antigo',
            'email' => 'antigo@cifraly.com',
            'phone' => '12345',
            'is_super_admin' => false,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(EditUser::class, ['record' => $targetUser->getKey()])
            ->fillForm([
                'name' => 'Nome Atualizado',
                'phone' => '(21) 99999-0000',
                'is_super_admin' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $targetUser->refresh();
        $this->assertSame('Nome Atualizado', $targetUser->name);
        $this->assertSame('(21) 99999-0000', $targetUser->phone);
        $this->assertTrue($targetUser->isSuperAdmin());
    }

    public function test_super_admin_can_reset_user_password_via_table_action(): void
    {
        $targetUser = User::factory()->create([
            'password' => Hash::make('old-password-123'),
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ListUsers::class)
            ->callTableAction('resetPassword', $targetUser, data: [
                'password' => 'new-secure-password-2026',
            ])
            ->assertHasNoTableActionErrors();

        $targetUser->refresh();
        $this->assertTrue(Hash::check('new-secure-password-2026', $targetUser->password));
        $this->assertFalse(Hash::check('old-password-123', $targetUser->password));
    }

    public function test_super_admin_can_manage_user_organizations_via_table_action(): void
    {
        $orgA = Organization::factory()->create(['name' => 'Org A']);
        $orgB = Organization::factory()->create(['name' => 'Org B']);

        $targetUser = User::factory()->create();
        $targetUser->organizations()->attach($orgA);

        $this->assertCount(1, $targetUser->organizations);

        Livewire::actingAs($this->adminUser)
            ->test(ListUsers::class)
            ->callTableAction('manageOrganizations', $targetUser, data: [
                'organizations' => [$orgA->id, $orgB->id],
            ])
            ->assertHasNoTableActionErrors();

        $targetUser->refresh();
        $this->assertCount(2, $targetUser->organizations);
        $this->assertTrue($targetUser->organizations->contains($orgA));
        $this->assertTrue($targetUser->organizations->contains($orgB));
    }

    public function test_super_admin_can_delete_other_user(): void
    {
        $targetUser = User::factory()->create([
            'name' => 'Usuário Para Excluir',
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ListUsers::class)
            ->callTableAction('delete', $targetUser)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('users', [
            'id' => $targetUser->id,
        ]);
    }

    public function test_super_admin_cannot_delete_themselves(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(ListUsers::class)
            ->assertTableActionDisabled('delete', $this->adminUser);

        $this->assertDatabaseHas('users', [
            'id' => $this->adminUser->id,
        ]);
    }
}
