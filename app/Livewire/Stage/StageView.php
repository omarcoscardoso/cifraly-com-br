<?php

declare(strict_types=1);

namespace App\Livewire\Stage;

use App\Models\Event;
use App\Models\EventSong;
use App\Models\Organization;
use App\Models\User;
use App\Services\Music\ChordTransposerService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.stage')]
#[Title('Modo Palco - Cifraly')]
class StageView extends Component
{
    public Organization $organization;

    public Event $event;

    public ?int $selectedEventSongId = null;

    public ?string $currentKey = null;

    public int $fontSize = 18;

    public int $scrollSpeed = 3;

    public bool $isAutoScrolling = false;

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

    public function transposeUp(): void
    {
        $service = app(ChordTransposerService::class);
        $this->currentKey = $service->transposeNote($this->currentKey ?? 'C', 1);
    }

    public function transposeDown(): void
    {
        $service = app(ChordTransposerService::class);
        $this->currentKey = $service->transposeNote($this->currentKey ?? 'C', -1);
    }

    public function resetKey(): void
    {
        $selected = $this->getSelectedEventSong();

        if ($selected) {
            $this->currentKey = $selected->target_key ?? $selected->song?->original_key ?? 'C';
        }
    }

    public function increaseFontSize(): void
    {
        $this->fontSize = min(36, $this->fontSize + 2);
    }

    public function decreaseFontSize(): void
    {
        $this->fontSize = max(12, $this->fontSize - 2);
    }

    public function toggleAutoScroll(): void
    {
        $this->isAutoScrolling = ! $this->isAutoScrolling;
    }

    public function toggleDrawer(): void
    {
        $this->isDrawerOpen = ! $this->isDrawerOpen;
    }

    public function getSelectedEventSong(): ?EventSong
    {
        return $this->event->eventSongs->firstWhere('id', $this->selectedEventSongId);
    }

    public function getFormattedChords(): HtmlString
    {
        $selected = $this->getSelectedEventSong();

        if (! $selected || ! $selected->song) {
            return new HtmlString('<p class="text-slate-500 italic p-8">Nenhuma música selecionada no repertório.</p>');
        }

        $song = $selected->song;
        $version = $selected->songVersion ?? $song->defaultVersion ?? $song->versions->first();
        $content = $version?->chordpro_content ?? '';

        if (blank($content)) {
            return new HtmlString('<p class="text-slate-500 italic p-8">Cifra não cadastrada para esta música.</p>');
        }

        $fromKey = $version?->base_key ?? $song->original_key ?? 'C';
        $toKey = $this->currentKey ?? $selected->target_key ?? $fromKey;

        $service = app(ChordTransposerService::class);

        try {
            $transposed = $service->transpose($content, $fromKey, $toKey);
        } catch (\Throwable) {
            $transposed = $content;
        }

        return $this->formatForStageHtml($transposed, $service);
    }

    private function formatForStageHtml(string $text, ChordTransposerService $service): HtmlString
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", $text);
        $lines = explode("\n", $normalized);
        $htmlLines = [];

        foreach ($lines as $line) {
            // Check if section header or bracketed tag: e.g. [Intro] or [Refrão]
            if (preg_match('/^\[(intro|verso|verse|refrão|refrao|chorus|ponte|bridge|solo|final|outro|tag|interlúdio|interlude)[^\]]*\]/i', trim($line))) {
                $htmlLines[] = '<div class="font-bold text-cyan-400 bg-cyan-950/40 border-l-2 border-cyan-400 px-3 py-1 my-2 rounded-r tracking-wider text-[0.9em] inline-block">'.e($line).'</div>';

                continue;
            }

            // Standalone section bracket e.g. [Parte 1]
            if (preg_match('/^\[[^\]]+\]$/', trim($line))) {
                $htmlLines[] = '<div class="font-bold text-amber-500/80 tracking-wider text-[0.85em] my-1 uppercase">'.e($line).'</div>';

                continue;
            }

            // Chord line
            if ($service->isChordLine($line)) {
                // Highlight chords in amber while preserving spacing
                $escaped = e($line);
                // Wrap whitespace separated tokens that are valid chords in styled spans
                $formatted = preg_replace_callback('/\S+/', function (array $m) use ($service): string {
                    $token = $m[0];
                    if ($service->isValidChord(htmlspecialchars_decode($token))) {
                        return '<span class="text-amber-400 font-bold">'.$token.'</span>';
                    }

                    return '<span class="text-amber-200">'.$token.'</span>';
                }, $escaped);

                $htmlLines[] = '<div class="leading-tight font-bold whitespace-pre">'.$formatted.'</div>';

                continue;
            }

            // Lyric or other text line
            $htmlLines[] = '<div class="text-slate-200 leading-snug whitespace-pre mb-2">'.e($line).'</div>';
        }

        return new HtmlString(implode("\n", $htmlLines));
    }

    public function render(): View
    {
        return view('livewire.stage.stage-view', [
            'selectedEventSong' => $this->getSelectedEventSong(),
            'formattedChords' => $this->getFormattedChords(),
        ]);
    }
}
