<?php

declare(strict_types=1);

namespace App\Livewire\Stage;

use App\Livewire\Stage\Concerns\InteractsWithStageControls;
use App\Models\Event;
use App\Models\EventSong;
use App\Models\Organization;
use App\Models\Song;
use App\Models\SongVersion;
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

    public ?int $adHocSongId = null;

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
            $this->adHocSongId = null;
            $this->currentKey = $eventSong->target_key ?? $eventSong->song?->original_key ?? 'C';
            $this->isDrawerOpen = false;
            $this->isAutoScrolling = false;
        }
    }

    public function selectAdHocSong(int $songId): void
    {
        $song = $this->organization->songs()
            ->with(['versions', 'defaultVersion'])
            ->find($songId);

        if ($song) {
            $this->selectedEventSongId = null;
            $this->adHocSongId = $song->id;
            $this->currentKey = $song->original_key ?? 'C';
            $this->isDrawerOpen = false;
            $this->isAutoScrolling = false;
        }
    }

    public function selectOrganizationSong(int $songId): void
    {
        $eventSong = $this->event->eventSongs->firstWhere('song_id', $songId);

        if ($eventSong) {
            $this->selectSong($eventSong->id);
        } else {
            $this->selectAdHocSong($songId);
        }
    }

    public function nextSong(): void
    {
        $songs = $this->event->eventSongs;

        if ($songs->isEmpty()) {
            return;
        }

        if ($this->adHocSongId !== null) {
            $first = $songs->first();
            if ($first) {
                $this->selectSong($first->id);
            }

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

        if ($this->adHocSongId !== null) {
            $last = $songs->last();
            if ($last) {
                $this->selectSong($last->id);
            }

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
        if ($this->adHocSongId !== null) {
            return $this->getCurrentSong()?->original_key ?? 'C';
        }

        $selected = $this->getSelectedEventSong();

        return $selected?->target_key ?? $selected?->song?->original_key ?? 'C';
    }

    public function toggleDrawer(): void
    {
        $this->isDrawerOpen = ! $this->isDrawerOpen;
    }

    public function getSelectedEventSong(): ?EventSong
    {
        if ($this->adHocSongId !== null) {
            return null;
        }

        return $this->event->eventSongs->firstWhere('id', $this->selectedEventSongId);
    }

    public function getCurrentSong(): ?Song
    {
        if ($this->adHocSongId !== null) {
            return $this->organization->songs()
                ->with(['versions', 'defaultVersion'])
                ->find($this->adHocSongId);
        }

        return $this->getSelectedEventSong()?->song;
    }

    public function getCurrentSongVersion(): ?SongVersion
    {
        if ($this->adHocSongId !== null) {
            $song = $this->getCurrentSong();

            return $song?->defaultVersion ?? $song?->versions->first();
        }

        $selected = $this->getSelectedEventSong();

        return $selected?->songVersion ?? $selected?->song?->defaultVersion ?? $selected?->song?->versions->first();
    }

    public function getFormattedChords(?StageChordFormatterService $formatter = null): HtmlString
    {
        $song = $this->getCurrentSong();

        if (! $song) {
            return new HtmlString('<p class="text-slate-500 italic p-8">Nenhuma música selecionada no repertório.</p>');
        }

        $formatter ??= app(StageChordFormatterService::class);
        $version = $this->getCurrentSongVersion();
        $content = $version?->chordpro_content;
        $fromKey = $version?->base_key ?? $song->original_key ?? 'C';
        $toKey = $this->currentKey ?? ($this->getSelectedEventSong()?->target_key ?? $fromKey);

        return $formatter->transposeAndFormat($content, $fromKey, $toKey);
    }

    public function render(StageChordFormatterService $formatter): View
    {
        $allSongs = $this->organization->songs()
            ->orderBy('title')
            ->get(['id', 'organization_id', 'title', 'artist', 'original_key', 'youtube_url', 'spotify_url']);

        return view('livewire.stage.stage-view', [
            'selectedEventSong' => $this->getSelectedEventSong(),
            'currentSong' => $this->getCurrentSong(),
            'currentSongVersion' => $this->getCurrentSongVersion(),
            'isAdHocSong' => $this->adHocSongId !== null,
            'formattedChords' => $this->getFormattedChords($formatter),
            'allSongs' => $allSongs,
        ]);
    }
}
