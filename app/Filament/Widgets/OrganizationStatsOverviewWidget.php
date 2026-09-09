<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Resources\Events\EventResource;
use App\Filament\Resources\Songs\SongResource;
use App\Filament\Resources\Teams\TeamResource;
use App\Models\Event;
use App\Models\EventRoster;
use App\Models\Organization;
use App\Models\Song;
use App\Models\Team;
use Filament\Facades\Filament;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;

class OrganizationStatsOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 3;

    protected ?string $pollingInterval = '30s';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Filament::auth()->check() && Filament::getTenant() !== null;
    }

    #[On('roster-updated')]
    public function updateStats(): void
    {
        // Re-renderiza o componente reativamente quando uma escala for confirmada ou recusada
    }

    protected function getStats(): array
    {
        $tenant = Filament::getTenant();

        if (! $tenant instanceof Organization) {
            return [];
        }

        $tenantId = $tenant->id;

        // 1. Próximos Eventos
        $upcomingEventsQuery = Event::where('organization_id', $tenantId)
            ->where('starts_at', '>=', now()->startOfDay())
            ->where('status', '!=', Event::STATUS_CANCELED);

        $upcomingCount = (clone $upcomingEventsQuery)->count();
        $nextEvent = (clone $upcomingEventsQuery)->orderBy('starts_at', 'asc')->first();

        $nextEventDescription = $nextEvent
            ? 'Próximo: '.$nextEvent->starts_at->format('d/m \à\s H:i')
            : 'Nenhum evento agendado';

        // 2. Músicas no Repertório
        $songsCount = Song::where('organization_id', $tenantId)->count();

        // 3. Equipes e Voluntários
        $membersCount = $tenant->users()->count();
        $teamsCount = Team::where('organization_id', $tenantId)->count();

        // 4. Status de Escalas dos Próximos Eventos
        $upcomingEventIds = (clone $upcomingEventsQuery)->pluck('id');
        $totalRosters = EventRoster::whereIn('event_id', $upcomingEventIds)->count();
        $confirmedRosters = EventRoster::whereIn('event_id', $upcomingEventIds)
            ->where('status', EventRoster::STATUS_CONFIRMED)
            ->count();
        $pendingRosters = EventRoster::whereIn('event_id', $upcomingEventIds)
            ->where('status', EventRoster::STATUS_PENDING)
            ->count();

        if ($totalRosters > 0) {
            $rosterRate = round(($confirmedRosters / $totalRosters) * 100);
            $rosterValue = "{$confirmedRosters}/{$totalRosters} ({$rosterRate}%)";
            $rosterDesc = $pendingRosters > 0
                ? "{$pendingRosters} pendentes de resposta"
                : 'Todas as escalas confirmadas';
            $rosterColor = $pendingRosters > 0 ? 'warning' : 'success';
        } else {
            $rosterValue = '100%';
            $rosterDesc = 'Nenhuma escala pendente';
            $rosterColor = 'gray';
        }

        return [
            Stat::make('Próximos Eventos', (string) $upcomingCount)
                ->description($nextEventDescription)
                ->descriptionIcon(Heroicon::OutlinedCalendarDays)
                ->color('primary')
                ->url(EventResource::getUrl('index')),

            Stat::make('Músicas no Repertório', (string) $songsCount)
                ->description('Cifras e arranjos prontos')
                ->descriptionIcon(Heroicon::OutlinedMusicalNote)
                ->color('success')
                ->url(SongResource::getUrl('index')),

            Stat::make('Voluntários & Equipes', "{$membersCount} Membros")
                ->description("{$teamsCount} ".($teamsCount === 1 ? 'equipe cadastrada' : 'equipes cadastradas'))
                ->descriptionIcon(Heroicon::OutlinedUserGroup)
                ->color('info')
                ->url(TeamResource::getUrl('index')),

            Stat::make('Presença em Escalas', $rosterValue)
                ->description($rosterDesc)
                ->descriptionIcon(Heroicon::OutlinedCheckCircle)
                ->color($rosterColor)
                ->url(EventResource::getUrl('index')),
        ];
    }
}
