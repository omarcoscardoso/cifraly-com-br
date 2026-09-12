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

    protected static ?string $heading = 'Músicas Recentes';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Filament::auth()->check() && Filament::getTenant() !== null;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Song::query()
                    ->where('organization_id', Filament::getTenant()?->id)
                    ->latest()
                    ->limit(5)
            )
            ->recordUrl(fn (Song $record): string => route('songs.stage', [
                'organization' => Filament::getTenant(),
                'song' => $record,
            ]))
            ->columns([
                TextColumn::make('title')
                    ->label('Música')
                    ->weight('bold'),

                TextColumn::make('original_key')
                    ->label('Tom')
                    ->badge()
                    ->color('primary'),

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
