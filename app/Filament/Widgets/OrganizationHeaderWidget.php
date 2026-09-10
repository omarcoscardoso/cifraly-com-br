<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Resources\Events\EventResource;
use App\Filament\Resources\Songs\SongResource;
use App\Filament\Resources\Teams\TeamResource;
use App\Models\Event;
use App\Models\Organization;
use App\Models\Song;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Collection;

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

    public function getUser(): ?User
    {
        $user = Filament::auth()->user();

        return $user instanceof User ? $user : null;
    }

    public function getInviteCode(): ?string
    {
        return $this->getOrganization()?->invite_code;
    }

    public function getInviteUrl(): ?string
    {
        $code = $this->getInviteCode();

        return $code ? route('organization.join', ['code' => $code]) : null;
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
        $hour = (int) now()->format('H');
        $timeGreeting = match (true) {
            $hour < 12 => 'Manhã de Adoração',
            $hour < 18 => 'Tarde de Louvor',
            default => 'Noite de Celebração',
        };

        return "Altar • {$timeGreeting}";
    }

    /**
     * @return Collection<int, Song>
     */
    public function getSongs(): Collection
    {
        $tenantId = $this->getOrganization()?->id;
        if (! $tenantId) {
            return Song::query()->whereRaw('1 = 0')->get();
        }

        return Song::where('organization_id', $tenantId)
            ->latest()
            ->limit(6)
            ->get();
    }

    public function getNewEventUrl(): string
    {
        return EventResource::getUrl('create');
    }

    public function getNewSongUrl(): string
    {
        return SongResource::getUrl('create');
    }

    public function getSongsUrl(): string
    {
        return SongResource::getUrl('index');
    }

    public function getEventsUrl(): string
    {
        return EventResource::getUrl('index');
    }

    public function getTeamsUrl(): string
    {
        return TeamResource::getUrl('index');
    }
}
