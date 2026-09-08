<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\Tenancy\EditOrganizationProfile;
use App\Filament\Pages\Tenancy\RegisterOrganization;
use App\Filament\Resources\Organizations\Pages\EditOrganization;
use App\Filament\Resources\Organizations\RelationManagers\UsersRelationManager;
use App\Models\Organization;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrganizationOnboardingAndInviteTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_access_user_can_create_organization_and_becomes_org_admin(): void
    {
        $user = User::factory()->create();

        Filament::setCurrentPanel(Filament::getPanel('app'));

        Livewire::actingAs($user)
            ->test(RegisterOrganization::class)
            ->fillForm([
                'action_type' => 'create',
                'name' => 'Primeira Igreja Batista',
                'slug' => 'pib-central',
            ])
            ->call('register')
            ->assertHasNoFormErrors()
            ->assertRedirect('/app/pib-central');

        $org = Organization::where('slug', 'pib-central')->first();
        $this->assertNotNull($org);
        $this->assertNotNull($org->invite_code);
        $this->assertSame(8, strlen($org->invite_code));

        $this->assertTrue($user->isOrgAdmin($org));
        $this->assertSame(Organization::ROLE_ADMIN, $user->organizations()->where('organizations.id', $org->id)->first()->pivot->role);
    }

    public function test_first_access_user_can_join_organization_via_invite_code_and_becomes_member(): void
    {
        $existingOrg = Organization::factory()->create([
            'name' => 'Igreja Vida',
            'slug' => 'igreja-vida',
            'invite_code' => 'VIDA2026',
        ]);

        $newUser = User::factory()->create();

        Filament::setCurrentPanel(Filament::getPanel('app'));

        Livewire::actingAs($newUser)
            ->test(RegisterOrganization::class)
            ->fillForm([
                'action_type' => 'join',
                'invite_code' => 'VIDA2026',
            ])
            ->call('register')
            ->assertHasNoFormErrors()
            ->assertRedirect('/app/igreja-vida');

        $this->assertTrue($newUser->organizations->contains($existingOrg));
        $this->assertFalse($newUser->isOrgAdmin($existingOrg));
        $this->assertSame(Organization::ROLE_MEMBER, $newUser->organizations()->where('organizations.id', $existingOrg->id)->first()->pivot->role);
    }

    public function test_joining_with_invalid_invite_code_fails_validation(): void
    {
        $newUser = User::factory()->create();

        Filament::setCurrentPanel(Filament::getPanel('app'));

        Livewire::actingAs($newUser)
            ->test(RegisterOrganization::class)
            ->fillForm([
                'action_type' => 'join',
                'invite_code' => 'CODIGO_INEXISTENTE',
            ])
            ->call('register')
            ->assertHasFormErrors(['invite_code']);
    }

    public function test_org_admin_can_view_edit_organization_profile_page(): void
    {
        $org = Organization::factory()->create([
            'slug' => 'minha-igreja',
            'invite_code' => 'MINHA123',
        ]);

        $admin = User::factory()->create();
        $admin->organizations()->attach($org, ['role' => Organization::ROLE_ADMIN]);

        $response = $this->actingAs($admin)->get('/app/minha-igreja/profile');
        $response->assertStatus(200);
        $response->assertSee('Configurações da Organização');
        $response->assertSee('MINHA123');
    }

    public function test_regular_member_cannot_view_tenant_profile_page(): void
    {
        $org = Organization::factory()->create(['slug' => 'minha-igreja']);

        $member = User::factory()->create();
        $member->organizations()->attach($org, ['role' => Organization::ROLE_MEMBER]);

        $response = $this->actingAs($member)->get('/app/minha-igreja/profile');
        $response->assertStatus(404);
    }

    public function test_org_admin_can_regenerate_invite_code(): void
    {
        $org = Organization::factory()->create([
            'slug' => 'igreja-teste',
            'invite_code' => 'OLDCODE1',
        ]);

        $admin = User::factory()->create();
        $admin->organizations()->attach($org, ['role' => Organization::ROLE_ADMIN]);

        Filament::setCurrentPanel(Filament::getPanel('app'));
        Filament::setTenant($org, isQuiet: true);

        Livewire::actingAs($admin)
            ->test(EditOrganizationProfile::class)
            ->callAction('regenerateInviteCode')
            ->assertHasNoActionErrors();

        $org->refresh();
        $this->assertNotSame('OLDCODE1', $org->invite_code);
        $this->assertSame(8, strlen($org->invite_code));
    }

    public function test_direct_join_url_attaches_logged_in_user_and_redirects(): void
    {
        $org = Organization::factory()->create([
            'slug' => 'igreja-direta',
            'invite_code' => 'DIRETO26',
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/join/DIRETO26');

        $response->assertRedirect('/app/igreja-direta');
        $this->assertTrue($user->fresh()->organizations->contains($org));
        $this->assertSame(Organization::ROLE_MEMBER, $user->organizations()->where('organizations.id', $org->id)->first()->pivot->role);
    }

    public function test_direct_join_url_redirects_guest_to_login_and_saves_code_in_session(): void
    {
        $org = Organization::factory()->create([
            'invite_code' => 'GUEST123',
        ]);

        $response = $this->get('/join/GUEST123');

        $response->assertRedirect('/app/login');
        $this->assertSame('GUEST123', session('pending_invite_code'));
    }

    public function test_super_admin_can_change_member_role_in_users_relation_manager(): void
    {
        $org = Organization::factory()->create();
        $superAdmin = User::factory()->admin()->create();
        $superAdmin->organizations()->attach($org, ['role' => Organization::ROLE_ADMIN]);

        $member = User::factory()->create(['name' => 'Membro Promovido']);
        $org->users()->attach($member, ['role' => Organization::ROLE_MEMBER]);

        Livewire::actingAs($superAdmin)
            ->test(UsersRelationManager::class, [
                'ownerRecord' => $org,
                'pageClass' => EditOrganization::class,
            ])
            ->callTableAction('changeRole', $member, data: [
                'role' => Organization::ROLE_ADMIN,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertSame(Organization::ROLE_ADMIN, $org->users()->where('users.id', $member->id)->first()->pivot->role);
    }
}
