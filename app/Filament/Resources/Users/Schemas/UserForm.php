<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações Pessoais')
                    ->description('Dados cadastrais e contato do usuário')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Nome Completo')
                                ->placeholder('Ex: João Silva')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('email')
                                ->label('E-mail')
                                ->placeholder('joao@exemplo.com')
                                ->email()
                                ->required()
                                ->maxLength(255)
                                ->unique(User::class, 'email', ignoreRecord: true),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('phone')
                                ->label('WhatsApp / Telefone')
                                ->placeholder('(11) 99999-9999')
                                ->tel()
                                ->maxLength(30),

                            TextInput::make('password')
                                ->label('Senha')
                                ->password()
                                ->revealable()
                                ->required(fn (string $operation): bool => $operation === 'create')
                                ->dehydrated(fn (?string $state): bool => filled($state))
                                ->helperText(fn (string $operation): ?string => $operation === 'edit' ? 'Deixe em branco para manter a senha atual.' : null),
                        ]),
                    ]),

                Section::make('Acesso e Organizações')
                    ->description('Defina as organizações às quais o usuário pertence e permissões do sistema')
                    ->schema([
                        Select::make('organizations')
                            ->label('Organizações Vinculadas')
                            ->multiple()
                            ->relationship(titleAttribute: 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Selecione uma ou mais organizações')
                            ->helperText('O usuário poderá acessar o painel de todas as organizações selecionadas.'),

                        Toggle::make('is_super_admin')
                            ->label('Administrador Geral da Ferramenta (Super Admin)')
                            ->helperText('Concede acesso irrestrito para gerenciar usuários, organizações e configurações da plataforma Cifraly.')
                            ->default(false),
                    ]),
            ]);
    }
}
