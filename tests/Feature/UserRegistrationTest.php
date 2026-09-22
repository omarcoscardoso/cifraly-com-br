<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\Auth\Register;
use App\Models\Organization;
use App\Models\User;
use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('app'));
    }

    public function test_registration_page_is_accessible(): void
    {
        $response = $this->get('/app/register');

        $response->assertStatus(200);
        $response->assertSee('Criar sua conta');
        $response->assertSee('Cadastrar com Google');
        $response->assertSee('/app/login');
    }

    public function test_login_page_displays_registration_link(): void
    {
        $response = $this->get('/app/login');

        $response->assertStatus(200);
        $response->assertSee('/app/register');
        $response->assertSee('crie uma conta');
        $response->assertSee('Entrar com Google');
    }

    public function test_user_can_register_with_valid_data(): void
    {
        Livewire::test(Register::class)
            ->fillForm([
                'name' => 'Marcos Oliveira',
                'email' => 'marcos@exemplo.com',
                'password' => 'SenhaForte#2026',
                'passwordConfirmation' => 'SenhaForte#2026',
            ])
            ->call('register')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', [
            'name' => 'Marcos Oliveira',
            'email' => 'marcos@exemplo.com',
        ]);

        $user = User::where('email', 'marcos@exemplo.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('SenhaForte#2026', $user->password));

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_registration_fails_with_duplicate_email(): void
    {
        User::factory()->create([
            'email' => 'duplicado@exemplo.com',
        ]);

        Livewire::test(Register::class)
            ->fillForm([
                'name' => 'Outro Usuário',
                'email' => 'duplicado@exemplo.com',
                'password' => 'SenhaForte#2026',
                'passwordConfirmation' => 'SenhaForte#2026',
            ])
            ->call('register')
            ->assertHasFormErrors(['email' => 'unique']);

        $this->assertGuest();
    }

    public function test_user_registration_fails_when_passwords_do_not_match(): void
    {
        Livewire::test(Register::class)
            ->fillForm([
                'name' => 'Teste Senha',
                'email' => 'senha@exemplo.com',
                'password' => 'SenhaForte#2026',
                'passwordConfirmation' => 'SenhaDiferente#2026',
            ])
            ->call('register')
            ->assertHasFormErrors(['password' => 'same']);

        $this->assertGuest();
    }

    public function test_user_can_register_and_is_automatically_attached_to_pending_invite_organization(): void
    {
        $organization = Organization::factory()->create([
            'name' => 'Igreja Vida Nova',
            'slug' => 'igreja-vida-nova',
            'invite_code' => 'VIDA2026',
        ]);

        session(['pending_invite_code' => 'VIDA2026']);

        Livewire::test(Register::class)
            ->fillForm([
                'name' => 'Membro Convidado',
                'email' => 'membro@vidanova.com',
                'password' => 'SenhaForte#2026',
                'passwordConfirmation' => 'SenhaForte#2026',
            ])
            ->call('register')
            ->assertHasNoFormErrors();

        $user = User::where('email', 'membro@vidanova.com')->first();
        $this->assertNotNull($user);

        // Usuário deve ter sido vinculado à organização com papel de membro
        $this->assertTrue($organization->users()->where('users.id', $user->id)->exists());
        $this->assertSame(
            Organization::ROLE_MEMBER,
            $organization->users()->where('users.id', $user->id)->first()->pivot->role
        );

        // A sessão de convite pendente deve ter sido consumida
        $this->assertNull(session('pending_invite_code'));
    }

    public function test_registered_user_can_login_with_their_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'login@exemplo.com',
            'password' => Hash::make('MinhaSenhaSecreta#123'),
        ]);

        $this->assertGuest();

        Livewire::test(Login::class)
            ->fillForm([
                'email' => 'login@exemplo.com',
                'password' => 'MinhaSenhaSecreta#123',
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticatedAs($user);
    }

    public function test_authenticated_user_is_redirected_from_register_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/app/register');

        $response->assertRedirect('/app');
    }
}
