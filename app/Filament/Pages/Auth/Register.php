<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Models\Organization;
use App\Models\User;
use Filament\Auth\Pages\Register as BaseRegister;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use SensitiveParameter;

class Register extends BaseRegister
{
    public function getTitle(): string|Htmlable
    {
        return 'Criar Conta | Cifraly';
    }

    public function getHeading(): string|Htmlable|null
    {
        return 'Criar sua conta';
    }

    /**
     * Handle the registration of a new user.
     *
     * @param  array<string, mixed>  $data
     */
    protected function handleRegistration(#[SensitiveParameter] array $data): Model
    {
        /** @var User $user */
        $user = parent::handleRegistration($data);

        // Se houver convite pendente na sessão, vincula o novo usuário como membro da organização
        if ($inviteCode = session('pending_invite_code')) {
            $inviteCode = strtoupper(trim((string) $inviteCode));
            $organization = Organization::where('invite_code', $inviteCode)->first();

            if ($organization) {
                $organization->users()->syncWithoutDetaching([
                    $user->id => ['role' => Organization::ROLE_MEMBER],
                ]);

                session()->forget('pending_invite_code');
            }
        }

        return $user;
    }
}
