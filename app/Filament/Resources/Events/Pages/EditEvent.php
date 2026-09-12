<?php

declare(strict_types=1);

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('stageView')
                ->label('Modo Palco')
                ->icon(Heroicon::OutlinedPlayCircle)
                ->color('warning')
                ->url(fn (): string => route('events.stage', [
                    'organization' => Filament::getTenant(),
                    'event' => $this->getRecord(),
                ])),

            DeleteAction::make(),
        ];
    }
}
