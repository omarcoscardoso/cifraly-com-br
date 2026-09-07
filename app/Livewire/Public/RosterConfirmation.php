<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Models\EventRoster;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Confirmação de Presença - Cifraly')]
class RosterConfirmation extends Component
{
    public EventRoster $roster;

    public ?string $declineReason = '';

    public bool $showDeclineModal = false;

    public bool $showSetlist = false;

    public ?string $feedbackMessage = null;

    public function mount(string $token): void
    {
        $roster = EventRoster::with([
            'event.organization',
            'event.team',
            'user',
            'role',
            'event.eventSongs.song',
        ])
            ->where('confirmation_token', $token)
            ->first();

        if (! $roster) {
            abort(404, 'Convite não encontrado ou expirado.');
        }

        $this->roster = $roster;
        $this->declineReason = $roster->decline_reason ?? '';
    }

    public function confirm(): void
    {
        $this->roster->update([
            'status' => EventRoster::STATUS_CONFIRMED,
            'responded_at' => now(),
            'decline_reason' => null,
        ]);

        $this->feedbackMessage = 'Sua presença foi confirmada com sucesso! Obrigado por servir.';
        $this->showDeclineModal = false;
        $this->roster->refresh();
    }

    public function openDeclineModal(): void
    {
        $this->showDeclineModal = true;
    }

    public function closeDeclineModal(): void
    {
        $this->showDeclineModal = false;
    }

    public function decline(): void
    {
        $this->validate([
            'declineReason' => 'nullable|string|max:500',
        ]);

        $this->roster->update([
            'status' => EventRoster::STATUS_DECLINED,
            'responded_at' => now(),
            'decline_reason' => trim((string) $this->declineReason) ?: null,
        ]);

        $this->feedbackMessage = 'Sua ausência foi registrada. Sentiremos sua falta!';
        $this->showDeclineModal = false;
        $this->roster->refresh();
    }

    public function toggleSetlist(): void
    {
        $this->showSetlist = ! $this->showSetlist;
    }

    public function render(): View
    {
        return view('livewire.public.roster-confirmation');
    }
}
