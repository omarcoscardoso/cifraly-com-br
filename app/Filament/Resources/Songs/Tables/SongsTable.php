<?php

declare(strict_types=1);

namespace App\Filament\Resources\Songs\Tables;

use App\Filament\Resources\Songs\SongResource;
use App\Models\Song;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SongsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->stackedOnMobile()
            ->columns([
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('artist')
                    ->label('Artista')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Não informado'),

                TextColumn::make('original_key')
                    ->label('Tom Original')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('bpm')
                    ->label('BPM')
                    ->numeric()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('time_signature')
                    ->label('Compasso')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Criado em')
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
                Action::make('transposePreview')
                    ->label('Visualizar / Transpor')
                    ->icon(Heroicon::OutlinedPlayCircle)
                    ->color('info')
                    ->url(fn (Song $record): string => route('songs.stage', [
                        'organization' => Filament::getTenant(),
                        'song' => $record,
                    ])),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
