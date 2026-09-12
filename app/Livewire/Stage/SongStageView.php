<?php

declare(strict_types=1);

namespace App\Livewire\Stage;

use App\Livewire\Stage\Concerns\InteractsWithStageControls;
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
class SongStageView extends Component
{
    use InteractsWithStageControls;

    public Organization $organization;

    public Song $song;

    public ?SongVersion $songVersion = null;

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
        $this->currentKey = $this->getDefaultKey();
    }

    protected function getDefaultKey(): string
    {
        return $this->songVersion?->base_key ?? $this->song->original_key ?? 'C';
    }

    public function getFormattedChords(?StageChordFormatterService $formatter = null): HtmlString
    {
        $formatter ??= app(StageChordFormatterService::class);
        $version = $this->songVersion ?? $this->song->defaultVersion ?? $this->song->versions->first();
        $content = $version?->chordpro_content;
        $fromKey = $version?->base_key ?? $this->song->original_key ?? 'C';
        $toKey = $this->currentKey ?? $fromKey;

        return $formatter->transposeAndFormat($content, $fromKey, $toKey);
    }

    public function render(StageChordFormatterService $formatter): View
    {
        return view('livewire.stage.song-stage-view', [
            'formattedChords' => $this->getFormattedChords($formatter),
        ]);
    }
}
