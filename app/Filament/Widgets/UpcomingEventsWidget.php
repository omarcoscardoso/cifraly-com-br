<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Event;
use App\Models\EventRoster;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Collection;

class UpcomingEventsWidget extends Widget
{
    protected static ?int $sort = 2;

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
}
