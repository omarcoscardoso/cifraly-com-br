<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Resources\Songs\SongResource;
use App\Models\Song;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentSongsWidget extends TableWidget
{
    protected static ?int $sort = 4;

    protected static ?string $heading = 'Repertório Recente de Músicas';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Filament::auth()->check() && Filament::getTenant() !== null;
    }

    public function table(Table $table): Table
    {
        return $table
            ->stackedOnMobile()
            ->query(
                Song::query()
                    ->where('organization_id', Filament::getTenant()?->id)
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('title')
                    ->label('Música')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('artist')
                    ->label('Artista')
                    ->placeholder('Não informado'),

                TextColumn::make('original_key')
                    ->label('Tom Principal')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('bpm')
                    ->label('BPM')
                    ->placeholder('-'),

                TextColumn::make('time_signature')
                    ->label('Compasso')
                    ->placeholder('-'),

                TextColumn::make('updated_at')
                    ->label('Atualizada em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                Action::make('createSong')
                    ->label('Nova Música')
                    ->icon(Heroicon::OutlinedPlus)
                    ->color('primary')
                    ->url(fn (): string => SongResource::getUrl('create')),

                Action::make('viewAllSongs')
                    ->label('Ver Repertório')
                    ->icon(Heroicon::OutlinedMusicalNote)
                    ->color('gray')
                    ->url(fn (): string => SongResource::getUrl('index')),
            ])
            ->recordActions([
                Action::make('editSong')
                    ->label('Editar')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->color('gray')
                    ->url(fn (Song $record): string => SongResource::getUrl('edit', ['record' => $record])),
            ])
            ->emptyStateHeading('Nenhuma música cadastrada')
            ->emptyStateDescription('Adicione cifras e músicas ao repertório da sua organização.')
            ->emptyStateIcon(Heroicon::OutlinedMusicalNote)
            ->emptyStateActions([
                Action::make('createFirstSong')
                    ->label('Cadastrar Música')
                    ->icon(Heroicon::OutlinedPlus)
                    ->url(fn (): string => SongResource::getUrl('create')),
            ])
            ->paginated(false);
    }
}
