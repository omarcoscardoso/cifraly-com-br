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

class UpcomingEventsWidget extends TableWidget
{
    protected static ?int $sort = 3;

    protected static ?string $heading = 'Próximos Eventos & Modo Palco';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Filament::auth()->check() && Filament::getTenant() !== null;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Event::query()
                    ->where('organization_id', Filament::getTenant()?->id)
                    ->where('starts_at', '>=', now()->subHours(6))
                    ->orderBy('starts_at', 'asc')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('title')
                    ->label('Evento')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('team.name')
                    ->label('Equipe')
                    ->placeholder('Geral / Sem equipe')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('starts_at')
                    ->label('Data e Horário')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => Event::STATUS_OPTIONS[$state] ?? $state ?? '-')
                    ->color(fn (?string $state): string => Event::STATUS_COLORS[$state] ?? 'gray'),

                TextColumn::make('roster_summary')
                    ->label('Escala')
                    ->badge()
                    ->color('info')
                    ->state(function (Event $record): string {
                        $total = $record->rosters()->count();
                        $confirmed = $record->rosters()->where('status', EventRoster::STATUS_CONFIRMED)->count();

                        return "{$confirmed}/{$total} confirmados";
                    }),

                TextColumn::make('songs_summary')
                    ->label('Setlist')
                    ->badge()
                    ->color('primary')
                    ->state(fn (Event $record): string => $record->eventSongs()->count().' músicas'),
            ])
            ->headerActions([
                Action::make('createEvent')
                    ->label('Novo Evento')
                    ->icon(Heroicon::OutlinedPlus)
                    ->color('primary')
                    ->url(fn (): string => EventResource::getUrl('create')),

                Action::make('viewAll')
                    ->label('Ver Todos')
                    ->icon(Heroicon::OutlinedCalendarDays)
                    ->color('gray')
                    ->url(fn (): string => EventResource::getUrl('index')),
            ])
            ->recordActions([
                Action::make('stageView')
                    ->label('Modo Palco')
                    ->icon(Heroicon::OutlinedPlayCircle)
                    ->color('warning')
                    ->url(fn (Event $record): string => route('events.stage', [
                        'organization' => Filament::getTenant(),
                        'event' => $record,
                    ]))
                    ->openUrlInNewTab(),

                Action::make('editEvent')
                    ->label('Editar')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->color('gray')
                    ->url(fn (Event $record): string => EventResource::getUrl('edit', ['record' => $record])),
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
