<?php

declare(strict_types=1);

namespace App\Filament\Resources\Organizations\RelationManagers;

use App\Models\Organization;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'Membros / Usuários da Organização';

    protected static ?string $modelLabel = 'Membro';

    protected static ?string $pluralModelLabel = 'Membros';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                    ->dehydrated(fn (?string $state): bool => filled($state)),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->stackedOnMobile()
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('WhatsApp / Telefone')
                    ->placeholder('-'),

                TextColumn::make('pivot.role')
                    ->label('Papel na Organização')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => Organization::ROLE_OPTIONS[$state] ?? $state ?? 'Membro')
                    ->color(fn (?string $state): string => $state === Organization::ROLE_ADMIN ? 'success' : 'gray')
                    ->sortable(),

                IconColumn::make('is_super_admin')
                    ->label('Admin Geral')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Vincular Usuário Existente')
                    ->preloadRecordSelect()
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Select::make('role')
                            ->label('Papel na Organização')
                            ->options(Organization::ROLE_OPTIONS)
                            ->default(Organization::ROLE_MEMBER)
                            ->required(),
                    ]),

                CreateAction::make()
                    ->label('Cadastrar Novo Usuário')
                    ->mutateFormDataUsing(function (array $data): array {
                        return $data;
                    }),
            ])
            ->recordActions([
                Action::make('changeRole')
                    ->label('Alterar Papel')
                    ->icon(Heroicon::OutlinedShieldCheck)
                    ->color('info')
                    ->modalHeading(fn (User $record): string => "Alterar Papel de {$record->name}")
                    ->form([
                        Select::make('role')
                            ->label('Papel na Organização')
                            ->options(Organization::ROLE_OPTIONS)
                            ->required(),
                    ])
                    ->fillForm(fn (User $record): array => [
                        'role' => $record->pivot->role ?? Organization::ROLE_MEMBER,
                    ])
                    ->action(function (User $record, array $data): void {
                        /** @var Organization $owner */
                        $owner = $this->getOwnerRecord();
                        $owner->users()->updateExistingPivot($record->id, [
                            'role' => $data['role'],
                        ]);

                        Notification::make()
                            ->title('Papel atualizado com sucesso!')
                            ->success()
                            ->send();
                    }),

                EditAction::make()
                    ->label('Editar'),

                DetachAction::make()
                    ->label('Desvincular'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
