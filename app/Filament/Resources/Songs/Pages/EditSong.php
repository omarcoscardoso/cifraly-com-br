<?php

declare(strict_types=1);

namespace App\Filament\Resources\Songs\Pages;

use App\Filament\Resources\Songs\Actions\ImportChordFromWebAction;
use App\Filament\Resources\Songs\SongResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSong extends EditRecord
{
    protected static string $resource = SongResource::class;

    public ?string $returnUrl = null;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->returnUrl = request()->query('return_url');
    }

    protected function getRedirectUrl(): ?string
    {
        return $this->getValidReturnUrl() ?? parent::getRedirectUrl();
    }

    protected function getCancelFormAction(): Action
    {
        $action = parent::getCancelFormAction();

        if ($validUrl = $this->getValidReturnUrl()) {
            $action->url($validUrl);
        }

        return $action;
    }

    protected function getValidReturnUrl(): ?string
    {
        if (empty($this->returnUrl)) {
            return null;
        }

        if (str_starts_with($this->returnUrl, '/') && ! str_starts_with($this->returnUrl, '//')) {
            return $this->returnUrl;
        }

        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        $urlHost = parse_url($this->returnUrl, PHP_URL_HOST);

        if (! empty($urlHost) && ($urlHost === $appHost || $urlHost === request()->getHost())) {
            return $this->returnUrl;
        }

        return null;
    }

    protected function getHeaderActions(): array
    {
        return [
            ImportChordFromWebAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $defaultVersion = $this->record->defaultVersion;

        if ($defaultVersion) {
            $data['chordpro_content'] = $defaultVersion->chordpro_content;
            if (! isset($data['capo_fret']) && $defaultVersion->capo_fret !== null) {
                $data['capo_fret'] = $defaultVersion->capo_fret;
            }
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $chordproContent = $this->data['chordpro_content'] ?? null;
        $defaultVersion = $this->record->defaultVersion;

        if ($defaultVersion) {
            $defaultVersion->update([
                'base_key' => $this->record->original_key ?? 'C',
                'chordpro_content' => $chordproContent,
                'capo_fret' => $this->record->capo_fret,
            ]);
        } else {
            $this->record->versions()->create([
                'label' => 'Padrão',
                'base_key' => $this->record->original_key ?? 'C',
                'chordpro_content' => $chordproContent,
                'capo_fret' => $this->record->capo_fret,
                'is_default' => true,
            ]);
        }
    }
}
