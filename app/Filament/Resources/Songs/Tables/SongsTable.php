<?php

declare(strict_types=1);

namespace App\Filament\Resources\Songs\Tables;

use App\Filament\Resources\Songs\SongResource;
use App\Filament\Tables\Columns\ResponsiveTextColumn;
use App\Models\Song;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SongsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(fn (Song $record): string => route('songs.stage', [
                'organization' => Filament::getTenant(),
                'song' => $record,
            ]))
            ->columns([
                Split::make([
                    Stack::make([
                        ResponsiveTextColumn::make('title')
                            ->label('Nome da música')
                            ->searchable()
                            ->sortable()
                            ->weight(FontWeight::Bold),

                        ResponsiveTextColumn::make('artist')
                            ->label('Autor')
                            ->searchable()
                            ->sortable()
                            ->icon('heroicon-m-user')
                            ->weight(FontWeight::Normal)
                            ->size(TextSize::ExtraSmall)
                            ->color('gray')
                            ->placeholder('Não informado')
                            ->toggleable(isToggledHiddenByDefault: true),
                    ])->space(1),

                    ResponsiveTextColumn::make('original_key')
                        ->label('Tom')
                        ->badge()
                        ->color('primary')
                        ->sortable()
                        ->grow(false)
                        ->toggleable(),

                    ResponsiveTextColumn::make('bpm')
                        ->label('BPM')
                        ->icon('heroicon-m-bolt')
                        ->numeric()
                        ->sortable()
                        ->placeholder('-')
                        ->grow(false)
                        ->toggleable(isToggledHiddenByDefault: true),

                    ResponsiveTextColumn::make('time_signature')
                        ->label('Compasso')
                        ->icon('heroicon-m-clock')
                        ->sortable()
                        ->placeholder('-')
                        ->grow(false)
                        ->toggleable(isToggledHiddenByDefault: true),

                    ResponsiveTextColumn::make('created_at')
                        ->label('Data de criação')
                        ->icon('heroicon-m-calendar')
                        ->dateTime('d/m/Y H:i')
                        ->sortable()
                        ->grow(false)
                        ->toggleable(isToggledHiddenByDefault: true),
                ]),
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
