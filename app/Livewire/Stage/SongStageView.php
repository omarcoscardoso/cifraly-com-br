<?php

declare(strict_types=1);

namespace App\Livewire\Stage;

use App\Models\Organization;
use App\Models\Song;
use App\Models\SongVersion;
use App\Models\User;
use App\Services\Music\ChordTransposerService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.stage')]
#[Title('Modo Palco - Cifraly')]
class SongStageView extends Component
{
    public Organization $organization;

    public Song $song;

    public ?SongVersion $songVersion = null;

    public ?string $currentKey = null;

    public int $fontSize = 18;

    public int $scrollSpeed = 3;

    public bool $isAutoScrolling = false;

    public function mount(Organization $organization, Song $song): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (! $user || (! $user->isSuperAdmin() && ! $user->canAccessTenant($organization))) {
            abort(403, 'Você não tem permissão para acessar esta cifra.');
        }

        if ($song->organization_id !== $organization->id) {
            abort(404);
        }

        $song->load(['versions', 'defaultVersion']);

        $this->organization = $organization;
        $this->song = $song;
        $this->songVersion = $song->defaultVersion ?? $song->versions->first();
        $this->currentKey = $this->songVersion?->base_key ?? $song->original_key ?? 'C';
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
        $this->currentKey = $this->songVersion?->base_key ?? $this->song->original_key ?? 'C';
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

    public function getFormattedChords(): HtmlString
    {
        $version = $this->songVersion ?? $this->song->defaultVersion ?? $this->song->versions->first();
        $content = $version?->chordpro_content ?? '';

        if (blank($content)) {
            return new HtmlString('<p class="text-slate-500 italic p-8">Cifra não cadastrada para esta música.</p>');
        }

        $fromKey = $version?->base_key ?? $this->song->original_key ?? 'C';
        $toKey = $this->currentKey ?? $fromKey;

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
        $normalized = str_replace("\t", '    ', $normalized);
        $lines = explode("\n", $normalized);
        $htmlLines = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if (preg_match('/^\[(refrão|refrao|chorus)[^\]]*\]/i', $trimmed)) {
                $htmlLines[] = '<div class="stage-section-chorus stage-chorus-target">'.e($line).'</div>';

                continue;
            }

            if (preg_match('/^\[(intro|verso|verse|ponte|bridge|solo|final|outro|tag|interlúdio|interlude)[^\]]*\]/i', $trimmed)) {
                $htmlLines[] = '<div class="stage-section-badge">'.e($line).'</div>';

                continue;
            }

            if (preg_match('/^\[[^\]]+\]$/', $trimmed)) {
                $htmlLines[] = '<div class="stage-section-tag">'.e($line).'</div>';

                continue;
            }

            if ($service->isChordLine($line)) {
                $escaped = e($line);
                $formatted = preg_replace_callback('/\S+/', function (array $m) use ($service): string {
                    $token = $m[0];
                    if ($service->isValidChord(htmlspecialchars_decode($token))) {
                        return '<span class="stage-chord text-amber-400 font-bold">'.$token.'</span>';
                    }

                    return '<span class="stage-chord-token text-amber-200">'.$token.'</span>';
                }, $escaped);

                $htmlLines[] = '<div class="stage-chord-line leading-tight font-bold whitespace-pre" style="white-space: pre;">'.$formatted.'</div>';

                continue;
            }

            $htmlLines[] = '<div class="stage-lyric-line text-slate-200 leading-snug whitespace-pre mb-2" style="white-space: pre;">'.e($line).'</div>';
        }

        return new HtmlString(implode("\n", $htmlLines));
    }

    public function render(): View
    {
        return view('livewire.stage.song-stage-view', [
            'formattedChords' => $this->getFormattedChords(),
        ]);
    }
}
