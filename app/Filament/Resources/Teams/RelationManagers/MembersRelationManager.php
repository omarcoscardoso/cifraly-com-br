<?php

declare(strict_types=1);

namespace App\Filament\Resources\Teams\RelationManagers;

use App\Models\Role;
use App\Models\TeamMember;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'teamMembers';

    protected static ?string $title = 'Membros da Equipe';

    protected static ?string $modelLabel = 'Membro';

    protected static ?string $pluralModelLabel = 'Membros';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('user.email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.phone')
                    ->label('WhatsApp / Telefone')
                    ->placeholder('-'),

                TextColumn::make('defaultRole.name')
                    ->label('Função Padrão')
                    ->badge()
                    ->placeholder('Sem função definida')
                    ->color(fn (TeamMember $record): string => $record->defaultRole ? (Role::CATEGORY_COLORS[$record->defaultRole->category] ?? 'primary') : 'gray'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Adicionar Membro')
                    ->modalHeading('Adicionar Membro à Equipe')
                    ->form([
                        Select::make('user_id')
                            ->label('Usuário')
                            ->options(function (): array {
                                $tenant = Filament::getTenant();

                                if (! $tenant) {
                                    return [];
                                }

                                return $tenant->users()->pluck('name', 'users.id')->toArray();
                            })
                            ->searchable()
                            ->required()
                            ->rules([
                                fn (): \Closure => function (string $attribute, $value, \Closure $fail): void {
                                    $tenant = Filament::getTenant();

                                    if (! $tenant || ! $tenant->users()->where('users.id', $value)->exists()) {
                                        $fail('O usuário selecionado não pertence à organização atual.');
                                    }
                                },
                            ]),

                        Select::make('default_role_id')
                            ->label('Função Padrão')
                            ->options(function (): array {
                                $tenantId = Filament::getTenant()?->id;

                                if (! $tenantId) {
                                    return [];
                                }

                                return Role::where('organization_id', $tenantId)->pluck('name', 'id')->toArray();
                            })
                            ->searchable()
                            ->nullable()
                            ->placeholder('Sem função definida')
                            ->rules([
                                fn (): \Closure => function (string $attribute, $value, \Closure $fail): void {
                                    if ($value) {
                                        $tenantId = Filament::getTenant()?->id;

                                        if (! Role::where('organization_id', $tenantId)->where('id', $value)->exists()) {
                                            $fail('A função selecionada não pertence à organização atual.');
                                        }
                                    }
                                },
                            ]),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['organization_id'] = Filament::getTenant()?->id;

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Editar')
                    ->modalHeading('Editar Função Padrão')
                    ->form([
                        Select::make('default_role_id')
                            ->label('Função Padrão')
                            ->options(function (): array {
                                $tenantId = Filament::getTenant()?->id;

                                if (! $tenantId) {
                                    return [];
                                }

                                return Role::where('organization_id', $tenantId)->pluck('name', 'id')->toArray();
                            })
                            ->searchable()
                            ->nullable()
                            ->placeholder('Sem função definida')
                            ->rules([
                                fn (): \Closure => function (string $attribute, $value, \Closure $fail): void {
                                    if ($value) {
                                        $tenantId = Filament::getTenant()?->id;

                                        if (! Role::where('organization_id', $tenantId)->where('id', $value)->exists()) {
                                            $fail('A função selecionada não pertence à organização atual.');
                                        }
                                    }
                                },
                            ]),
                    ]),

                DeleteAction::make()
                    ->label('Remover'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
