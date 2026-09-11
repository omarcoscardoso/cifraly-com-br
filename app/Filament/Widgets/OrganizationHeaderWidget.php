<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Resources\Events\EventResource;
use App\Filament\Resources\Songs\SongResource;
use App\Models\Event;
use App\Models\Organization;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class OrganizationHeaderWidget extends Widget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.organization-header-widget';

    public static function canView(): bool
    {
        return Filament::auth()->check() && Filament::getTenant() !== null;
    }

    public function getOrganization(): ?Organization
    {
        $tenant = Filament::getTenant();

        return $tenant instanceof Organization ? $tenant : null;
    }

    public function getNextEvent(): ?Event
    {
        $tenantId = $this->getOrganization()?->id;
        if (! $tenantId) {
            return null;
        }

        return Event::where('organization_id', $tenantId)
            ->where('starts_at', '>=', now()->subHours(4))
            ->where('status', '!=', Event::STATUS_CANCELED)
            ->orderBy('starts_at', 'asc')
            ->withCount('eventSongs')
            ->first();
    }

    public function getNextEventStageUrl(): ?string
    {
        $event = $this->getNextEvent();
        $org = $this->getOrganization();

        if ($event && $org) {
            return route('events.stage', ['organization' => $org, 'event' => $event]);
        }

        return null;
    }

    public function getGreeting(): string
    {
        $orgName = $this->getOrganization()?->name ?? 'Igreja';

        $hour = (int) now()->format('H');
        $timeGreeting = match (true) {
            $hour < 12 => 'Manhã de Adoração',
            $hour < 18 => 'Tarde de Louvor',
            default => 'Noite de Celebração',
        };

        return "{$orgName} • {$timeGreeting}";
    }

    public function getNewEventUrl(): string
    {
        return EventResource::getUrl('create');
    }

    public function getNewSongUrl(): string
    {
        return SongResource::getUrl('create');
    }

    public function getSongsIndexUrl(): string
    {
        return SongResource::getUrl('index');
    }

    public function getUserFirstName(): string
    {
        $name = Filament::auth()->user()?->name ?? 'Músico';
        $parts = explode(' ', trim($name));

        return $parts[0] ?? $name;
    }

    public function getTimeGreeting(): string
    {
        $hour = (int) now()->format('H');

        return match (true) {
            $hour < 12 => 'Bom dia',
            $hour < 18 => 'Boa tarde',
            default => 'Boa noite',
        };
    }
}
