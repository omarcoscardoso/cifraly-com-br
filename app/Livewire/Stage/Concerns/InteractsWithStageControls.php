<?php

declare(strict_types=1);

namespace App\Livewire\Stage\Concerns;

use App\Services\Music\ChordTransposerService;

trait InteractsWithStageControls
{
    public ?string $currentKey = null;

    public bool $useCapo = true;

    public int $fontSize = 18;

    public int $scrollSpeed = 3;

    public bool $isAutoScrolling = false;

    public bool $twoColumns = false;

    public bool $showLyricsOnly = false;

    public function toggleCapo(): void
    {
        $capoFret = $this->getActiveCapoFret();

        if (! $capoFret) {
            return;
        }

        $service = app(ChordTransposerService::class);
        $preferFlats = $service->keyPrefersFlats($this->currentKey ?? 'C');
        $this->useCapo = ! $this->useCapo;

        if ($this->useCapo) {
            $this->currentKey = $service->transposeNote($this->currentKey ?? 'C', -$capoFret, $preferFlats);
        } else {
            $this->currentKey = $service->transposeNote($this->currentKey ?? 'C', $capoFret, $preferFlats);
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
        $this->useCapo = true;
        $this->currentKey = $this->getDefaultKey();
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

    public function toggleTwoColumns(): void
    {
        $this->twoColumns = ! $this->twoColumns;
    }

    public function toggleLyricsOnly(): void
    {
        $this->showLyricsOnly = ! $this->showLyricsOnly;
    }

    /**
     * Get the default baseline key for the active song.
     */
    abstract protected function getDefaultKey(): string;

    /**
     * Get the active capo fret for the current song or version.
     */
    abstract public function getActiveCapoFret(): ?int;
}
