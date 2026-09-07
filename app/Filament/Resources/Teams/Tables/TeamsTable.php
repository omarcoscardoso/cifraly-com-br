<?php

declare(strict_types=1);

namespace App\Filament\Resources\Teams\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TeamsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Equipe')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('members_count')
                    ->counts('members')
                    ->label('Membros')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Descrição')
                    ->placeholder('-')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                //
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
