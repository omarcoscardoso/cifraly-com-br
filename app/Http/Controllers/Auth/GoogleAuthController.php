<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function callback(): RedirectResponse
    {
        try {
            /** @var \Laravel\Socialite\Two\User $googleUser */
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable) {
            return redirect()->route('filament.admin.auth.login')
                ->with('error', 'Falha ao autenticar com o Google. Tente novamente.');
        }

        $email = $googleUser->getEmail();

        if (blank($email)) {
            return redirect()->route('filament.admin.auth.login')
                ->with('error', 'Não foi possível obter o e-mail da sua conta Google.');
        }

        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $email)
            ->first();

        if ($user) {
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar() ?? $user->avatar,
                'name' => $user->name ?: ($googleUser->getName() ?? $googleUser->getNickname() ?? 'Usuário'),
            ]);
        } else {
            $user = User::create([
                'name' => $googleUser->getName() ?? $googleUser->getNickname() ?? 'Usuário',
                'email' => $email,
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'email_verified_at' => now(),
            ]);
        }

        Auth::login($user, remember: true);

        if ($inviteCode = session('pending_invite_code')) {
            $inviteCode = strtoupper(trim((string) $inviteCode));
            $org = Organization::where('invite_code', $inviteCode)->first();
            if ($org) {
                $org->users()->syncWithoutDetaching([
                    $user->id => ['role' => Organization::ROLE_MEMBER],
                ]);

                session()->forget('pending_invite_code');
                request()->session()->regenerate();

                return redirect("/admin/{$org->slug}");
            }
            session()->forget('pending_invite_code');
        }

        request()->session()->regenerate();

        return redirect()->intended('/admin');
    }
}
