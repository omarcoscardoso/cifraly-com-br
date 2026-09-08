<?php

declare(strict_types=1);

namespace App\Filament\Resources\Events\RelationManagers;

use App\Filament\Resources\Songs\SongResource;
use App\Models\Event;
use App\Models\EventSong;
use App\Models\Song;
use App\Models\SongVersion;
use App\Services\Music\ChordTransposerService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SongsRelationManager extends RelationManager
{
    protected static string $relationship = 'eventSongs';

    protected static ?string $title = 'Setlist do Repertório';

    protected static ?string $modelLabel = 'Música no Repertório';

    protected static ?string $pluralModelLabel = 'Setlist do Repertório';

    public function table(Table $table): Table
    {
        return $table
            ->stackedOnMobile()
            ->columns([
                TextColumn::make('order_index')
                    ->label('#')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('song.title')
                    ->label('Música')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('song.original_key')
                    ->label('Tom Original')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('target_key')
                    ->label('Tom no Evento')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('song.bpm')
                    ->label('BPM')
                    ->placeholder('-'),

                TextColumn::make('arrangement_notes')
                    ->label('Arranjo')
                    ->placeholder('-')
                    ->limit(50),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Adicionar Música ao Setlist')
                    ->modalHeading('Adicionar Música ao Repertório')
                    ->form([
                        Select::make('song_id')
                            ->label('Música')
                            ->options(function (): array {
                                $tenantId = Filament::getTenant()?->id;

                                if (! $tenantId) {
                                    return [];
                                }

                                return Song::where('organization_id', $tenantId)->pluck('title', 'id')->toArray();
                            })
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set): void {
                                if (! $state) {
                                    $set('song_version_id', null);
                                    $set('target_key', null);

                                    return;
                                }

                                $song = Song::with('versions')->find($state);

                                if ($song) {
                                    $defaultVersion = $song->defaultVersion ?? $song->versions->first();
                                    $set('song_version_id', $defaultVersion?->id);
                                    $set('target_key', $song->original_key ?? 'C');
                                }
                            })
                            ->rules([
                                fn (): \Closure => function (string $attribute, $value, \Closure $fail): void {
                                    $tenantId = Filament::getTenant()?->id;

                                    if (! Song::where('organization_id', $tenantId)->where('id', $value)->exists()) {
                                        $fail('A música selecionada não pertence à organização atual.');
                                    }
                                },
                            ]),

                        Select::make('song_version_id')
                            ->label('Versão da Cifra')
                            ->options(function (callable $get): array {
                                $songId = $get('song_id');

                                if (! $songId) {
                                    return [];
                                }

                                return SongVersion::where('song_id', $songId)
                                    ->pluck('label', 'id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->required(),

                        Select::make('target_key')
                            ->label('Tom no Evento')
                            ->options(SongResource::KEY_OPTIONS)
                            ->required()
                            ->searchable(),

                        TextInput::make('order_index')
                            ->label('Ordem (#)')
                            ->numeric()
                            ->default(function (RelationManager $livewire): int {
                                /** @var Event $event */
                                $event = $livewire->getOwnerRecord();

                                return ($event->eventSongs()->max('order_index') ?? 0) + 1;
                            })
                            ->required(),

                        TextInput::make('arrangement_notes')
                            ->label('Arranjo / Observações')
                            ->placeholder('Ex: Intro suave no piano, subir 1 tom no final')
                            ->maxLength(255),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['organization_id'] = Filament::getTenant()?->id;

                        return $data;
                    }),
            ])
            ->recordActions([
                Action::make('viewTransposedChord')
                    ->label('Ver Cifra no Tom')
                    ->icon(Heroicon::OutlinedMusicalNote)
                    ->color('info')
                    ->modalHeading(function (EventSong $record): string {
                        $songTitle = $record->song?->title ?? 'Música';
                        $key = $record->target_key;

                        return "Cifra: {$songTitle} (Tom: {$key})";
                    })
                    ->modalWidth('4xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar')
                    ->form(function (EventSong $record): array {
                        $song = $record->song;
                        $version = $record->songVersion ?? $song?->defaultVersion ?? $song?->versions()->first();
                        $content = $version?->chordpro_content ?? '';
                        $fromKey = $version?->base_key ?? $song?->original_key ?? 'C';
                        $toKey = $record->target_key ?? $fromKey;

                        $transposed = $content;

                        if (! empty($content) && $fromKey !== $toKey) {
                            $transposer = app(ChordTransposerService::class);

                            try {
                                $transposed = $transposer->transpose($content, $fromKey, $toKey);
                            } catch (\Throwable) {
                                $transposed = $content;
                            }
                        }

                        return [
                            Grid::make(3)->schema([
                                TextInput::make('from_key')
                                    ->label('Tom Original')
                                    ->default($fromKey)
                                    ->disabled(),

                                TextInput::make('target_key_display')
                                    ->label('Tom no Evento')
                                    ->default($toKey)
                                    ->disabled(),

                                TextInput::make('version_label')
                                    ->label('Versão')
                                    ->default($version?->label ?? 'Padrão')
                                    ->disabled(),
                            ]),

                            Textarea::make('chord_content')
                                ->label('Cifra Transposta (Acordes sobre a Letra)')
                                ->rows(18)
                                ->default($transposed)
                                ->readOnly()
                                ->extraInputAttributes(['class' => 'font-mono text-sm leading-relaxed bg-gray-50 dark:bg-gray-900']),
                        ];
                    }),

                EditAction::make()
                    ->label('Editar')
                    ->form([
                        Select::make('target_key')
                            ->label('Tom no Evento')
                            ->options(SongResource::KEY_OPTIONS)
                            ->required()
                            ->searchable(),

                        TextInput::make('order_index')
                            ->label('Ordem (#)')
                            ->numeric()
                            ->required(),

                        TextInput::make('arrangement_notes')
                            ->label('Arranjo / Observações')
                            ->maxLength(255)
                            ->nullable(),
                    ]),

                DeleteAction::make()
                    ->label('Remover'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('order_index', 'asc');
    }
}
