<?php

declare(strict_types=1);

namespace App\Filament\Resources\Songs\Pages;

use App\Filament\Resources\Songs\Actions\ImportChordFromWebAction;
use App\Filament\Resources\Songs\SongResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSong extends EditRecord
{
    protected static string $resource = SongResource::class;

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
