<?php

declare(strict_types=1);

namespace App\Filament\Resources\Songs\Tables;

use App\Filament\Resources\Songs\SongResource;
use App\Models\Song;
use App\Services\Music\ChordTransposerService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SongsTable
{
    public static function configure(Table $table): Table
    {
        return $table
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
                    ->icon(Heroicon::OutlinedMusicalNote)
                    ->color('info')
                    ->modalHeading(fn (Song $record): string => "Visualizar / Transpor Cifra - {$record->title}")
                    ->modalWidth('4xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar')
                    ->fillForm(function (Song $record): array {
                        $defaultVersion = $record->defaultVersion;
                        $content = $defaultVersion?->chordpro_content ?? '';
                        $key = $record->original_key ?? 'C';

                        return [
                            'target_key' => $key,
                            'preview_content' => $content,
                        ];
                    })
                    ->form([
                        Grid::make(2)->schema([
                            TextInput::make('current_key_display')
                                ->label('Tom Original')
                                ->default(fn (Song $record): string => $record->original_key ?? 'C')
                                ->disabled()
                                ->dehydrated(false),

                            Select::make('target_key')
                                ->label('Tom Desejado')
                                ->options(SongResource::KEY_OPTIONS)
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set, Song $record): void {
                                    $defaultVersion = $record->defaultVersion;
                                    $content = $defaultVersion?->chordpro_content ?? '';

                                    if (blank($content) || blank($state)) {
                                        $set('preview_content', $content);

                                        return;
                                    }

                                    $service = app(ChordTransposerService::class);
                                    $fromKey = $record->original_key ?? 'C';
                                    $transposed = $service->transpose($content, $fromKey, (string) $state);
                                    $set('preview_content', $transposed);
                                }),
                        ]),

                        Textarea::make('preview_content')
                            ->label('Cifra Formatada (Acordes sobre a Letra)')
                            ->rows(18)
                            ->readOnly()
                            ->extraInputAttributes(['class' => 'font-mono text-sm leading-relaxed bg-gray-50 dark:bg-gray-900']),
                    ]),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
