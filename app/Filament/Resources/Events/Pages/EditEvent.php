<?php

declare(strict_types=1);

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getContentTabLabel(): ?string
    {
        return 'Dados do Evento';
    }

    public function getContentTabIcon(): string|BackedEnum|Htmlable|null
    {
        return Heroicon::OutlinedCalendarDays;
    }

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
