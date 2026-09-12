<?php

declare(strict_types=1);

namespace App\Filament\Resources\Songs\Tables;

use App\Filament\Resources\Songs\SongResource;
use App\Models\Song;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SongsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->stackedOnMobile()
            ->recordUrl(fn (Song $record): string => route('songs.stage', [
                'organization' => Filament::getTenant(),
                'song' => $record,
            ]))
            ->columns([
                TextColumn::make('title')
                    ->label('Nome da música')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('artist')
                    ->label('Autor')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Não informado')
                    ->toggleable(),

                TextColumn::make('original_key')
                    ->label('Tom')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('bpm')
                    ->label('BPM')
                    ->numeric()
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('time_signature')
                    ->label('Compasso')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Data de criação')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('original_key')
                    ->label('Tom Original')
                    ->options(SongResource::KEY_OPTIONS),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
