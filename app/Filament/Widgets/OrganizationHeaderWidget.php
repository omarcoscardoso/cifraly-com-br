<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Pages\Tenancy\EditOrganizationProfile;
use App\Filament\Resources\Events\EventResource;
use App\Filament\Resources\Songs\SongResource;
use App\Filament\Resources\Teams\TeamResource;
use App\Models\Organization;
use App\Models\User;
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

    public function getTeamsUrl(): string
    {
        return TeamResource::getUrl('index');
    }

    public function getSettingsUrl(): ?string
    {
        $org = $this->getOrganization();

        if ($org && $this->getUser()?->isOrgAdmin($org)) {
            return EditOrganizationProfile::getUrl();
        }

        return null;
    }
}
