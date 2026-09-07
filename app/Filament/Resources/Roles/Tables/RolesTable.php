<?php

declare(strict_types=1);

namespace App\Filament\Resources\Roles\Tables;

use App\Models\Role;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Função')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('category')
                    ->label('Categoria')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => Role::CATEGORY_OPTIONS[$state] ?? $state ?? '-')
                    ->color(fn (?string $state): string => Role::CATEGORY_COLORS[$state] ?? 'gray')
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Descrição')
                    ->placeholder('-')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Categoria')
                    ->options(Role::CATEGORY_OPTIONS),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
