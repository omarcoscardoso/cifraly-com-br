<?php

declare(strict_types=1);

namespace App\Livewire\Stage\Concerns;

use App\Services\Music\ChordTransposerService;

trait InteractsWithStageControls
{
    public ?string $currentKey = null;

    public int $fontSize = 18;

    public int $scrollSpeed = 3;

    public bool $isAutoScrolling = false;

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

    /**
     * Get the default baseline key for the active song.
     */
    abstract protected function getDefaultKey(): string;
}
