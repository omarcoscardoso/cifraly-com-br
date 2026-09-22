<?php

declare(strict_types=1);

namespace App\Livewire\Stage;

use App\Livewire\Stage\Concerns\InteractsWithStageControls;
use App\Models\Event;
use App\Models\EventSong;
use App\Models\Organization;
use App\Models\User;
use App\Services\Music\StageChordFormatterService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.stage')]
#[Title('Modo Palco - Cifraly')]
class StageView extends Component
{
    use InteractsWithStageControls;

    public Organization $organization;

    public Event $event;

    public ?int $selectedEventSongId = null;

    public bool $isDrawerOpen = false;

    public function mount(Organization $organization, Event $event): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (! $user || (! $user->isSuperAdmin() && ! $user->canAccessTenant($organization))) {
            abort(403, 'Você não tem permissão para acessar o Modo Palco desta organização.');
        }

        if ($event->organization_id !== $organization->id) {
            abort(404);
        }

        $event->load([
            'organization',
            'team',
            'eventSongs.song.versions',
            'eventSongs.songVersion',
        ]);

        $this->organization = $organization;
        $this->event = $event;

        $firstSong = $event->eventSongs->first();

        if ($firstSong) {
            $this->selectedEventSongId = $firstSong->id;
            $this->currentKey = $firstSong->target_key ?? $firstSong->song?->original_key ?? 'C';
        }
    }

    public function selectSong(int $eventSongId): void
    {
        $eventSong = $this->event->eventSongs->firstWhere('id', $eventSongId);

        if ($eventSong) {
            $this->selectedEventSongId = $eventSong->id;
            $this->currentKey = $eventSong->target_key ?? $eventSong->song?->original_key ?? 'C';
            $this->isDrawerOpen = false;
            $this->isAutoScrolling = false;
        }
    }

    public function nextSong(): void
    {
        $songs = $this->event->eventSongs;

        if ($songs->isEmpty()) {
            return;
        }

        $currentIndex = $songs->search(fn (EventSong $item): bool => $item->id === $this->selectedEventSongId);

        if ($currentIndex !== false && $currentIndex < $songs->count() - 1) {
            $nextSong = $songs->get($currentIndex + 1);
            $this->selectSong($nextSong->id);
        }
    }

    public function previousSong(): void
    {
        $songs = $this->event->eventSongs;

        if ($songs->isEmpty()) {
            return;
        }

        $currentIndex = $songs->search(fn (EventSong $item): bool => $item->id === $this->selectedEventSongId);

        if ($currentIndex !== false && $currentIndex > 0) {
            $prevSong = $songs->get($currentIndex - 1);
            $this->selectSong($prevSong->id);
        }
    }

    protected function getDefaultKey(): string
    {
        $selected = $this->getSelectedEventSong();

        return $selected?->target_key ?? $selected?->song?->original_key ?? 'C';
    }

    public function toggleDrawer(): void
    {
        $this->isDrawerOpen = ! $this->isDrawerOpen;
    }

    public function getSelectedEventSong(): ?EventSong
    {
        return $this->event->eventSongs->firstWhere('id', $this->selectedEventSongId);
    }

    public function getFormattedChords(?StageChordFormatterService $formatter = null): HtmlString
    {
        $selected = $this->getSelectedEventSong();

        if (! $selected || ! $selected->song) {
            return new HtmlString('<p class="text-slate-500 italic p-8">Nenhuma música selecionada no repertório.</p>');
        }

        $formatter ??= app(StageChordFormatterService::class);
        $song = $selected->song;
        $version = $selected->songVersion ?? $song->defaultVersion ?? $song->versions->first();
        $content = $version?->chordpro_content;
        $fromKey = $version?->base_key ?? $song->original_key ?? 'C';
        $toKey = $this->currentKey ?? $selected->target_key ?? $fromKey;

        return $formatter->transposeAndFormat($content, $fromKey, $toKey);
    }

    public function render(StageChordFormatterService $formatter): View
    {
        return view('livewire.stage.stage-view', [
            'selectedEventSong' => $this->getSelectedEventSong(),
            'formattedChords' => $this->getFormattedChords($formatter),
        ]);
    }
}
