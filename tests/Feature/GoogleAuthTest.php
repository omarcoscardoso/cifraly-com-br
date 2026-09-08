<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_redirect_route_redirects_to_google_oauth(): void
    {
        Socialite::fake('google');

        $response = $this->get(route('auth.google.redirect'));

        $response->assertRedirect('https://socialite.fake/google/authorize');
    }

    public function test_google_callback_creates_new_user_and_logs_in(): void
    {
        $googleUser = (new SocialiteUser)->map([
            'id' => 'google-user-id-999',
            'name' => 'Pastor João',
            'email' => 'joao@cifraly.com.br',
            'avatar' => 'https://lh3.googleusercontent.com/avatar.jpg',
        ]);

        Socialite::fake('google', $googleUser);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect('/app');

        $this->assertDatabaseHas('users', [
            'email' => 'joao@cifraly.com.br',
            'name' => 'Pastor João',
            'google_id' => 'google-user-id-999',
            'avatar' => 'https://lh3.googleusercontent.com/avatar.jpg',
        ]);

        $user = User::where('email', 'joao@cifraly.com.br')->first();
        $this->assertNotNull($user);
        $this->assertAuthenticatedAs($user);
    }

    public function test_google_callback_links_existing_user_by_email(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'membro@cifraly.com.br',
            'name' => 'Membro Existente',
            'google_id' => null,
            'avatar' => null,
        ]);

        $googleUser = (new SocialiteUser)->map([
            'id' => 'google-user-id-888',
            'name' => 'Membro Existente',
            'email' => 'membro@cifraly.com.br',
            'avatar' => 'https://lh3.googleusercontent.com/avatar2.jpg',
        ]);

        Socialite::fake('google', $googleUser);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect('/app');

        $this->assertSame(1, User::where('email', 'membro@cifraly.com.br')->count());
        $this->assertSame('google-user-id-888', $existingUser->fresh()->google_id);
        $this->assertSame('https://lh3.googleusercontent.com/avatar2.jpg', $existingUser->fresh()->avatar);
        $this->assertAuthenticatedAs($existingUser);
    }

    public function test_google_callback_handles_failure_gracefully(): void
    {
        Socialite::fake('google', function () {
            throw new Exception('Google OAuth cancelled');
        });

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('filament.app.auth.login'));
        $response->assertSessionHas('error', 'Falha ao autenticar com o Google. Tente novamente.');
        $this->assertGuest();
    }
}
