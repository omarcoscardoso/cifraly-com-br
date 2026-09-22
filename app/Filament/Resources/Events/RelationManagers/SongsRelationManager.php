<?php

declare(strict_types=1);

namespace App\Filament\Resources\Events\RelationManagers;

use App\Filament\Resources\Songs\SongResource;
use App\Models\Event;
use App\Models\Song;
use App\Models\SongVersion;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SongsRelationManager extends RelationManager
{
    protected static string $relationship = 'eventSongs';

    protected static ?string $title = 'Setlist do Repertório';

    protected static ?string $modelLabel = 'Música no Repertório';

    protected static ?string $pluralModelLabel = 'Setlist do Repertório';

    protected static string|BackedEnum|null $icon = Heroicon::OutlinedMusicalNote;

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        /** @var Event $ownerRecord */
        $count = $ownerRecord->eventSongs()->count();

        return $count > 0 ? (string) $count : null;
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('order_index')
            ->defaultSort('order_index', 'asc')
            ->searchable(false)
            ->stackedOnMobile()
            ->extraAttributes([
                'class' => 'setlist-table-compact',
            ])
            ->columns([
                TextColumn::make('order_index')
                    ->label('#')
                    ->sortable()
                    ->weight('bold')
                    ->extraAttributes(['class' => 'setlist-col-order']),

                TextColumn::make('song.title')
                    ->label('Música')
                    ->sortable()
                    ->weight('medium')
                    ->extraAttributes(['class' => 'setlist-col-song']),

                TextColumn::make('song.original_key')
                    ->label('Tom Original')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('target_key')
                    ->label('Tom no Evento')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('song.bpm')
                    ->label('BPM')
                    ->placeholder('-')
                    ->visibleFrom('md'),

                TextColumn::make('arrangement_notes')
                    ->label('Arranjo')
                    ->placeholder('-')
                    ->limit(50)
                    ->visibleFrom('md'),
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
