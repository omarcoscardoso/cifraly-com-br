<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;

class EditProfile extends BaseEditProfile
{
    public function getTitle(): string|Htmlable
    {
        return 'Meu Perfil | Cifraly';
    }

    public static function getLabel(): string
    {
        return 'Meu Perfil';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPhoneFormComponent(),
                $this->getBirthDateFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                $this->getCurrentPasswordFormComponent(),
            ]);
    }

    protected function getPhoneFormComponent(): Component
    {
        return TextInput::make('phone')
            ->label('Celular / WhatsApp')
            ->tel()
            ->mask('(99) 99999-9999')
            ->placeholder('(99) 99999-9999')
            ->maxLength(30);
    }

    protected function getBirthDateFormComponent(): Component
    {
        return DatePicker::make('birth_date')
            ->label('Data de Aniversário')
            ->displayFormat('d/m/Y')
            ->native(false)
            ->maxDate(now());
    }

    protected function getRedirectUrl(): ?string
    {
        return filament()->getUrl();
    }
}
