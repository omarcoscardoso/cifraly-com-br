<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Components\Component;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    public function getTitle(): string|Htmlable
    {
        return 'Entrar | Cifraly';
    }

    public function getHeading(): string|Htmlable|null
    {
        return 'Entrar na sua conta';
    }

    /**
     * Define o checkbox de "lembrar de mim" marcado por padrão para garantir
     * a persistência da autenticação mesmo após o fechamento do navegador ou app.
     */
    protected function getRememberFormComponent(): Component
    {
        return parent::getRememberFormComponent()
            ->default(true);
    }

    /**
     * Garante que a flag remember seja verdadeira por padrão ao autenticar.
     */
    public function authenticate(): ?LoginResponse
    {
        if (is_array($this->data) && ! array_key_exists('remember', $this->data)) {
            $this->data['remember'] = true;
        }

        return parent::authenticate();
    }
}
