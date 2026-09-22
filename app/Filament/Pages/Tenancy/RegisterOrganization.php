<?php

declare(strict_types=1);

namespace App\Filament\Pages\Tenancy;

use App\Models\Organization;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\RegisterTenant;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RegisterOrganization extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Cadastrar Organização';
    }

    public function mount(): void
    {
        parent::mount();

        if ($code = request()->query('code', session('pending_invite_code'))) {
            $this->form->fill([
                'action_type' => 'join',
                'invite_code' => strtoupper((string) $code),
            ]);
            session()->forget('pending_invite_code');
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Radio::make('action_type')
                    ->label('O que você deseja fazer?')
                    ->options([
                        'create' => 'Criar uma nova organização (Igreja / Ministério)',
                        'join' => 'Entrar em uma organização existente via Código de Convite',
                    ])
                    ->default('create')
                    ->live(),

                TextInput::make('name')
                    ->label('Nome da Organização')
                    ->placeholder('Ex: Primeira Igreja Batista')
                    ->required(fn (callable $get): bool => ($get('action_type') ?? 'create') === 'create')
                    ->visible(fn (callable $get): bool => ($get('action_type') ?? 'create') === 'create')
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug((string) $state))),

                TextInput::make('slug')
                    ->label('Identificador (Slug)')
                    ->placeholder('Ex: pib-central')
                    ->required(fn (callable $get): bool => ($get('action_type') ?? 'create') === 'create')
                    ->visible(fn (callable $get): bool => ($get('action_type') ?? 'create') === 'create')
                    ->maxLength(255)
                    ->unique(Organization::class, 'slug'),

                TextInput::make('invite_code')
                    ->label('Código de Convite')
                    ->placeholder('Ex: ABC123XY')
                    ->required(fn (callable $get): bool => $get('action_type') === 'join')
                    ->visible(fn (callable $get): bool => $get('action_type') === 'join')
                    ->maxLength(32)
                    ->extraInputAttributes(['style' => 'text-transform: uppercase; font-family: monospace; letter-spacing: 0.1em;'])
                    ->helperText('Informe o código fornecido pelo administrador da sua igreja ou equipe.')
                    ->rules([
                        fn (callable $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get): void {
                            if ($get('action_type') === 'join') {
                                $code = strtoupper(trim((string) $value));
                                if (! Organization::where('invite_code', $code)->exists()) {
                                    $fail('Código de convite inválido ou organização não encontrada.');
                                }
                            }
                        },
                    ]),
            ]);
    }

    protected function handleRegistration(array $data): Model
    {
        $actionType = $data['action_type'] ?? 'create';

        if ($actionType === 'join') {
            $code = strtoupper(trim((string) ($data['invite_code'] ?? '')));
            $organization = Organization::where('invite_code', $code)->firstOrFail();

            $organization->users()->syncWithoutDetaching([
                auth()->id() => ['role' => Organization::ROLE_MEMBER],
            ]);

            return $organization;
        }

        $organization = Organization::create([
            'name' => $data['name'],
            'slug' => $data['slug'],
        ]);

        $organization->users()->syncWithoutDetaching([
            auth()->id() => ['role' => Organization::ROLE_ADMIN],
        ]);

        return $organization;
    }
}
