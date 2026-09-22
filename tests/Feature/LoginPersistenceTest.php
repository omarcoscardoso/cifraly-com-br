<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Auth\Register;
use App\Models\Organization;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class LoginPersistenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('app'));
    }

    public function test_guest_visiting_home_sees_welcome_landing_page(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewIs('welcome');
    }

    public function test_authenticated_user_visiting_home_is_redirected_to_app(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect('/app');
    }

    public function test_authenticated_user_visiting_login_route_is_redirected_to_app(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/app');
    }

    public function test_guest_visiting_login_route_is_redirected_to_filament_login(): void
    {
        $response = $this->get('/login');

        $response->assertRedirect(route('filament.app.auth.login'));
    }

    public function test_login_page_renders_remember_me_checked_by_default(): void
    {
        Livewire::test(Login::class)
            ->assertSchemaStateSet(['remember' => true]);
    }

    public function test_user_can_login_with_remember_enabled_by_default(): void
    {
        $user = User::factory()->create([
            'email' => 'persistente@exemplo.com',
            'password' => Hash::make('SenhaPersistente#123'),
        ]);

        $this->assertGuest();

        Livewire::test(Login::class)
            ->fillForm([
                'email' => 'persistente@exemplo.com',
                'password' => 'SenhaPersistente#123',
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticatedAs($user);

        $user->refresh();
        $this->assertNotNull($user->remember_token);
    }

    public function test_user_registration_generates_remember_token_for_persistence(): void
    {
        Livewire::test(Register::class)
            ->fillForm([
                'name' => 'Cantor Louvor',
                'email' => 'cantor@exemplo.com',
                'password' => 'SenhaSegura#2026',
                'passwordConfirmation' => 'SenhaSegura#2026',
            ])
            ->call('register')
            ->assertHasNoFormErrors();

        $user = User::where('email', 'cantor@exemplo.com')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->remember_token);
        $this->assertAuthenticatedAs($user);
    }

    public function test_authenticated_user_reloading_dashboard_remains_authenticated(): void
    {
        $user = User::factory()->create();
        $org = Organization::factory()->create();
        $org->users()->attach($user, ['role' => Organization::ROLE_ADMIN]);

        $response = $this->actingAs($user)->get("/app/{$org->slug}");
        $response->assertOk();

        // Simula recarregamento na mesma sessão
        $responseAfterReload = $this->get("/app/{$org->slug}");
        $responseAfterReload->assertOk();
    }
}
