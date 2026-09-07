<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Tables;

use App\Models\Organization;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('phone')
                    ->label('WhatsApp / Telefone')
                    ->placeholder('-'),

                TextColumn::make('organizations.name')
                    ->label('Organizações')
                    ->badge()
                    ->color('info')
                    ->separator(', ')
                    ->placeholder('Nenhuma organização'),

                IconColumn::make('is_super_admin')
                    ->label('Super Admin')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Cadastrado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('organizations')
                    ->label('Organização')
                    ->relationship('organizations', 'name')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('is_super_admin')
                    ->label('Administrador Geral')
                    ->trueLabel('Apenas Super Admins')
                    ->falseLabel('Apenas Usuários Comuns')
                    ->placeholder('Todos os Usuários'),
            ])
            ->recordActions([
                Action::make('resetPassword')
                    ->label('Redefinir Senha')
                    ->icon(Heroicon::OutlinedKey)
                    ->color('warning')
                    ->modalHeading(fn (User $record): string => "Redefinir Senha de {$record->name}")
                    ->modalDescription('Informe a nova senha de acesso para este usuário.')
                    ->modalSubmitActionLabel('Salvar Nova Senha')
                    ->form([
                        TextInput::make('password')
                            ->label('Nova Senha')
                            ->password()
                            ->revealable()
                            ->required()
                            ->minLength(6),
                    ])
                    ->action(function (User $record, array $data): void {
                        $record->update([
                            'password' => $data['password'],
                        ]);

                        Notification::make()
                            ->title('Senha redefinida com sucesso!')
                            ->body("A senha do usuário {$record->name} foi atualizada.")
                            ->success()
                            ->send();
                    }),

                Action::make('manageOrganizations')
                    ->label('Organizações')
                    ->icon(Heroicon::OutlinedBuildingOffice2)
                    ->color('info')
                    ->modalHeading(fn (User $record): string => "Organizações de {$record->name}")
                    ->modalDescription('Gerencie a quais organizações este usuário tem acesso.')
                    ->modalSubmitActionLabel('Salvar Organizações')
                    ->fillForm(fn (User $record): array => [
                        'organizations' => $record->organizations()->pluck('organizations.id')->toArray(),
                    ])
                    ->form([
                        Select::make('organizations')
                            ->label('Organizações')
                            ->multiple()
                            ->options(fn (): array => Organization::pluck('name', 'id')->toArray())
                            ->searchable()
                            ->preload()
                            ->placeholder('Selecione as organizações...'),
                    ])
                    ->action(function (User $record, array $data): void {
                        $record->organizations()->sync($data['organizations'] ?? []);

                        Notification::make()
                            ->title('Organizações atualizadas com sucesso!')
                            ->body("As organizações de {$record->name} foram atualizadas.")
                            ->success()
                            ->send();
                    }),

                EditAction::make(),

                DeleteAction::make()
                    ->label('Excluir')
                    ->disabled(fn (User $record): bool => $record->id === auth()->id())
                    ->tooltip(fn (User $record): ?string => $record->id === auth()->id() ? 'Você não pode excluir sua própria conta.' : null),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
