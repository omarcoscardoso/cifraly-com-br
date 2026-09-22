<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\Auth\EditProfile;
use App\Models\Organization;
use App\Models\User;
use Filament\Enums\UserMenuPosition;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class EditProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Lucas Batera',
            'email' => 'lucas@cifraly.com',
            'phone' => '(11) 98888-7777',
            'birth_date' => '1995-04-20',
            'password' => Hash::make('SenhaAtual#123'),
        ]);

        $this->organization = Organization::factory()->create([
            'name' => 'Igreja Central',
            'slug' => 'igreja-central',
        ]);
        $this->user->organizations()->attach($this->organization, ['role' => Organization::ROLE_ADMIN]);

        $this->actingAs($this->user);
        Filament::setCurrentPanel(Filament::getPanel('app'));
        Filament::setTenant($this->organization, isQuiet: true);
    }

    protected function tearDown(): void
    {
        Filament::setTenant(null);

        parent::tearDown();
    }

    public function test_panel_has_sidebar_collapsible_and_user_menu_in_sidebar(): void
    {
        $panel = Filament::getPanel('app');

        $this->assertTrue($panel->isSidebarCollapsibleOnDesktop());
        $this->assertSame(UserMenuPosition::Sidebar, $panel->getUserMenuPosition());
        $this->assertSame(EditProfile::class, $panel->getProfilePage());
    }

    public function test_profile_page_is_accessible_to_authenticated_user(): void
    {
        Livewire::test(EditProfile::class)
            ->assertSuccessful()
            ->assertFormSet([
                'name' => 'Lucas Batera',
                'email' => 'lucas@cifraly.com',
                'phone' => '(11) 98888-7777',
                'birth_date' => '1995-04-20',
            ]);
    }

    public function test_profile_http_route_is_accessible(): void
    {
        $response = $this->get('/app/profile');

        $response->assertSuccessful();
        $response->assertSee('Meu Perfil');
        $response->assertSee('Lucas Batera');
    }

    public function test_profile_http_route_is_accessible_when_no_tenant_is_active(): void
    {
        Filament::setTenant(null);

        $response = $this->get('/app/profile');

        $response->assertSuccessful();
        $response->assertSee('Meu Perfil');
        $response->assertSee('Lucas Batera');
    }

    public function test_user_can_update_profile_information_including_phone_and_birth_date(): void
    {
        Livewire::test(EditProfile::class)
            ->fillForm([
                'name' => 'Lucas Silva',
                'phone' => '(11) 99999-0000',
                'birth_date' => '1996-05-25',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertRedirect(Filament::getUrl($this->organization));

        $this->user->refresh();

        $this->assertSame('Lucas Silva', $this->user->name);
        $this->assertSame('(11) 99999-0000', $this->user->phone);
        $this->assertSame('1996-05-25', $this->user->birth_date?->format('Y-m-d'));
    }

    public function test_user_can_update_password_with_correct_current_password(): void
    {
        Livewire::test(EditProfile::class)
            ->fillForm([
                'currentPassword' => 'SenhaAtual#123',
                'password' => 'NovaSenha#2026',
                'passwordConfirmation' => 'NovaSenha#2026',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->user->refresh();
        $this->assertTrue(Hash::check('NovaSenha#2026', $this->user->password));
    }

    public function test_user_cannot_update_password_with_incorrect_current_password(): void
    {
        Livewire::test(EditProfile::class)
            ->fillForm([
                'currentPassword' => 'SenhaErrada#999',
                'password' => 'NovaSenha#2026',
                'passwordConfirmation' => 'NovaSenha#2026',
            ])
            ->call('save')
            ->assertHasFormErrors(['currentPassword']);

        $this->user->refresh();
        $this->assertTrue(Hash::check('SenhaAtual#123', $this->user->password));
    }
}
