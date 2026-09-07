<?php

declare(strict_types=1);

namespace App\Filament\Resources\Songs\Pages;

use App\Filament\Resources\Songs\Actions\ImportChordFromWebAction;
use App\Filament\Resources\Songs\SongResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSong extends CreateRecord
{
    protected static string $resource = SongResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportChordFromWebAction::make(),
        ];
    }

    protected function afterCreate(): void
    {
        $chordproContent = $this->data['chordpro_content'] ?? null;

        $this->record->versions()->create([
            'label' => 'Padrão',
            'base_key' => $this->record->original_key ?? 'C',
            'chordpro_content' => $chordproContent,
            'is_default' => true,
        ]);
    }
}
