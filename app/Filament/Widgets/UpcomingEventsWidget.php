<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Resources\Events\EventResource;
use App\Models\Event;
use App\Models\EventRoster;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Collection;

class UpcomingEventsWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected static ?string $heading = 'Eventos & Setlist';

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.upcoming-events-widget';

    public static function canView(): bool
    {
        return Filament::auth()->check() && Filament::getTenant() !== null;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getUpcomingEvents(): Collection
    {
        $tenantId = Filament::getTenant()?->id;

        if (! $tenantId) {
            return new Collection;
        }

        return Event::query()
            ->where('organization_id', $tenantId)
            ->where('starts_at', '>=', now()->subHours(6))
            ->where('status', '!=', Event::STATUS_CANCELED)
            ->withCount([
                'eventSongs',
                'rosters',
                'rosters as confirmed_rosters_count' => fn ($query) => $query->where('status', EventRoster::STATUS_CONFIRMED),
            ])
            ->with(['team'])
            ->orderBy('starts_at', 'asc')
            ->limit(6)
            ->get();
    }

    public function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->query(
                Event::query()
                    ->where('organization_id', Filament::getTenant()?->id)
                    ->where('starts_at', '>=', now()->subHours(6))
                    ->withCount([
                        'eventSongs',
                        'rosters',
                        'rosters as confirmed_rosters_count' => fn ($query) => $query->where('status', EventRoster::STATUS_CONFIRMED),
                    ])
                    ->orderBy('starts_at', 'asc')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('title')
                    ->label('Evento')
                    ->weight('bold'),

                TextColumn::make('starts_at')
                    ->label('Data')
                    ->dateTime('d/m/Y'),

                TextColumn::make('roster_summary')
                    ->label('Escala')
                    ->badge()
                    ->color('info')
                    ->state(function (Event $record): string {
                        $total = $record->rosters_count ?? $record->rosters()->count();
                        $confirmed = $record->confirmed_rosters_count ?? $record->rosters()->where('status', EventRoster::STATUS_CONFIRMED)->count();

                        return "{$confirmed}/{$total} confirmados";
                    })
                    ->visibleFrom('md'),

                TextColumn::make('songs_summary')
                    ->label('Setlist')
                    ->badge()
                    ->color('primary')
                    ->state(fn (Event $record): string => ($record->event_songs_count ?? $record->eventSongs()->count()).' músicas')
                    ->visibleFrom('md'),
            ])
            ->recordActions([
                Action::make('stageView')
                    ->label('Modo Palco')
                    ->icon(Heroicon::OutlinedPlayCircle)
                    ->color('warning')
                    ->button()
                    ->size('xs')
                    ->url(fn (Event $record): string => route('events.stage', [
                        'organization' => Filament::getTenant(),
                        'event' => $record,
                    ]))
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('Nenhum evento agendado')
            ->emptyStateDescription('Crie um novo evento para organizar a escala de voluntários e as cifras no Modo Palco.')
            ->emptyStateIcon(Heroicon::OutlinedCalendarDays)
            ->emptyStateActions([
                Action::make('createFirstEvent')
                    ->label('Criar Primeiro Evento')
                    ->icon(Heroicon::OutlinedPlus)
                    ->url(fn (): string => EventResource::getUrl('create')),
            ])
            ->paginated(false);
    }
}
