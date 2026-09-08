<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Event;
use App\Models\EventRoster;
use App\Models\Organization;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Collection;

class RosterConfirmationAlertWidget extends Widget
{
    protected static ?int $sort = 0;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.roster-confirmation-alert-widget';

    public bool $showModal = false;

    public ?int $decliningRosterId = null;

    public string $declineReason = '';

    public static function canView(): bool
    {
        $user = Filament::auth()->user();
        $tenant = Filament::getTenant();

        if (! $user instanceof User || ! $tenant instanceof Organization) {
            return false;
        }

        // O card/notificação só é visível se o usuário possuir escalas PENDENTES em eventos futuros
        return EventRoster::query()
            ->where('user_id', $user->id)
            ->where('organization_id', $tenant->id)
            ->where('status', EventRoster::STATUS_PENDING)
            ->whereHas('event', function ($query): void {
                $query->where('status', '!=', Event::STATUS_CANCELED)
                    ->where('starts_at', '>=', now()->subHours(6));
            })
            ->exists();
    }

    /**
     * @return Collection<int, EventRoster>
     */
    public function getPendingRosters(): Collection
    {
        $user = Filament::auth()->user();
        $tenant = Filament::getTenant();

        if (! $user instanceof User || ! $tenant instanceof Organization) {
            return new Collection;
        }

        return EventRoster::with(['event.team', 'role', 'event.eventSongs'])
            ->where('user_id', $user->id)
            ->where('organization_id', $tenant->id)
            ->where('status', EventRoster::STATUS_PENDING)
            ->whereHas('event', function ($query): void {
                $query->where('status', '!=', Event::STATUS_CANCELED)
                    ->where('starts_at', '>=', now()->subHours(6));
            })
            ->get()
            ->sortBy('event.starts_at')
            ->values();
    }

    public function openModal(): void
    {
        $this->showModal = true;
        $this->decliningRosterId = null;
        $this->declineReason = '';
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->decliningRosterId = null;
        $this->declineReason = '';
    }

    public function confirmAttendance(int $rosterId): void
    {
        $roster = $this->findAuthorizedRoster($rosterId);

        if (! $roster) {
            Notification::make()
                ->title('Escala não encontrada ou acesso não autorizado.')
                ->danger()
                ->send();

            return;
        }

        $roster->update([
            'status' => EventRoster::STATUS_CONFIRMED,
            'responded_at' => now(),
            'decline_reason' => null,
        ]);

        Notification::make()
            ->title('Presença confirmada!')
            ->body("Sua presença no evento '{$roster->event->title}' foi confirmada com sucesso.")
            ->success()
            ->send();

        $this->decliningRosterId = null;
        $this->declineReason = '';

        // Se não houver mais escalas pendentes, fecha o modal automaticamente
        if ($this->getPendingRosters()->isEmpty()) {
            $this->showModal = false;
        }
    }

    public function startDecline(int $rosterId): void
    {
        $this->decliningRosterId = $rosterId;
        $this->declineReason = '';
    }

    public function cancelDecline(): void
    {
        $this->decliningRosterId = null;
        $this->declineReason = '';
    }

    public function submitDecline(int $rosterId): void
    {
        $roster = $this->findAuthorizedRoster($rosterId);

        if (! $roster) {
            $this->cancelDecline();

            return;
        }

        $reason = trim($this->declineReason) ?: null;

        $roster->update([
            'status' => EventRoster::STATUS_DECLINED,
            'responded_at' => now(),
            'decline_reason' => $reason,
        ]);

        $this->cancelDecline();

        Notification::make()
            ->title('Ausência informada')
            ->body("Sua falta no evento '{$roster->event->title}' foi registrada.")
            ->warning()
            ->send();

        // Se não houver mais escalas pendentes, fecha o modal automaticamente
        if ($this->getPendingRosters()->isEmpty()) {
            $this->showModal = false;
        }
    }

    protected function findAuthorizedRoster(int $rosterId): ?EventRoster
    {
        $user = Filament::auth()->user();
        $tenant = Filament::getTenant();

        if (! $user instanceof User || ! $tenant instanceof Organization) {
            return null;
        }

        return EventRoster::query()
            ->where('id', $rosterId)
            ->where('user_id', $user->id)
            ->where('organization_id', $tenant->id)
            ->with(['event', 'role'])
            ->first();
    }
}
